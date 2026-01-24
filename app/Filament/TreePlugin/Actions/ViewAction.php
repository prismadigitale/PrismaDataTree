<?php

namespace App\Filament\TreePlugin\Actions;

use Filament\Actions\ViewAction as BaseViewAction;
use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;

class ViewAction extends BaseViewAction
{
    use TreeActionTrait;
}
