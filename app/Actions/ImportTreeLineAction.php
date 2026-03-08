<?php

namespace App\Actions;

use App\Models\DataType;
use App\Models\Field;
use App\Models\Node;
use Illuminate\Support\Str;

class ImportTreeLineAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute(string $filePath, string $rootTitle): void
    {
        // Load the XML file
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \Exception('Invalid XML file.');
        }

        // Create the root node to contain all imports
        // Use a generic DataType for the root, or create one if none exists.
        $rootDataType = $this->getOrCreateDataType('Imported Root', 'heroicon-o-folder');

        $rootNode = Node::create([
            'title' => $rootTitle,
            'data_type_id' => $rootDataType->id,
            'data' => [],
            'parent_id' => null,
            'order' => 1,
        ]);

        $this->parseNode($xml, $rootNode);
    }

    protected function parseNode(\SimpleXMLElement $element, Node $parentNode): void
    {
        foreach ($element->children() as $child) {
            // In TreeLine .trl, nodes have the attribute item="y"
            $attributes = $child->attributes();
            $isItem = isset($attributes['item']) && (string) $attributes['item'] === 'y';

            if ($isItem) {
                // It's a node
                $tagName = $child->getName();

                // Title is either from the 'Name' or 'NomeDominio' sub-elements, or generic
                // We test sequentially some common TreeLine elements
                $rawTitle = (string) $child->Name ?: ((string) $child->NomeDominio ?: ((string) $child->NomeStep ?: $tagName.' Node'));
                $title = \Illuminate\Support\Str::limit(strip_tags($rawTitle), 250);

                $dataType = $this->getOrCreateDataType($tagName);

                // Now extract fields
                $data = [];
                $fieldsToProcess = [];

                foreach ($child->children() as $grandChild) {
                    $gcAttributes = $grandChild->attributes();
                    $gcIsItem = isset($gcAttributes['item']) && (string) $gcAttributes['item'] === 'y';

                    if (! $gcIsItem) {
                        // It's a field
                        $fieldName = $grandChild->getName();
                        $fieldTypeValue = isset($gcAttributes['type']) ? (string) $gcAttributes['type'] : 'Text';

                        $fieldsToProcess[] = [
                            'name' => $fieldName,
                            'type' => $fieldTypeValue,
                        ];

                        $data[$fieldName] = trim((string) $grandChild);
                    }
                }

                $this->ensureFieldsExistOnDataType($dataType, $fieldsToProcess);

                $node = Node::create([
                    'title' => $title,
                    'data_type_id' => $dataType->id,
                    'data' => $data,
                    'parent_id' => $parentNode->id,
                    'order' => Node::where('parent_id', $parentNode->id)->max('order') + 1,
                ]);

                // Recursively parse children of this node
                $this->parseNode($child, $node);
            }
        }
    }

    protected function getOrCreateDataType(string $name, string $icon = 'heroicon-o-document-text'): DataType
    {
        $slug = Str::slug($name);

        return DataType::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => str_replace('_', ' ', $name),
                'icon' => $icon,
            ]
        );
    }

    protected function ensureFieldsExistOnDataType(DataType $dataType, array $fields): void
    {
        $existingFields = $dataType->fields()->pluck('name')->toArray();

        foreach ($fields as $fieldInfo) {
            if (! in_array($fieldInfo['name'], $existingFields)) {
                // Map XML field type to our database field types
                $type = match ($fieldInfo['type']) {
                    'ExternalLink', 'url' => 'text',
                    'Date' => 'date',
                    'Number' => 'number',
                    'Boolean' => 'toggle',
                    'Choice', 'Combination' => 'select',
                    'Text' => 'textarea', // Text in TreeLine can be multiline
                    default => 'text',
                };

                $field = Field::firstOrCreate(
                    ['name' => $fieldInfo['name']],
                    [
                        'label' => str_replace('_', ' ', $fieldInfo['name']),
                        'type' => $type,
                    ]
                );

                $dataType->fields()->attach($field->id);
                $existingFields[] = $fieldInfo['name'];
            }
        }
    }
}
