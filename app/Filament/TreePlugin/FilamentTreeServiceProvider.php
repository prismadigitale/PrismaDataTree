<?php

namespace App\Filament\TreePlugin;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use App\Filament\TreePlugin\Macros\BlueprintMarcos;

class FilamentTreeServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // Register Blade anonymous components with the correct namespace
        Blade::anonymousComponentPath(resource_path('views/filament/tree-plugin/components'), 'filament.tree-plugin');
        
        // Also register views for non-component templates
        $this->loadViewsFrom(resource_path('views/filament/tree-plugin'), 'filament.tree-plugin');
        
        FilamentAsset::register([
             // Css::make('filament-tree-css', asset('vendor/filament-tree/filament-tree.css')),
             AlpineComponent::make('filament-tree-component', asset('vendor/filament-tree/components/filament-tree-component.js')),
             Js::make('filament-tree-js', asset('vendor/filament-tree/filament-tree.js')),
        ], 'solution-forest/filament-tree');
    }

    public function register()
    {
        // Macros if needed
        Blueprint::mixin(new BlueprintMarcos);
    }
}
