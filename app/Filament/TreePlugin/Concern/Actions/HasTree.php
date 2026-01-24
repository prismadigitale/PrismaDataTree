<?php

namespace App\Filament\TreePlugin\Concern\Actions;

use App\Filament\TreePlugin\Components\Tree;

interface HasTree
{
    public function tree(Tree $tree): static;
}
