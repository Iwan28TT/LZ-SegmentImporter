<?php

namespace App\Commands;

use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

class TrackExportCommand extends Command
{
    protected $signature = 'track:export';

    protected $description = 'Track Export data to CSV';

    public function handle(Filesystem $filesystem)
    {
        $directory = 'segment';
        $csvFilePath = $directory.'/trackexport.csv';

        $header = ['userId', 'event', 'property'];
        $csvContent = implode(';', $header).PHP_EOL;

        if (! $filesystem->exists($directory)) {
            $filesystem->makeDirectory($directory, 0755, true);
        }

        if (file_put_contents($csvFilePath, $csvContent) !== false) {
            $this->info('CSV file has been generated successfully: '.$csvFilePath);
        } else {
            $this->error('Failed to generate CSV file.');
        }
    }
}
