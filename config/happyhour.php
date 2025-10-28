<?php

return [
    // Chance for HH to trigger automatically (1 in X)
    'random_chance' => 3,

    // Default duration if not specified (hours)
    'default_duration_hours' => 2,

    // Default upload multiplier if not specified
    'default_upload_multiplier' => 3,

    // Default free download setting if not specified
    'default_free_download' => true,

    // Day-specific themes
    'day_themes' => [
        0 => [ // Sunday
            'name' => 'Super Sunday',
            'upload_multiplier' => 8,
            'duration_hours' => 10,
            'free_download' => true,
        ],
        1 => [ // Monday
            'name' => 'Mega Monday',
            'upload_multiplier' => 3,
            'duration_hours' => 5,
            'free_download' => false,
        ],
        2 => [ // Tuesday
            'name' => 'Turbo Tuesday',
            'upload_multiplier' => 4,
            'duration_hours' => 6,
            'free_download' => true,
        ],
        3 => [ // Wednesday
            'name' => 'Wild Wednesday',
            'upload_multiplier' => 3,
            'duration_hours' => 7,
            'free_download' => true,
        ],
        4 => [ // Thursday
            'name' => 'Thrilling Thursday',
            'upload_multiplier' => 3,
            'duration_hours' => 6,
            'free_download' => false,
        ],
        5 => [ // Friday
            'name' => 'Frenzy Friday',
            'upload_multiplier' => 3,
            'duration_hours' => 8,
            'free_download' => true,
        ],
        6 => [ // Saturday
            'name' => 'Super Saturday',
            'upload_multiplier' => 4,
            'duration_hours' => 9,
            'free_download' => true,
        ],
    ],
];
