<?php

namespace App\Filament\TreePlugin\Actions;

use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;
use Filament\Actions\DeleteAction as BaseDeleteAction;

class DeleteAction extends BaseDeleteAction
{
    use TreeActionTrait;
}
