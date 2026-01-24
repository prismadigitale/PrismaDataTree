<?php

namespace App\Filament\Resources\DataTypes\Schemas;

use Filament\Schemas\Schema;

class DataTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => 
                        $set('slug', \Illuminate\Support\Str::slug($state, '_'))
                    ),
                \Filament\Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                \Filament\Forms\Components\TextInput::make('icon')
                    ->placeholder('heroicon-o-folder'),
                \Filament\Forms\Components\Select::make('default_child_type_id')
                    ->label('Default Child Type')
                    ->relationship('defaultChildType', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
