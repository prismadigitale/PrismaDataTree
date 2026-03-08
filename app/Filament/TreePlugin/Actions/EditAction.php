<?php

namespace App\Filament\TreePlugin\Actions;

use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;
use Filament\Actions\EditAction as BaseEditAction;

class EditAction extends BaseEditAction
{
    use TreeActionTrait;
}
