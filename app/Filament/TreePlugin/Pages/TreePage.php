<?php

namespace App\Filament\TreePlugin\Pages;

use App\Filament\TreePlugin\Concern\TreePageTrait;
use App\Filament\TreePlugin\Contract\HasTree;
use Filament\Pages\Page;

abstract class TreePage extends Page implements HasTree
{
    use TreePageTrait;

    protected string $view = 'filament.tree-plugin.pages.tree';
}
