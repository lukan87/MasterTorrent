<?php

namespace Tests\Integration;

use App\Models\User;
use App\Models\Invite;
use App\Models\Message;
use App\Services\WelcomeMessageService;
use App\Notifications\ActivateAccount;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

/** Connection-local temporary tables shadow real tables; no production data is modified. */
class EmailRegistrationTest extends \Illuminate\Foundation\Testing\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->detectEnvironment(fn () => 'testing');
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config([
            'database.connections.registration_test' => $connection,
            'database.default' => 'registration_test',
            'auth.email_registration' => true,
            'app.invite_only' => false,
            'session.driver' => 'array',
            'cache.default' => 'array',
            'mail.default' => 'array',
            'achievements.enabled' => false,
        ]);
        DB::setDefaultConnection('registration_test');
        User::flushEventListeners();
        Schema::create('users', function (Blueprint $t) {
            $t->temporary();
            $t->id();
            foreach (['name', 'email', 'password', 'recovery_code', 'user_class', 'IP', 'acceptpm', 'title', 'enabled', 'donor', 'info', 'passkey', 'invite_code', 'timezone', 'downloadpos'] as $field) {
                $t->string($field)->nullable();
            }
            $t->unsignedBigInteger('invited_by')->nullable();
            $t->boolean('subscribed')->default(false);
            $t->boolean('activation_pending')->default(false);
            $t->timestamp('email_verified_at')->nullable();
            $t->timestamp('banned_until')->nullable();
            $t->unsignedInteger('failed_attempts')->default(0);
            $t->rememberToken();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->temporary();
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        foreach ([
            'conversations' => 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(255), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(255), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'invites' => 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, inviter_id BIGINT, user_id BIGINT NULL, invite_code VARCHAR(255), is_used BOOLEAN DEFAULT 0, is_expired BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        Notification::fake();
    }

    protected function tearDown(): void
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS users, password_reset_tokens, conversations, messages, invites');
        DB::purge('registration_test');
        parent::tearDown();
    }

    private function data(): array
    {
        return ['name' => 'newmember', 'email' => 'member@example.test', 'password' => 'secret-password', 'password_confirmation' => 'secret-password'];
    }

    private function member(array $attributes = []): User
    {
        $user = new User;
        $user->forceFill(array_merge($this->data(), ['enabled' => 'yes', 'recovery_code' => Hash::make('private-code')], $attributes));
        unset($user->password_confirmation);
        $user->save();
        return $user;
    }

    public function test_email_registration_requires_activation_before_login(): void
    {
        $this->post('/register', $this->data())->assertRedirect(route('activation.notice'));
        $user = User::firstOrFail();
        $welcome = Message::where('receiver_id', $user->id)->sole();
        $this->assertSame(WelcomeMessageService::SUBJECT, $welcome->subject);
        $this->assertFalse($welcome->is_read);
        $this->assertSame(2, $welcome->sender_id);
        $this->assertStringContainsString('logging in and seeding', $welcome->body);
        $this->assertStringContainsString('Drop by each day', $welcome->body);
        $this->assertGuest();
        $this->assertTrue($user->activation_pending);
        $this->assertSame('no', $user->enabled);
        $this->assertNull($user->recovery_code);
        Notification::assertSentTo($user, ActivateAccount::class);
        $this->post('/login', ['name' => $user->name, 'password' => 'secret-password'])->assertSessionHasErrors('name');
        $this->assertGuest();
        $url = (new ActivateAccount)->toMail($user)->actionUrl;
        $this->get($url)->assertRedirect(route('login'));
        $this->assertFalse($user->fresh()->activation_pending);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->post('/login', ['name' => $user->name, 'password' => 'secret-password'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, Message::where('receiver_id', $user->id)->count());
    }

    public function test_legacy_registration_logs_in_immediately_and_requires_code(): void
    {
        config(['auth.email_registration' => false]);
        $this->post('/register', $this->data())->assertSessionHasErrors('recovery_code');
        $this->post('/register', $this->data() + ['recovery_code' => 'private-code'])->assertRedirect('/');
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('yes', $user->enabled);
        $this->assertFalse($user->activation_pending);
        $this->assertTrue(Hash::check('private-code', $user->recovery_code));
        $this->assertSame(WelcomeMessageService::SUBJECT, Message::where('receiver_id', $user->id)->sole()->subject);
        Notification::assertNothingSent();
    }

    public function test_invited_members_receive_the_welcome_and_inviter_keeps_their_notice(): void
    {
        $inviter = $this->member(['name' => 'inviter', 'email' => 'inviter@example.test']);
        $invite = Invite::create(['inviter_id' => $inviter->id, 'invite_code' => 'fixture-invite']);
        config(['app.invite_only' => true]);
        $this->post('/register', $this->data() + ['invite_code' => $invite->invite_code])->assertRedirect(route('activation.notice'));
        $member = User::where('name', 'newmember')->firstOrFail();
        $this->assertSame(WelcomeMessageService::SUBJECT, Message::where('receiver_id', $member->id)->sole()->subject);
        $this->assertSame('Invite Used', Message::where('receiver_id', $inviter->id)->sole()->subject);
        $this->assertTrue($invite->fresh()->is_used);
        $this->assertSame(2, Message::count());
    }

    public function test_invalid_registration_does_not_send_a_welcome(): void
    {
        config(['app.invite_only' => true]);
        $this->post('/register', $this->data() + ['invite_code' => 'invalid'])->assertSessionHasErrors('invite_code');
        $this->assertSame(0, Message::count());
        $this->assertSame(0, User::count());
    }

    public function test_expired_and_tampered_links_fail_and_used_links_cannot_enable_disabled_accounts(): void
    {
        $user = $this->member(['activation_pending' => true, 'enabled' => 'no']);
        $parameters = ['id' => $user->id, 'hash' => sha1($user->email)];
        $expired = URL::temporarySignedRoute('activation.verify', now()->subMinute(), $parameters);
        $valid = URL::temporarySignedRoute('activation.verify', now()->addHour(), $parameters);
        $this->get($expired)->assertSessionHasErrors('email');
        $this->get($valid.'tampered')->assertSessionHasErrors('email');
        $this->assertSame('no', $user->fresh()->enabled);
        $this->get($valid)->assertRedirect(route('login'));
        $user->refresh()->forceFill(['enabled' => 'no'])->save();
        $this->get($valid)->assertRedirect(route('login'));
        $this->assertSame('no', $user->fresh()->enabled);
    }

    public function test_email_reset_is_single_use_and_does_not_activate_or_login(): void
    {
        $user = $this->member(['activation_pending' => true, 'enabled' => 'no']);
        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $data = ['email' => $user->email, 'password' => 'changed-password', 'password_confirmation' => 'changed-password', 'token' => $token];
        $this->post('/password/reset', $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('changed-password', $user->fresh()->password));
        $this->assertTrue($user->fresh()->activation_pending);
        $this->assertSame('no', $user->fresh()->enabled);
        $this->assertGuest();
        $this->post('/password/reset', $data)->assertSessionHasErrors('email');
    }

    public function test_recovery_codes_cannot_bypass_email_mode_on_either_reset_route(): void
    {
        $user = $this->member();
        foreach (['/password/reset', '/custom-password/reset'] as $route) {
            $this->post($route, $this->data() + ['recovery_code' => 'private-code'])->assertSessionHasErrors('token');
        }
        $this->assertTrue(Hash::check('secret-password', $user->fresh()->password));
    }

    public function test_legacy_reset_matches_email_and_code_and_rotates_remember_token(): void
    {
        config(['auth.email_registration' => false]);
        $this->member(['email' => 'other@example.test', 'name' => 'other', 'recovery_code' => Hash::make('different-code')]);
        $user = $this->member(['remember_token' => 'old-token']);
        $data = ['email' => $user->email, 'password' => 'changed-password', 'password_confirmation' => 'changed-password', 'recovery_code' => 'wrong-code'];
        $this->post('/custom-password/reset', $data)->assertSessionHasErrors('recovery_code');
        $data['recovery_code'] = 'private-code';
        $this->post('/password/reset', $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('changed-password', $user->fresh()->password));
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->assertGuest();
    }

    public function test_resend_only_sends_for_pending_accounts_even_when_mode_is_disabled(): void
    {
        $pending = $this->member(['activation_pending' => true, 'enabled' => 'no']);
        config(['auth.email_registration' => false]);
        $this->post('/activation', ['email' => $pending->email])->assertSessionHas('status');
        Notification::assertSentTo($pending, ActivateAccount::class);
        $this->post('/activation', ['email' => 'missing@example.test'])->assertSessionHas('status');
        Notification::assertCount(1);
    }
    public function test_expired_reset_tokens_are_rejected(): void
    {
        $user = $this->member();
        $token = Password::createToken($user);
        DB::table('password_reset_tokens')->update(['created_at' => now()->subMinutes(61)]);
        $this->post('/password/reset', $this->data() + ['token' => $token])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_reset_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/password/email', ['email' => 'missing@example.test'])->assertSessionHas('status');
        }
        $this->post('/password/email', ['email' => 'missing@example.test'])->assertStatus(429);
    }

    public function test_mail_failure_leaves_a_resendable_pending_account(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Test mail failure'));
        $this->post('/register', $this->data())->assertRedirect(route('activation.notice'))->assertSessionHasErrors('email');
        $user = User::firstOrFail();
        $this->assertTrue($user->activation_pending);
        $this->assertSame('no', $user->enabled);
        $this->assertGuest();
    }

}
