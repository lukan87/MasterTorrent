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
            self::USER => 'gray',
            self::ELITE_USER => 'cyan',
            self::VIP => 'green',
            self::SUPERUSER => 'gold',
            self::UPLOADER => 'orange',
            self::MODERATOR => 'yellow',
            self::ADMIN => 'red',
            self::OWNER => 'purple',
            self::WEB_DEVELOPER => 'SlateBlue',
        ];

        return $colors[$class] ?? 'LightGray'; // default to gray if class not found
    }




    public static function permissions()
    {
        return [
            self::USER => [
                'can_download' => true,
                'can_upload' => false,
                'can_edit_content' => false,
                'can_manage_users' => false,
                'can_view_admin_panel' => false,
                'view_topics' => true,
                'reply' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => false,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::ELITE_USER => [
                'can_download' => true,
                'can_upload' => false,
                'can_edit_content' => false,
                'can_manage_users' => false,
                'can_view_admin_panel' => false,
                'view_topics' => true,
                'create_topics' => true,
                'reply' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => false,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::VIP => [
                'can_download' => true,
                'can_upload' => true,
                'can_edit_content' => false,
                'can_manage_users' => false,
                'can_view_admin_panel' => false,
                'view_topics' => true,
                'create_topics' => true,
                'reply' => true,
                'edit_own_posts' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => false,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::SUPERUSER => [
                'can_download' => true,
                'can_upload' => true,
                'can_edit_content' => false,
                'can_manage_users' => false,
                'can_view_admin_panel' => false,
                'view_topics' => true,
                'create_topics' => true,
                'reply' => true,
                'edit_own_posts' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => false,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::UPLOADER => [
                'can_download' => true,
                'can_upload' => true,
                'can_edit_content' => true,
                'can_manage_users' => false,
                'can_view_admin_panel' => false,
                'view_topics' => true,
                'create_topics' => true,
                'reply' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => false,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::MODERATOR => [
                'can_download' => true,
                'can_upload' => true,
                'can_edit_content' => true,
                'can_manage_users' => true,
                'can_view_admin_panel' => true,
                'view_topics' => true,
                'create_topics' => true,
                'reply' => true,
                'edit_posts' => true,
                'manage_topics' => true,
                'create_polls' => false,
                'edit_polls' => false,
                'create_categories' => true,
                'view_categories' => true,
                'edit_categories' => false,
                'delete_categories' => false,
            ],
            self::ADMIN => [
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
                'create_polls' => true,
                'edit_polls' =>true,
                'create_categories' => true,
                'view_categories' => true,
                'edit_categories' => true,
                'delete_categories' => false,
            ],
            self::OWNER => [
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
                'create_polls' => true,
                'edit_polls' => true,
                'create_categories' => true,
                'view_categories' => true,
                'edit_categories' => true,
                'delete_categories' => true,
            ],
        ];
    }


    public static function userHasPermission($userClass, $permission)
    {
        $permissions = self::permissions();

        return isset($permissions[$userClass]) && $permissions[$userClass][$permission] ?? false;
    }
}
