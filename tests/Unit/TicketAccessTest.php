<?php

namespace Tests\Unit;

use App\Http\Controllers\StaffTicketController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketDashboardController;
use App\Http\Controllers\TicketReplyController;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TicketAccessTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        $user = new User;
        $user->forceFill(['id' => 10, 'user_class' => 1]);
        Auth::swap(Mockery::mock());
        Auth::shouldReceive('user')->andReturn($user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }

    public function test_only_owner_and_staff_can_view_ticket(): void
    {
        $ticket = new Ticket(['user_id' => 10]);
        $owner = new User;
        $owner->forceFill(['id' => 10, 'user_class' => 1]);
        $other = new User;
        $other->forceFill(['id' => 20, 'user_class' => 5]);
        $staff = new User;
        $staff->forceFill(['id' => 30, 'user_class' => 6]);
        self::assertTrue($ticket->canBeViewedBy($owner));
        self::assertFalse($ticket->canBeViewedBy($other));
        self::assertTrue($ticket->canBeViewedBy($staff));
    }

    public static function privateEndpoints(): array
    {
        return [['fetch'], ['typing'], ['typingStatus'], ['store']];
    }

    #[DataProvider('privateEndpoints')]
    public function test_other_members_cannot_access_ticket_endpoints(string $method): void
    {
        $tickets = Mockery::mock('alias:App\Models\Ticket');
        $ticket = $tickets;
        $tickets->shouldReceive('findOrFail')->with(42)->andReturn($ticket);
        $ticket->shouldReceive('canBeViewedBy')->andReturn(false);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('');
        try {
            $controller = new TicketReplyController;
            $method === 'store' ? $controller->store(Request::create('/', 'POST'), 42) : $controller->$method(42);
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
            throw $exception;
        }
    }

    public function test_locked_ticket_cannot_receive_replies(): void
    {
        $tickets = Mockery::mock('alias:App\Models\Ticket');
        $ticket = $tickets;
        $ticket->is_locked = true;
        $tickets->shouldReceive('findOrFail')->andReturn($ticket);
        $ticket->shouldReceive('canBeViewedBy')->andReturn(true);
        $this->expectException(HttpException::class);
        (new TicketReplyController)->store(Request::create('/', 'POST'), 42);
    }

    public function test_members_cannot_submit_internal_notes(): void
    {
        $tickets = Mockery::mock('alias:App\Models\Ticket');
        $ticket = $tickets;
        $ticket->is_locked = false;
        $ticket->status = 'Open';
        $tickets->shouldReceive('findOrFail')->andReturn($ticket);
        $ticket->shouldReceive('canBeViewedBy')->andReturn(true);
        $this->expectException(HttpException::class);
        (new TicketReplyController)->store(Request::create('/', 'POST', ['staff_note' => 1]), 42);
    }

    public static function completedTicketActions(): array
    {
        return [['store', 'Closed'], ['typing', 'Closed'], ['unlock', 'Closed'], ['store', 'Resolved'], ['typing', 'Resolved'], ['unlock', 'Resolved']];
    }

    #[DataProvider('completedTicketActions')]
    public function test_creator_cannot_reply_type_or_unlock_a_completed_ticket(string $action, string $status): void
    {
        $ticket = Mockery::mock('alias:App\Models\Ticket');
        $ticket->is_locked = false;
        $ticket->status = $status;
        $ticket->shouldReceive('findOrFail')->with(42)->andReturnSelf();
        $ticket->shouldReceive('canBeViewedBy')->andReturn(true);
        if ($action === 'unlock') {
            DB::swap(Mockery::mock());
            DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
            $ticket->shouldReceive('lockForUpdate')->andReturnSelf();
        }
        try {
            if ($action === 'unlock') {
                (new TicketController)->unlock(42);
            } elseif ($action === 'store') {
                (new TicketReplyController)->store(Request::create('/', 'POST', ['message' => 'Another reply']), 42);
            } else {
                (new TicketReplyController)->typing(42);
            }
            self::fail('Completed ticket permitted a member action.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_public_responses_exclude_both_internal_flags(): void
    {
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('when')->with(true, Mockery::type('callable'))->andReturnUsing(fn ($condition, $callback) => $callback($query));
        $query->shouldReceive('where')->once()->with('is_staff_note', false)->andReturnSelf();
        $query->shouldReceive('where')->once()->with('is_internal', false)->andReturnSelf();
        self::assertSame($query, (new TicketResponse)->scopeVisibleTo($query, Auth::user()));
    }

    public function test_member_cannot_download_internal_attachment(): void
    {
        $files = Mockery::mock('alias:App\Models\TicketAttachment');
        $files->shouldReceive('with')->with('response.ticket')->andReturnSelf();
        $files->shouldReceive('findOrFail')->with(7)->andReturn((object) ['response' => (object) [
            'ticket' => new Ticket(['user_id' => 10]), 'is_staff_note' => true, 'is_internal' => false,
        ]]);
        $this->expectException(HttpException::class);
        (new TicketReplyController)->download(7);
    }

    public static function staffControllers(): array
    {
        return [[StaffTicketController::class], [TicketDashboardController::class]];
    }

    #[DataProvider('staffControllers')]
    public function test_staff_controller_middleware_denies_members(string $class): void
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => Auth::user());
        $controller = new $class;
        $this->expectException(HttpException::class);
        foreach ($controller->getMiddleware() as $middleware) {
            if ($middleware['middleware'] instanceof \Closure) {
                $middleware['middleware']($request, fn () => self::fail('Staff action was permitted.'));
            }
        }
    }
}
