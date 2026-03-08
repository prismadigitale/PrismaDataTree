<?php

namespace App\Filament\TreePlugin\Actions;

use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;
use Filament\Actions\CreateAction as BaseCreateAction;

class CreateAction extends BaseCreateAction
{
    use TreeActionTrait;
}
