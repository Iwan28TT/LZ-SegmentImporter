<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Segment\Segment;

class SegmentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $apiKey = config('segment.api_key');

        if (! empty($apiKey)) {
            Segment::init($apiKey);
        } else {

        }
    }
}
