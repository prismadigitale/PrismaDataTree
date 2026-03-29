<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateChangelog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:changelog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a CHANGELOG.md file based on git tags and commits.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Changelog generation...');

        // 1. Get all tags sorted by date (newest first)
        $tagsOutput = shell_exec('git tag --sort=-creatordate');
        if (empty(trim((string)$tagsOutput))) {
            $this->warn('No git tags found. Cannot generate changelog.');
            return 1;
        }

        $tags = array_filter(explode("\n", trim($tagsOutput)));

        $markdown = "# Changelog\n\nTutti i cambiamenti notevoli a questo progetto sono documentati in questo file.\n\n";

        // For each tag to the next older tag, get the commits
        for ($i = 0; $i < count($tags); $i++) {
            $currentTag = $tags[$i];
            
            // Re-fetch the tag date properly formatted
            $dateOutput = shell_exec("git log -1 --format=%ai {$currentTag}");
            $date = $dateOutput ? substr(trim($dateOutput), 0, 10) : 'Unknown Date';

            $markdown .= "## [{$currentTag}] - {$date}\n";

            $previousTag = $tags[$i + 1] ?? null;

            if ($previousTag) {
                // Get commits between previous and current tag
                $logCommand = "git log {$previousTag}..{$currentTag} --pretty=format:\"- %s (%h)\"";
            } else {
                // Get all commits up to the first tag
                $logCommand = "git log {$currentTag} --pretty=format:\"- %s (%h)\"";
            }

            $commits = shell_exec($logCommand);

            if ($commits) {
                // Filter out non-important commits if needed, or format them.
                $commitsList = explode("\n", trim($commits));
                foreach ($commitsList as $commit) {
                    // Skip empty
                    if (trim($commit) === '') continue;
                    
                    // Skip merge commits or generic "bump version" if desired, but for now we include all.
                    // If you want to filter out "Avanzamento di versione":
                    // if (str_contains(strtolower($commit), 'avanzamento di versione')) continue;
                    
                    $markdown .= "{$commit}\n";
                }
            } else {
                $markdown .= "- Nessun commit visibile per questo tag.\n";
            }

            $markdown .= "\n";
        }

        // Save to CHANGELOG.md in base path
        $path = base_path('CHANGELOG.md');
        File::put($path, $markdown);

        $this->info("CHANGELOG.md generated successfully at {$path}");

        // Version Validation check
        $latestTag = $tags[0];
        $currentAppVersion = config('app.version');

        // Extract numbers only to compare, or compare directly.
        // Assuming tags are like "v0.5.0" and app.version is "0.5.0" or "v0.5.0"
        $latestTagClean = ltrim($latestTag, 'v');
        $currentAppVersionClean = ltrim((string)$currentAppVersion, 'v');

        if ($latestTagClean !== $currentAppVersionClean) {
            $this->warn("ATTENZIONE! Il tag Git più recente è '{$latestTag}' ma in config/app.php la versione impostata è '{$currentAppVersion}'. Ricordati di allinearli!");
        } else {
            $this->info("La versione in config/app.php ({$currentAppVersion}) è correttamente allineata con l'ultimo tag ({$latestTag}).");
        }

        return 0;
    }
}
