<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Segment\Segment;

class UpdateSegmentApiKeyCommand extends Command
{
    protected $signature = 'destination:set {api_key}';

    protected $description = 'Update Segment API key and send test event';

    public function handle(Filesystem $filesystem)
    {

        $apiKey = $this->argument('api_key');

        if (strlen($apiKey) !== 32) {
            $this->error('Wrong api key');

            return;
        }

        try {
            $directory = 'segment';
            if (! $filesystem->exists($directory)) {
                $filesystem->makeDirectory($directory, 0755, true);
            }

            $filePath = $directory.'/.apikey';
            $filesystem->put($filePath, $apiKey);

            // exec('attrib +h '.$filePath);

            $this->info('Segment API key has been updated: '.$apiKey);
            $this->sendTestEvent($apiKey);
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e->getMessage());
            $this->error('Token may be incorrect.');
        }
    }

    private function sendTestEvent($apiKey)
    {
        try {
            Segment::init($apiKey);
            Segment::track([
                'userId' => '1',
                'event' => 'Integration test',
            ]);
            $this->info('Segment API-Key is set, Test track event sent.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e->getMessage());
            $this->error('Token may be incorrect.');
        }
    }
}
