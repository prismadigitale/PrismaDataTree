<?php

namespace App\Filament\TreePlugin\Pages;

use Filament\Pages\Page;
use App\Filament\TreePlugin\Concern\TreePageTrait;
use App\Filament\TreePlugin\Contract\HasTree;

abstract class TreePage extends Page implements HasTree
{
    use TreePageTrait;

    protected string $view = 'filament.tree-plugin.pages.tree';
}
