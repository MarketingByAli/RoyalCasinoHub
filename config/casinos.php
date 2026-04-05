<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default listing image (no website or capture failed)
    |--------------------------------------------------------------------------
    |
    | Full URL to an image (e.g. CDN) used when a casino has no website or
    | automatic capture is unavailable. Leave empty to show no image until
    | you configure it.
    |
    */
    'default_screenshot_url' => env('DEFAULT_CASINO_SCREENSHOT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Website screenshot capture
    |--------------------------------------------------------------------------
    |
    | driver: "microlink" uses api.microlink.io (optional MICROLINK_API_KEY for
    | higher limits). "none" skips remote capture; only the default URL above
    | is applied when there is no website or you handle images manually.
    |
    | For self-hosted capture, consider spatie/browsershot + Chrome instead.
    |
    */
    'screenshot' => [
        'driver' => env('CASINO_SCREENSHOT_DRIVER', 'microlink'),
        'microlink_api_key' => env('MICROLINK_API_KEY'),
    ],

];
