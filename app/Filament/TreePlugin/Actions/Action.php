<?php

namespace App\Filament\TreePlugin\Actions;

use Filament\Actions\Action as BaseAction;
use App\Filament\TreePlugin\Concern\Actions\HasTree;
use App\Filament\TreePlugin\Concern\Actions\TreeActionTrait;
use App\Filament\TreePlugin\Concern\BelongsToTree;

class Action extends BaseAction implements HasTree
{
    use BelongsToTree;
    use TreeActionTrait;
}
