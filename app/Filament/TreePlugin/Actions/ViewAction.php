<?php

namespace App\Filament\TreePlugin\Actions;

use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;
use Filament\Actions\ViewAction as BaseViewAction;

class ViewAction extends BaseViewAction
{
    use TreeActionTrait;
}
