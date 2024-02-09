<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class AssistentCommand extends Command
{
    protected $signature = 'assistent';

    protected $description = 'Help Assistent';

    public function handle()
    {
        $this->info('');
        $this->line('Set the segment api key');
        $this->info('php segment.phar destination:set "Your\Api\Key"');
        $this->line('');
        $this->line('Get the current segment api key');
        $this->info('php segment.phar destination:get');
        $this->info('');
        $this->line('Export a csv file with some required columns');
        $this->info('php segment.phar track:export');
        $this->info('php segment.phar identify:export');
        $this->info('php segment.phar group:export');
        $this->info('');
        $this->line('Import a csv file Example');
        $this->info('php segment.phar track:import "C:\Users\Probo\OneDrive - Probo\Dekstop\csv\probo.csv"');
        $this->line('');
        $this->line('Import a csv file Usage');
        $this->info('php segment.phar track:import "Your\Path\To\Csv\File"
php segment.phar identify:import "Your\Path\To\Csv\File"
php segment.phar group:import "Your\Path\To\Csv\File"
        ');
    }
}
