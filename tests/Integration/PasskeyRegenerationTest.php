<?php

namespace Tests\Integration;

use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class PasskeyRegenerationTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('PASSKEY_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set PASSKEY_MYSQL_TEST=1 to test against isolated temporary tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.passkey_test' => $connection, 'cache.default' => 'array']);
        DB::setDefaultConnection('passkey_test');
        DB::statement('CREATE TEMPORARY TABLE users (id BIGINT PRIMARY KEY, name VARCHAR(100), email VARCHAR(255), password VARCHAR(255), recovery_code VARCHAR(255), passkey VARCHAR(32), user_class INT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL)');
        DB::statement('CREATE TEMPORARY TABLE peers (id BIGINT PRIMARY KEY, user_id BIGINT)');
        DB::table('users')->insert(['id' => 1, 'name' => 'Member', 'password' => Hash::make('correct-password'), 'recovery_code' => Hash::make('recovery-secret'), 'passkey' => str_repeat('a', 32), 'user_class' => UserClass::USER]);
        DB::table('peers')->insert(['id' => 1, 'user_id' => 1]);
        Auth::guard('web')->setUser(User::find(1));
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('passkey_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function test_missing_and_incorrect_passwords_do_not_change_passkey_or_peers(): void
    {
        foreach (['', 'incorrect-password'] as $password) {
            try {
                (new ProfileController)->regeneratePasskey(Request::create('/profile/1/passkey/regenerate', 'PATCH', ['current_password' => $password]), 1);
                self::fail('Password validation should fail');
            } catch (ValidationException $e) {
                self::assertSame('passkey', $e->errorBag);
                self::assertArrayHasKey('current_password', $e->errors());
                self::assertSame(str_repeat('a', 32), User::find(1)->passkey);
                self::assertSame(1, DB::table('peers')->count());
            }
        }
    }

    public function test_correct_password_regenerates_and_redirects_to_edit(): void
    {
        $response = (new ProfileController)->regeneratePasskey(Request::create('/profile/1/passkey/regenerate', 'PATCH', ['current_password' => 'correct-password']), 1);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', User::find(1)->passkey);
        self::assertNotSame(str_repeat('a', 32), User::find(1)->passkey);
        self::assertSame(0, DB::table('peers')->count());
        self::assertSame(route('profile.edit', [1, 'Member']), $response->getTargetUrl());
    }

    public function test_other_member_cannot_regenerate_even_with_correct_password(): void
    {
        $actor = new User;
        $actor->forceFill(['id' => 2, 'user_class' => UserClass::USER]);
        Auth::guard('web')->setUser($actor);
        try {
            (new ProfileController)->regeneratePasskey(Request::create('/profile/1/passkey/regenerate', 'PATCH', ['current_password' => 'correct-password']), 1);
            self::fail('Expected forbidden');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
            self::assertSame(str_repeat('a', 32), User::find(1)->passkey);
        }
    }
    public function test_administrator_cannot_regenerate_another_members_passkey(): void
    {
        $actor = new User;
        $actor->forceFill(['id' => 2, 'user_class' => UserClass::ADMIN, 'password' => Hash::make('admin-password')]);
        Auth::guard('web')->setUser($actor);
        try {
            (new ProfileController)->regeneratePasskey(Request::create('/profile/1/passkey/regenerate', 'PATCH', ['current_password' => 'admin-password']), 1);
            self::fail('Expected forbidden');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
            self::assertSame(str_repeat('a', 32), User::find(1)->passkey);
            self::assertSame(1, DB::table('peers')->count());
        }
    }

    public function test_admin_cannot_submit_password_changes_for_another_account(): void
    {
        $actor = new User;
        $actor->forceFill(['id' => 2, 'user_class' => UserClass::ADMIN]);
        Auth::guard('web')->setUser($actor);
        foreach ([['new_password' => 'new-password'], ['current_password' => 'correct-password']] as $payload) {
            try {
                (new ProfileController)->update(Request::create('/profile/1/Member', 'PUT', $payload), 1, 'Member');
                self::fail('Expected forbidden');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                self::assertSame(403, $e->getStatusCode());
                self::assertTrue(Hash::check('correct-password', User::find(1)->password));
                self::assertTrue(Hash::check('recovery-secret', User::find(1)->recovery_code));
            }
        }
    }

    public function test_owner_password_change_requires_valid_current_password_and_recovery_code(): void
    {
        $payload = ['new_password' => 'replacement-password', 'new_password_confirmation' => 'replacement-password'];
        foreach ([[], ['current_password' => 'wrong-password', 'verification_recovery_code' => 'recovery-secret'], ['current_password' => 'correct-password', 'verification_recovery_code' => 'wrong-code']] as $credentials) {
            try {
                (new ProfileController)->update(Request::create('/profile/1/Member', 'PUT', $payload + $credentials), 1, 'Member');
                self::fail('Expected validation failure');
            } catch (ValidationException $e) {
                self::assertNotEmpty($e->errors());
                self::assertTrue(Hash::check('correct-password', User::find(1)->password));
            }
        }
        (new ProfileController)->update(Request::create('/profile/1/Member', 'PUT', $payload + ['current_password' => 'correct-password', 'verification_recovery_code' => 'recovery-secret']), 1, 'Member');
        self::assertTrue(Hash::check('replacement-password', User::find(1)->password));
    }

    public function test_admin_can_change_recovery_code_without_changing_password_or_passkey(): void
    {
        $actor = new User;
        $actor->forceFill(['id' => 2, 'user_class' => UserClass::ADMIN]);
        Auth::guard('web')->setUser($actor);
        (new ProfileController)->update(Request::create('/profile/1/Member', 'PUT', [
            'name' => 'Member', 'email' => 'member@example.test', 'recovery_code' => 'replacement-code',
        ]), 1, 'Member');
        $user = User::find(1);
        self::assertTrue(Hash::check('replacement-code', $user->recovery_code));
        self::assertTrue(Hash::check('correct-password', $user->password));
        self::assertSame(str_repeat('a', 32), $user->passkey);
    }

}
