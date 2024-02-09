<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Segment\Segment;
use Spatie\Emoji\Emoji;

class GroupImportCommand extends Command
{
    protected $signature = 'group:import {csvFile?}';

    protected $description = 'Import and create groups from a CSV file';

    public function handle()
    {
        $csvFileName = $this->argument('csvFile');

        if (is_null($csvFileName)) {
            $csvFileName = 'groupexport.csv';
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
        $progressBar->setMessage('Importing groups from CSV.', 'message');
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
            $groupId = $row['groupId'];

            

            $traits = [];

            foreach ($row as $columnName => $value) {
                if ($columnName !== 'userId' && $columnName !== 'groupId' && ! empty(trim($value))) {
                    $traits[$columnName] = $value;
                }
            }

            ksort($traits);

            $result = $this->createGroupInSegment($userId, $groupId, $traits);

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
            $this->error("Not all groups could be created. Successful: $successfulCount, Failed: $failedCount.");
        } else {
            $this->error('');
            $this->info('All groups have been successfully created.');
        }
    }

    private function createGroupInSegment($userId, $groupId, $traits)
    {
        $apiKey = $this->getApiKey();
        Segment::init($apiKey);

        $result = Segment::group([
            'userId' => $userId,
            'groupId' => $groupId,
            'traits' => $traits,
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