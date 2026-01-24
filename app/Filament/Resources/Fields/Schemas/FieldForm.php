<?php

namespace App\Filament\Resources\Fields\Schemas;

use Filament\Schemas\Schema;

class FieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('label')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => 
                        $set('name', \Illuminate\Support\Str::slug($state, '_'))
                    ),
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Internal unique identifier (slug)'),
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'richtext' => 'Rich Text Editor',
                        'number' => 'Number',
                        'date' => 'Date',
                        'datetime' => 'Date & Time',
                        'toggle' => 'Toggle (Yes/No)',
                        'select' => 'Select (Dropdown)',
                        'radio' => 'Radio Buttons',
                        'checkbox_list' => 'Checkbox List',
                        'image' => 'Image Upload',
                        'file' => 'File Upload',
                    ])
                    ->required()
                    ->live()
                    ->default('text'),
                \Filament\Forms\Components\Repeater::make('options')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('value')
                            ->required()
                            ->label('Value'),
                        \Filament\Forms\Components\TextInput::make('label')
                            ->required()
                            ->label('Label'),
                    ])
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => in_array($get('type'), ['select', 'radio', 'checkbox_list']))
                    ->columns(2)
                    ->label('Options')
                    ->helperText('Define the available options for this field'),
                \Filament\Forms\Components\TextInput::make('validation_rules')
                    ->placeholder('required|min:3|max:255')
                    ->helperText('Laravel validation rules, separated by |'),
            ]);
    }
}
