<?php

namespace App\Filament\TreePlugin\Actions;

use App\Filament\TreePlugin\Components\Tree;
use App\Filament\TreePlugin\Concern\Actions\HasTree;
use Filament\Actions\ActionGroup as BaseActionGroup;

class ActionGroup extends BaseActionGroup implements HasTree
{
    public function getActions(): array
    {
        $actions = [];

        foreach ($this->actions as $action) {
            $actions[$action->getName()] = $action->grouped()->record($this->getRecord());
        }

        return $actions;
    }

    public function tree(Tree $tree): static
    {
        foreach ($this->actions as $action) {
            if (! $action instanceof HasTree) {
                continue;
            }

            $action->tree($tree);
        }

        return $this;
    }
}
