<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Segment\Segment;
use Spatie\Emoji\Emoji;

class IdentifyImportCommand extends Command
{
    protected $signature = 'identify:import {csvFile?}';

    protected $description = 'Import and identify users from a CSV file';

    public function handle()
    {
        $csvFileName = $this->argument('csvFile');

        if (is_null($csvFileName)) {
            $csvFileName = 'identifyexport.csv';
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
        $progressBar->setMessage('Identifying users from CSV', 'message');
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
            $traits = [];

            foreach ($row as $columnName => $value) {
                if ($columnName !== 'userId' && ! empty(trim($value))) {
                    $traits[$columnName] = $value;
                }
            }

            ksort($traits);

            $result = $this->identifyUserInSegment($userId, $traits);

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
            $this->error("Not all users could be identified. Successful: $successfulCount, Failed: $failedCount");
        } else {
            $this->error('');
            $this->info('All users have been successfully identified.');
        }
    }

    private function identifyUserInSegment($userId, $traits)
    {
        $apiKey = $this->getApiKey();
        Segment::init($apiKey);

        $result = Segment::identify([
            'userId' => $userId,
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
