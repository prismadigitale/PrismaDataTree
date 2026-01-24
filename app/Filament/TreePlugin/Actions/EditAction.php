<?php

namespace App\Filament\TreePlugin\Actions;

use Filament\Actions\EditAction as BaseEditAction;
use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;

class EditAction extends BaseEditAction
{
    use TreeActionTrait;
}
