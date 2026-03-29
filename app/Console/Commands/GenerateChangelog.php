<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-changelog')]
#[Description('Command description')]
class GenerateChangelog extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
