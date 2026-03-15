<?php

namespace App\Filament\Pages;

use App\Filament\TreePlugin\Pages\TreePage;
use App\Models\DataType;
use App\Models\Node;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class NodesManager extends TreePage
{
    protected static string $model = Node::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    public static function getNavigationLabel(): string
    {
        return __('messages.nodes_manager');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('messages.nodes_manager');
    }

    // Enable Edit action
    protected function hasEditAction(): bool
    {
        return true;
    }

    // Enable Delete action
    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function getTreeQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getTreeQuery()->with(['dataType']);
    }

    protected function getTreeActions(): array
    {
        return array_merge(
            parent::getTreeActions(),
            [
                \App\Filament\TreePlugin\Actions\CreateAction::make('addChild')
                    ->label(__('messages.add_child'))
                    ->icon('heroicon-o-plus')
                    ->fillForm(function (Node $record): array {
                        $defaultChildTypeId = \Illuminate\Support\Facades\DB::table('data_types')
                            ->where('id', $record->data_type_id)
                            ->value('default_child_type_id');

                        // If no default child type on the parent's data type, check global settings
                        if (! $defaultChildTypeId) {
                            $defaultChildTypeId = \App\Models\Setting::where('key', 'default_data_type_id')->value('value');
                        }

                        return [
                            'parent_id' => $record->id,
                            'data_type_id' => $defaultChildTypeId ?? $record->data_type_id,
                        ];
                    })
                    ->modalHeading(__('messages.new_node'))
                    ->modalWidth('4xl')
                    ->extraAttributes(['class' => 'hidden']),
            ]
        );
    }

    protected function getActions(): array
    {
        return array_merge(
            parent::getActions(),
            [
                \Filament\Actions\Action::make('importTreeLine')
                    ->label(__('messages.import_treeline'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('root_name')
                            ->label(__('messages.root_node_name'))
                            ->required()
                            ->default('Imported Data'),
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label(__('messages.treeline_file'))
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    $file = is_array($value) ? array_values($value)[0] ?? null : $value;

                                    if ($file && method_exists($file, 'getClientOriginalExtension')) {
                                        $extension = strtolower($file->getClientOriginalExtension());
                                        if (! in_array($extension, ['xml', 'trl'])) {
                                            $fail('Puoi importare solo file con estensione .trl o .xml da TreeLine.');
                                        }
                                    } elseif (is_string($file)) {
                                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        if (! in_array($extension, ['xml', 'trl'])) {
                                            $fail('Puoi importare solo file con estensione .trl o .xml da TreeLine.');
                                        }
                                    }
                                },
                            ]),
                    ])
                    ->action(function (array $data, \App\Actions\ImportTreeLineAction $importer) {
                        $file = $data['file'];
                        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($file);

                        try {
                            $importer->execute($path, $data['root_name']);

                            \Filament\Notifications\Notification::make()
                                ->title(__('messages.import_successful'))
                                ->success()
                                ->send();

                            \Illuminate\Support\Facades\Storage::disk('local')->delete($file);

                            return redirect(request()->header('Referer'));
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('messages.import_failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]
        );
    }

    // Define Form Schema for Create/Edit actions
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Select::make('parent_id')
                ->relationship('parent', 'title')
                ->searchable()
                ->placeholder(__('messages.root_placeholder'))
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $parentId = $get('parent_id');
                    if (! $parentId) {
                        return;
                    }

                    $parent = Node::find($parentId);
                    if ($parent && $parent->dataType?->default_child_type_id) {
                        $set('data_type_id', $parent->dataType->default_child_type_id);

                        // Also trigger the data reset for the new type
                        $set('data', []);
                    }
                }),
            Select::make('data_type_id')
                ->relationship('dataType', 'name')
                ->searchable()
                ->placeholder(__('messages.select_data_type'))
                ->live()
                ->afterStateUpdated(fn ($state, Set $set) => $set('data', [])),

            Group::make()
                ->schema(function (Get $get) {
                    $dataTypeId = $get('data_type_id');
                    if (! $dataTypeId) {
                        return [];
                    }

                    $dataType = DataType::with('fields')->find($dataTypeId);
                    if (! $dataType) {
                        return [];
                    }

                    $fields = [];
                    foreach ($dataType->fields as $field) {
                        $component = match ($field->type) {
                            'text' => TextInput::make("data.{$field->name}"),
                            'textarea' => \Filament\Forms\Components\RichEditor::make("data.{$field->name}"),
                            'number' => TextInput::make("data.{$field->name}")->numeric(),
                            'date' => DatePicker::make("data.{$field->name}"),
                            'toggle' => Toggle::make("data.{$field->name}"),
                            'select' => Select::make("data.{$field->name}")
                                ->options(collect($field->options)->pluck('label', 'value')->toArray()),
                            default => TextInput::make("data.{$field->name}"),
                        };

                        $component->label($field->label ?: str($field->name)->title());

                        if ($field->validation_rules) {
                            $component->rules($field->validation_rules);
                        }

                        $fields[] = $component;
                    }

                    return $fields;
                }),
        ];
    }
}
