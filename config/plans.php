<?php

return [
    'default' => 'free',

    'plans' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'billing_period' => 'month',
            'limits' => [
                'people' => 50,
                'users' => 3,
                'ministries' => 3,
                'groups' => 2,
                'events_per_month' => 20,
                'storage_mb' => 100,
            ],
            'features' => [
                'dashboard', 'people', 'groups', 'ministries', 'events', 'attendance',
            ],
        ],
        'standard' => [
            'name' => 'Standard',
            'price' => 9,
            'billing_period' => 'month',
            'limits' => [
                'people' => 300,
                'users' => 15,
                'ministries' => 15,
                'groups' => 10,
                'events_per_month' => 100,
                'storage_mb' => 1000,
            ],
            'features' => [
                'dashboard', 'people', 'groups', 'ministries', 'events', 'attendance',
                'finances', 'reports', 'boards', 'announcements', 'resources',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19,
            'billing_period' => 'month',
            'limits' => [
                'people' => -1,
                'users' => -1,
                'ministries' => -1,
                'groups' => -1,
                'events_per_month' => -1,
                'storage_mb' => 5000,
            ],
            'features' => [
                'dashboard', 'people', 'groups', 'ministries', 'events', 'attendance',
                'finances', 'reports', 'boards', 'announcements', 'resources',
                'website', 'google_calendar', 'telegram_bot', 'custom_roles',
            ],
        ],
    ],
];
