<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \BezhanSalleh\LanguageSwitch\LanguageSwitch::configureUsing(function (\BezhanSalleh\LanguageSwitch\LanguageSwitch $switch) {
            $switch
                ->locales(['it', 'en']) // Provide the locales your app uses
                ->labels([
                    'it' => '🇮🇹 Italiano',
                    'en' => '🇬🇧 English',
                ])
                ->visible(outsidePanels: true);
        });
    }
}
