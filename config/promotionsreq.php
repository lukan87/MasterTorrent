<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Elite User Promotion Requirements
    |--------------------------------------------------------------------------
    */

    'elite_user' => [

        'account_age_months' => 5,

        'uploaded' => 536870912000,     // 500 GB
        'downloaded' => 268435456000,   // 250 GB

        'ratio' => 1.1,

        // Future community requirements
        'forum_posts' => 50,
        'comments' => 50,
        'thanks' => 50,
        'hnrs' => 0,

    ],

];