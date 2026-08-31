<?php

namespace App\Models;

class UserClass
{
    const int USER = 1;
    const int ELITE_USER = 2;
    const int VIP = 3;
    const int SUPERUSER = 4;
    const int UPLOADER = 5;
    const int MODERATOR = 6;
    const int ADMIN = 7;
    const int OWNER = 8;
    const int WEB_DEVELOPER = 9;

    public static function getClasses()
    {
        return [
            self::USER => 'User',
            self::ELITE_USER => 'Elite User',
            self::UPLOADER => 'Uploader',
            self::VIP => 'VIP',
            self::SUPERUSER => 'Special User',
            self::MODERATOR => 'Moderator',
            self::ADMIN => 'Admin',
            self::OWNER => 'Owner',
            self::WEB_DEVELOPER => 'Web Developer',
        ];
    }

    public static function getClassName($class)
{
    $classes = self::getClasses();
    return $classes[$class] ?? 'Unknown';
}


    public static function getClassColor($class)
    {
        $colors = [
            self::USER => 'SlateGrey',
            self::ELITE_USER => 'cyan',
            self::VIP => 'green',
            self::SUPERUSER => 'gold',
            self::UPLOADER => 'orange',
            self::MODERATOR => 'yellow',
            self::ADMIN => 'red',
            self::OWNER => 'DarkCyan',
            self::WEB_DEVELOPER => 'BurlyWood',
        ];

        return $colors[$class] ?? 'LightGray'; // default to gray if class not found
    }




   public static function permissions()
{
    $ownerPermissions = [
        'can_download' => true,
        'can_upload' => true,
        'can_edit_content' => true,
        'can_manage_users' => true,
        'can_view_admin_panel' => true,

        'view_topics' => true,
        'create_topics' => true,
        'reply' => true,

        'edit_posts' => true,
        'delete_posts' => true,
        'delete_topics' => true,
        'manage_topics' => true,

        'create_forums' => true,
        'create_categories' => true,
        'view_categories' => true,
        'edit_categories' => true,
        'delete_categories' => true,
        'edit_forums' => true,
        'delete_forums' => true,
        'create_polls' => true,
        'edit_polls' => true,
    ];

    $staffBase = [
    'view_topics' => true,
    'create_topics' => true,
    'reply' => true,

    'edit_posts' => true,
    'manage_topics' => true,

    'create_forums' => true,
    'view_categories' => true,
];


    return [
        self::USER => [
            'view_topics' => true,
            'reply' => true,
            'view_categories' => true,
        ],

        self::ELITE_USER => [
            'view_topics' => true,
            'create_topics' => true,
            'reply' => true,
            'view_categories' => true,
        ],

        self::VIP => [
            'view_topics' => true,
            'create_topics' => true,
            'reply' => true,
            'edit_own_posts' => true,
            'view_categories' => true,
        ],

        self::SUPERUSER => [
            'view_topics' => true,
            'create_topics' => true,
            'reply' => true,
            'edit_own_posts' => true,
            'view_categories' => true,
        ],

        self::UPLOADER => [
            'view_topics' => true,
            'create_topics' => true,
            'reply' => true,
            'view_categories' => true,
        ],

        self::MODERATOR => $staffBase,

      self::ADMIN => array_merge($staffBase, [
    'delete_posts' => true,
    'delete_topics' => true,
    'create_categories' => true,
    'edit_categories' => true,
]),


        self::OWNER => $ownerPermissions,

        // FULL ACCESS
        self::WEB_DEVELOPER => $ownerPermissions,
    ];
}


public static function userHasPermission($userClass, $permission): bool
{
    $permissions = self::permissions();

    // Return true only if the class exists AND the key exists AND it is true
    return !empty($permissions[$userClass][$permission]);
}


}