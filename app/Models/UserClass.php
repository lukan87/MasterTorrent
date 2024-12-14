<?php

namespace App\Models;

class UserClass
{
    const USER = 1;
    const ELITE_USER = 2;
    const VIP = 3;
    const SUPERUSER = 4;
    const UPLOADER = 5;
    const MODERATOR = 6;
    const ADMIN = 7;
    const OWNER = 8;

    public static function getClasses()
    {
        return [
            self::USER => 'User',
            self::ELITE_USER => 'Elite User',
            self::UPLOADER => 'Uploader',
            self::VIP => 'VIP',
            self::SUPERUSER => 'SuperUser',
            self::MODERATOR => 'Moderator',
            self::ADMIN => 'Admin',
            self::OWNER => 'Owner',
        ];
    }

    public static function getClassColor($class)
    {
        $colors = [
            self::USER => 'gray',
            self::ELITE_USER => 'cyan',
            self::VIP => 'green',
            self::SUPERUSER => 'purple',
            self::UPLOADER => 'orange',
            self::MODERATOR => 'yellow',
            self::ADMIN => 'red',
            self::OWNER => 'gold',
        ];

        return $colors[$class] ?? 'gray'; // default to gray if class not found
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
