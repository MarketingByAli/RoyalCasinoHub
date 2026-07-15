<?php

return [

    'stage' => env('BETTING_STAGE', 'play_money'),

    'minimum_age' => (int) env('BETTING_MINIMUM_AGE', 18),

    'currency' => env('BETTING_CURRENCY', 'POINTS'),

    'starter_points' => (int) env('BETTING_STARTER_POINTS', 10000),

    'max_stake_per_market' => (int) env('BETTING_MAX_STAKE', 5000),

    'max_open_liability_per_user' => (int) env('BETTING_MAX_LIABILITY', 20000),

    'default_dispute_window_hours' => 24,

    'invite_expiry_days' => 7,

    'prohibited_keywords' => [
        'suicide', 'self-harm', 'murder', 'death', 'assault', 'terrorism',
        'minor', 'child', 'underage', 'rape', 'harassment',
    ],

    /*
    | Stage 1 gate — real-money features must not ship until all items are satisfied.
    | Licensing, KYC/PSP vendors, legal pages, geo/RG controls are prerequisites.
    */
    'stage1_gate' => [
        'gambling_licence_obtained' => false,
        'legal_sign_off_crossed_bets' => false,
        'customer_funds_segregation' => false,
        'kyc_provider_selected' => false,
        'gambling_psp_selected' => false,
        'legal_pages_published' => false,
        'geolocation_enforcement' => false,
        'responsible_gambling_limits' => false,
    ],

    'real_money_enabled' => env('BETTING_REAL_MONEY', false) && env('BETTING_STAGE') === 'real_money',

];
