<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Message Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limit' => env('MESSAGE_RATE_LIMIT', 2),
    'rate_interval' => env('MESSAGE_RATE_INTERVAL', 5),
    'max_length' => env('MESSAGE_MAX_LENGTH', 160),

];
