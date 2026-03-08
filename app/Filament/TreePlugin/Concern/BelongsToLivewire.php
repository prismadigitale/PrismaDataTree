<?php

namespace App\Filament\TreePlugin\Concern;

use App\Filament\TreePlugin\Contract\HasTree;
use Filament\Support\Contracts\TranslatableContentDriver;

trait BelongsToLivewire
{
    protected HasTree $livewire;

    public function livewire(HasTree $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasTree
    {
        return $this->livewire;
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return $this->getLivewire()->makeFilamentTranslatableContentDriver();
    }
}
