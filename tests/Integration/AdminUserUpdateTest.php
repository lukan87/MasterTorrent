<?php

namespace Tests\Integration;

use App\Http\Controllers\Admin\UserController;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class AdminUserUpdateTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('ADMIN_USER_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set ADMIN_USER_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }

        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config([
            'database.connections.admin_user_test' => $connection,
            'cache.default' => 'array',
            'session.driver' => 'array',
            'achievements.awarding_enabled' => false,
        ]);
        DB::setDefaultConnection('admin_user_test');

        foreach ([
            'users' => "id BIGINT PRIMARY KEY, name VARCHAR(100), email VARCHAR(255), user_class INT, info TEXT NULL, profile_image TEXT NULL, uploadpos VARCHAR(3) DEFAULT 'no', warned BOOLEAN DEFAULT 0, warned_reason TEXT NULL, warned_until TIMESTAMP NULL, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL",
            'users_timeline' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, staff_id BIGINT, comment TEXT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'conversations' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(255), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(255), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([
            'id' => 10, 'name' => 'Member', 'email' => 'member@example.test',
            'user_class' => UserClass::USER, 'info' => '', 'profile_image' => '', 'warned_reason' => '',
        ]);
        $staff = new User;
        $staff->forceFill(['id' => 20, 'name' => 'lukan87', 'user_class' => UserClass::ADMIN]);
        Auth::guard('web')->setUser($staff);
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('admin_user_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function update(array $changes = []): void
    {
        (new UserController)->update(Request::create('/admin/users/10', 'PUT', $changes + [
            'name' => 'Member', 'email' => 'member@example.test',
            // These are the values after ConvertEmptyStringsToNull middleware.
            'info' => null, 'profile_image' => null, 'warned' => '0',
            'warned_reason' => null, 'warned_until' => null,
        ]), 10);
    }

    public function test_upload_permission_logs_actual_change_without_empty_profile_entries(): void
    {
        $this->update(['uploadpos' => 'yes']);

        self::assertSame('yes', User::findOrFail(10)->uploadpos);
        self::assertSame([
            '⚙️ Upload permission changed from no to yes by lukan87',
        ], DB::table('users_timeline')->pluck('comment')->all());
        self::assertSame([
            'Upload permission changed from no to yes by lukan87.',
        ], DB::table('messages')->pluck('body')->all());

        $this->update(['uploadpos' => 'yes']);
        self::assertSame(1, DB::table('users_timeline')->count());
        self::assertSame(1, DB::table('messages')->count());

        $this->update(['uploadpos' => 'no']);
        self::assertSame('no', User::findOrFail(10)->uploadpos);
        self::assertSame('⚙️ Upload permission changed from yes to no by lukan87', DB::table('users_timeline')->orderByDesc('id')->value('comment'));
    }

    public function test_unchanged_empty_fields_do_not_generate_logs_or_notifications(): void
    {
        foreach (['', null] as $empty) {
            DB::table('users')->where('id', 10)->update([
                'info' => $empty, 'profile_image' => $empty, 'warned_reason' => $empty,
            ]);
            $this->update();
            self::assertSame(0, DB::table('users_timeline')->count());
            self::assertSame(0, DB::table('messages')->count());
        }
    }

    public function test_real_profile_changes_and_clearing_values_are_still_logged(): void
    {
        $this->update(['info' => 'New biography', 'profile_image' => 'https://example.test/avatar.png']);
        self::assertSame([
            '📝 Profile information changed from [empty] to New biography by lukan87',
            '🖼️ Profile image changed from [empty] to https://example.test/avatar.png by lukan87',
        ], DB::table('users_timeline')->orderBy('id')->pluck('comment')->all());
        self::assertSame('New biography', User::findOrFail(10)->info);

        $this->update();
        self::assertSame([
            '📝 Profile information changed from New biography to [empty] by lukan87',
            '🖼️ Profile image changed from https://example.test/avatar.png to [empty] by lukan87',
        ], DB::table('users_timeline')->orderBy('id')->pluck('comment')->slice(2)->values()->all());
        self::assertNull(User::findOrFail(10)->info);
        self::assertNull(User::findOrFail(10)->profile_image);
        self::assertSame(2, DB::table('messages')->count());
    }
}
