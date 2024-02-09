<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Segment\Segment;
use Spatie\Emoji\Emoji;

class TrackImportCommand extends Command
{
    protected $signature = 'track:import {csvFile?}';

    protected $description = 'Import and create a Track from a CSV file';

    public function handle()
    {
        $csvFileName = $this->argument('csvFile');

        if (is_null($csvFileName)) {
            $csvFileName = 'trackexport.csv';
        }

        $csvFilePath = $this->getCsvFilePath($csvFileName);

        if (!File::exists($csvFilePath)) {
            $this->error("The specified CSV file ($csvFilePath) does not exist.");

            return;
        }

        $csvData = file($csvFilePath);
        $header = str_getcsv(array_shift($csvData), ';');

        $progressBar = $this->output->createProgressBar(count($csvData));
        $progressBar->setFormat('%current%/%max% <fg=green>[%bar%]</> %percent:3s%% %message%');
        $progressBar->setMessage('Importing track from CSV.', 'message');
        $progressBar->setBarCharacter('=');
        $progressBar->setProgressCharacter(Emoji::hourglassNotDone());
        $progressBar->setEmptyBarCharacter(' ');
        $progressBar->start();

        $successfulCount = 0;
        $failedCount = 0;

        foreach ($csvData as $line) {
            $data = str_getcsv($line, ';');

            usleep(150000);

            if ($data === false) {
                $failedCount++;

                continue;
            }

            $data = array_pad($data, count($header), '');

            $row = array_combine($header, $data);

            $userId = $row['userId'];
            $event = $row['event'];

            $properties = [];

            foreach ($row as $columnName => $value) {
                if ($columnName !== 'userId' && $columnName !== 'event' && ! empty(trim($value))) {
                    $properties[$columnName] = $value;
                }
            }

            ksort($properties);

            $result = $this->createGroupInSegment($userId, $event, $properties);

            if ($result) {
                $successfulCount++;
            } else {
                $failedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        if ($failedCount) {
            $this->info('');
            $this->error("Not all tracks could be created. Successful: $successfulCount, Failed: $failedCount.");
        } else {
            $this->error('');
            $this->info('The track has been successfully created.');
        }
    }

    private function createGroupInSegment($userId, $event, $properties)
    {
        $apiKey = $this->getApiKey();
        Segment::init($apiKey);

        $result = Segment::track([
            'userId' => $userId,
            'event' => $event,
            'properties' => $properties,
        ]);

        return $result;
    }

    private function getApiKey()
    {
        $filePath = 'segment/.apikey';

        if (File::exists($filePath)) {
            return File::get($filePath);
        } else {
            throw new \Exception('No Segment API key is currently set.');
        }
    }

    private function getCsvFilePath($csvFileName)
    {
        if (File::exists($csvFileName)) {
            return $csvFileName;
        }

        $csvFilePath = 'segment/' . $csvFileName;

        return $csvFilePath;
    }
}
