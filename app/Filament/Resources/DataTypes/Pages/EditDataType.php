<?php

namespace App\Filament\Resources\DataTypes\Pages;

use App\Filament\Resources\DataTypes\DataTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataType extends EditRecord
{
    protected static string $resource = DataTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
