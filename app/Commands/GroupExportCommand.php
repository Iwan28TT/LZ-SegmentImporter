<?php

namespace App\Commands;

use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

class GroupExportCommand extends Command
{
    protected $signature = 'group:export';

    protected $description = 'Group Export data to CSV';

    public function handle(Filesystem $filesystem)
    {
        $directory = 'segment';
        $csvFilePath = $directory.'/groupexport.csv';

        $header = ['userId', 'groupId', 'traits'];
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
