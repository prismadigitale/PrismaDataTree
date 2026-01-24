<?php

namespace App\Filament\TreePlugin\Macros;

use Illuminate\Database\Schema\Blueprint;
use App\Filament\TreePlugin\Support\Utils;

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
