<?php

namespace App\Filament\Resources\DataTypes\Pages;

use App\Filament\Resources\DataTypes\DataTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataTypes extends ListRecords
{
    protected static string $resource = DataTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
