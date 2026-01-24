<?php

namespace App\Filament\TreePlugin\Actions\Modal;

use App\Filament\TreePlugin\Concern\Actions\HasTree;
use App\Filament\TreePlugin\Concern\BelongsToTree;

/**
 * @deprecated Use `\Filament\Actions\StaticAction` instead.
 */
class Action extends \Filament\Actions\Action implements HasTree
{
    use BelongsToTree;
}
