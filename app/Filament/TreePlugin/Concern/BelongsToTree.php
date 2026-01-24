<?php

namespace App\Filament\TreePlugin\Concern;

use App\Filament\TreePlugin\Components\Tree;
use App\Filament\TreePlugin\Contract\HasTree;

trait BelongsToTree
{
    protected Tree $tree;

    public function tree(Tree $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    public function getTree(): Tree
    {
        return $this->tree;
    }

    public function getLivewire(): HasTree
    {
        return $this->getTree()->getLivewire();
    }
}
