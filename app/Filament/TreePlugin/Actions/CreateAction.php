<?php

namespace App\Filament\TreePlugin\Actions;

use Filament\Actions\CreateAction as BaseCreateAction;
use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;

class CreateAction extends BaseCreateAction
{
    use TreeActionTrait;
}
