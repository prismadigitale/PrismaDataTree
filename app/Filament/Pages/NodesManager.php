<?php

namespace App\Filament\Pages;

use App\Filament\TreePlugin\Pages\TreePage;
use App\Models\DataType;
use App\Models\Node;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class NodesManager extends TreePage
{
    protected static string $model = Node::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Nodes Manager';

    protected static ?string $title = 'Nodes Manager';

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
                    ->label('Add Child')
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
                    ->modalHeading('New node')
                    ->modalWidth('4xl')
                    ->extraAttributes(['class' => 'hidden']),
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
                ->placeholder('Root (leave empty for root node)')
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
                ->placeholder('Select data type')
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
                            'textarea' => Textarea::make("data.{$field->name}"),
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
