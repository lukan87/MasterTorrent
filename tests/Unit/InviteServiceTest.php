<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\InviteService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class InviteServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Container;
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        $app->instance('config', new Repository(['app' => ['invite_only' => true]]));
        $app->instance('validator', new Factory(
            new Translator(new ArrayLoader, 'en'), $app
        ));
        DB::swap(Mockery::mock());
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }

    private function lookup(?object $invite): void
    {
        $model = Mockery::mock('alias:App\\Models\\Invite');
        $query = Mockery::mock();
        $model->shouldReceive('where')->once()->with('invite_code', 'valid-code')->andReturn($query);
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($invite);
    }

    public function test_redemption_records_member_and_sends_one_private_message(): void
    {
        $invite = Mockery::mock();
        $invite->is_used = false;
        $invite->expired = false;
        $invite->inviter = new User;
        $invite->inviter_id = 10;
        $invite->invite_code = 'valid-code';
        $this->lookup($invite);
        $member = (new User)->setRawAttributes(['id' => 42, 'name' => 'New [Member]']);
        $invite->shouldReceive('update')->once()->with(['is_used' => true, 'user_id' => 42]);
        $url = Mockery::mock();
        $url->shouldReceive('route')->once()->with('profile.show', ['id' => 42, 'name' => 'New [Member]'], true)->andReturn('https://example.test/profile/42');
        Container::getInstance()->instance('url', $url);
        $messages = Mockery::mock('alias:App\\Services\\SystemMessageService');
        $messages->shouldReceive('send')->once()->with(2, 10, 'Invite Used', Mockery::on(
            fn ($body) => str_contains($body, 'valid-code') && str_contains($body, 'New &#91;Member&#93;') && str_contains($body, '/profile/42')
        ));
        self::assertSame($member, (new InviteService)->register('valid-code', function ($actual) use ($invite, $member) {
            self::assertSame($invite, $actual);

            return $member;
        }));
    }

    public function test_used_code_cannot_create_another_account(): void
    {
        $this->lookup((object) ['is_used' => true]);
        $this->expectException(ValidationException::class);
        (new InviteService)->register('valid-code', fn () => self::fail('Must not create an account'));
    }

    public function test_expired_code_is_rejected_without_waiting_for_scheduler(): void
    {
        $this->lookup((object) ['is_used' => false, 'expired' => true]);
        $this->expectException(ValidationException::class);
        (new InviteService)->register('valid-code', fn () => self::fail('Must not create an account'));
    }

    public function test_unknown_code_is_rejected(): void
    {
        $this->lookup(null);
        $this->expectException(ValidationException::class);
        (new InviteService)->register('valid-code', fn () => self::fail('Must not create an account'));
    }

    public function test_open_registration_without_code_does_not_claim_an_inviter(): void
    {
        config(['app.invite_only' => false]);
        $member = new User;
        self::assertSame($member, (new InviteService)->register(null, function ($invite) use ($member) {
            self::assertNull($invite);

            return $member;
        }));
    }

    public function test_open_registration_still_validates_supplied_code(): void
    {
        config(['app.invite_only' => false]);
        $this->lookup(null);
        $this->expectException(ValidationException::class);
        (new InviteService)->register('valid-code', fn () => self::fail('Must not create an account'));
    }

    public function test_revoke_is_scoped_to_owner_and_preserves_used_invites(): void
    {
        $user = (new User)->setRawAttributes(['id' => 10]);
        $model = Mockery::mock('alias:App\\Models\\Invite');
        $query = Mockery::mock();
        $model->shouldReceive('where')->once()->with('inviter_id', 10)->andReturn($query);
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('findOrFail')->once()->with(99)->andReturn((object) ['is_used' => true]);
        $this->expectException(ValidationException::class);
        (new InviteService)->revoke($user, 99);
    }

    public function test_creation_does_not_spend_an_empty_balance(): void
    {
        $user = Mockery::mock('alias:App\\Models\\User');
        $user->id = 10;
        $user->user_class = 7;
        $user->invites = 0;
        $query = Mockery::mock();
        $user->shouldReceive('whereKey')->once()->with(10)->andReturn($query);
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('firstOrFail')->once()->andReturn($user);
        $user->shouldNotReceive('decrement');
        $this->expectException(ValidationException::class);
        (new InviteService())->create($user);
    }

    public function test_active_invite_revocation_refunds_exactly_one_credit(): void
    {
        $user = Mockery::mock('alias:App\\Models\\User');
        $user->id = 10;
        $model = Mockery::mock('alias:App\\Models\\Invite');
        $query = Mockery::mock();
        $invite = Mockery::mock();
        $invite->is_used = false;
        $invite->expired = false;
        $model->shouldReceive('where')->once()->with('inviter_id', 10)->andReturn($query);
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('findOrFail')->once()->with(99)->andReturn($invite);
        $invite->shouldReceive('delete')->once();
        $user->shouldReceive('whereKey')->once()->with(10)->andReturn($query);
        $query->shouldReceive('increment')->once()->with('invites');
        (new InviteService())->revoke($user, 99);
        $this->addToAssertionCount(1);
    }
}
