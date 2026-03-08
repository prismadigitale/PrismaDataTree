<?php

namespace App\Filament\TreePlugin\Macros;

use App\Filament\TreePlugin\Support\Utils;
use Illuminate\Database\Schema\Blueprint;

/**
 * @see Blueprint
 */
class BlueprintMarcos
{
    public function treeColumns()
    {
        return function (string $titleType = 'string') {
            $this->{$titleType}(Utils::titleColumnName());
            $this->integer(Utils::parentColumnName())->default(Utils::defaultParentId())->index();
            $this->integer(Utils::orderColumnName())->default(0);
        };
    }
}
