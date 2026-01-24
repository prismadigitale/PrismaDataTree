<?php

namespace App\Filament\TreePlugin\Actions;

use Filament\Actions\DeleteAction as BaseDeleteAction;
use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;

class DeleteAction extends BaseDeleteAction
{
    use TreeActionTrait;
}
