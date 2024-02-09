<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GetSegmentApiKeyCommand extends Command
{
    protected $signature = 'destination:get';

    protected $description = 'Get the current Segment API key';

    public function handle(Filesystem $filesystem)
    {
        $filePath = 'segment/.apikey';

        if ($filesystem->exists($filePath)) {
            $apiKey = $filesystem->get($filePath);
            $this->info('Current Segment API key is: '.$apiKey);
        } else {
            $this->error('No Segment API key is currently set.');
        }
    }
}
