<?php

namespace App\Filament\Resources\DataTypes;

use App\Filament\Resources\DataTypes\Pages\CreateDataType;
use App\Filament\Resources\DataTypes\Pages\EditDataType;
use App\Filament\Resources\DataTypes\Pages\ListDataTypes;
use App\Filament\Resources\DataTypes\Schemas\DataTypeForm;
use App\Filament\Resources\DataTypes\Tables\DataTypesTable;
use App\Models\DataType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataTypeResource extends Resource
{
    protected static ?string $model = DataType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.configurations');
    }

    public static function getModelLabel(): string
    {
        return __('messages.data_type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.data_types');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DataTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\DataTypes\RelationManagers\FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataTypes::route('/'),
            'create' => CreateDataType::route('/create'),
            'edit' => EditDataType::route('/{record}/edit'),
        ];
    }
}
