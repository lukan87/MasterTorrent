<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Mockery;
use PHPUnit\Framework\TestCase;

class TicketViewTest extends TestCase
{
    private Factory $views;

    private Container $previous;

    private string $temporary;

    private User $viewer;

    private Store $session;

    protected function setUp(): void
    {
        $this->previous = Container::getInstance();
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();
        $app->instance('config', new Repository);
        $this->temporary = sys_get_temp_dir().'/ticket-views-'.bin2hex(random_bytes(6));
        mkdir($this->temporary.'/layouts', 0700, true);
        mkdir($this->temporary.'/compiled');
        file_put_contents($this->temporary.'/layouts/app.blade.php', '<html><body>@yield("content")</body></html>');
        $files = new Filesystem;
        $resolver = new EngineResolver;
        $resolver->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $this->temporary.'/compiled')));
        $finder = new FileViewFinder($files, [$this->temporary, dirname(__DIR__, 2).'/resources/views']);
        $finder->addNamespace('pagination', dirname(__DIR__, 2).'/vendor/laravel/framework/src/Illuminate/Pagination/resources/views');
        $this->views = new Factory($resolver, $finder, new Dispatcher($app));
        $this->views->setContainer($app);
        $this->views->share('__env', $this->views);
        $this->views->share('errors', new ViewErrorBag);
        $app->instance('view', $this->views);
        $app->instance('Illuminate\Contracts\View\Factory', $this->views);
        Paginator::viewFactoryResolver(fn () => $this->views);
        $url = Mockery::mock();
        $url->shouldReceive('route')->andReturnUsing(fn ($name) => '/test/'.$name);
        $app->instance('url', $url);
        $auth = Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('user')->andReturnUsing(fn () => $this->viewer);
        $auth->shouldReceive('id')->andReturnUsing(fn () => $this->viewer->id);
        $app->instance('auth', $auth);
        $this->session = new Store('test', new ArraySessionHandler(120));
        $app->instance('session', $this->session);
        $request = Request::create('/tickets');
        $request->setLaravelSession($this->session);
        $app->instance('request', $request);
        $this->viewer = $this->user(10);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->temporary);
        Paginator::viewFactoryResolver(fn () => app('view'));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previous);
        Container::setInstance($this->previous);
        Mockery::close();
    }

    private function user(int $id, int $class = UserClass::USER): User
    {
        $user = new User;
        $user->setDateFormat('Y-m-d H:i:s');

        return $user->forceFill(['id' => $id, 'name' => 'Member <unsafe>', 'user_class' => $class, 'created_at' => now()->subDays(45), 'uploaded' => User::UPLOADER_MIN_UPLOAD, 'downloaded' => 0]);
    }

    private function ticket(bool $locked = false): Ticket
    {
        $ticket = new Ticket;
        $ticket->setDateFormat('Y-m-d H:i:s');
        $ticket->forceFill(['id' => 42, 'user_id' => 10, 'title' => 'Help <script>alert(1)</script>', 'slug' => 'help', 'status' => 'Open', 'priority' => 'Medium', 'is_locked' => $locked, 'created_at' => now(), 'updated_at' => now(), 'responses_count' => 1]);
        $ticket->setRelation('user', $this->user(10));
        $ticket->setRelation('category', null);
        $ticket->setRelation('assignedStaff', null);
        $ticket->setRelation('claimedBy', null);
        $ticket->setRelation('events', collect());
        $response = new TicketResponse;
        $response->setDateFormat('Y-m-d H:i:s');
        $response->forceFill(['id' => 1, 'message' => '<img src=x onerror=alert(1)>', 'created_at' => now()]);
        $response->setRelation('user', $this->user(10));
        $response->setRelation('attachments', collect());
        $ticket->setRelation('responses', collect([$response]));

        return $ticket;
    }

    public function test_member_conversation_escapes_content_and_hides_staff_controls(): void
    {
        $html = $this->views->make('tickets.show', ['ticket' => $this->ticket(), 'staffMembers' => collect()])->render();
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        self::assertStringNotContainsString('Staff actions', $html);
        self::assertStringNotContainsString('name="staff_note"', $html);
        self::assertStringContainsString('name="message"', $html);
    }

    public function test_staff_has_assignment_and_note_controls(): void
    {
        $this->viewer = $this->user(20, UserClass::ADMIN);
        $html = $this->views->make('tickets.show', ['ticket' => $this->ticket(), 'staffMembers' => collect([$this->viewer])])->render();
        self::assertStringContainsString('Staff actions', $html);
        self::assertStringContainsString('name="staff_id"', $html);
        self::assertStringContainsString('name="staff_note" value="1"', $html);
    }

    public function test_locked_ticket_has_unlock_action_without_reply_form(): void
    {
        $html = $this->views->make('tickets.show', ['ticket' => $this->ticket(true), 'staffMembers' => collect()])->render();
        self::assertStringContainsString('tickets.unlock', $html);
        self::assertStringNotContainsString('name="message"', $html);
    }

    public function test_completed_ticket_directs_creator_to_new_ticket_without_reply_or_unlock(): void
    {
        foreach ([['Closed', false], ['Closed', true], ['Resolved', false], ['Resolved', true]] as [$status, $locked]) {
            $ticket = $this->ticket($locked);
            $ticket->status = $status;
            $html = $this->views->make('tickets.show', ['ticket' => $ticket, 'staffMembers' => collect()])->render();
            self::assertStringContainsString('Ticket '.strtolower($status), $html);
            self::assertStringContainsString('Create a new ticket', $html);
            self::assertStringContainsString('tickets.create', $html);
            self::assertStringNotContainsString('name="message"', $html);
            self::assertStringNotContainsString('tickets.unlock', $html);
        }
    }

    public function test_create_restores_input_and_displays_validation_feedback(): void
    {
        $this->session->flashInput(['title' => 'Original title', 'description' => 'Original details', 'priority' => 'High']);
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag(['attachment' => 'Too many files.']));
        $html = $this->views->make('tickets.create', ['categories' => collect(), 'torrent' => null, 'errors' => $errors])->render();
        self::assertStringContainsString('Original title', $html);
        self::assertStringContainsString('Original details', $html);
        self::assertStringContainsString('Too many files.', $html);
        self::assertMatchesRegularExpression('/<option\s+selected>High<\/option>/', $html);
    }

    public function test_torrent_report_preselects_category_and_keeps_torrent_context(): void
    {
        $torrent = new \App\Models\Torrent;
        $torrent->forceFill(['id' => 71, 'name' => 'Torrent <unsafe>']);
        $category = new \App\Models\TicketCategory;
        $category->forceFill(['id' => 24, 'name' => 'Torrent Problem']);
        $html = $this->views->make('tickets.create', [
            'categories' => collect([$category]), 'torrent' => $torrent,
            'reportedUser' => null, 'selectedCategoryId' => 24,
        ])->render();

        self::assertMatchesRegularExpression('/<option value="24"\s+selected>Torrent Problem<\/option>/', $html);
        self::assertStringContainsString('Issue with torrent: Torrent &lt;unsafe&gt;', $html);
        self::assertStringContainsString('name="linked_torrent_id" value="71"', $html);
        self::assertStringNotContainsString('name="linked_user_id"', $html);
    }

    public function test_user_report_preselects_category_and_escapes_user_context(): void
    {
        $category = new \App\Models\TicketCategory;
        $category->forceFill(['id' => 25, 'name' => 'User Report']);
        $html = $this->views->make('tickets.create', [
            'categories' => collect([$category]), 'torrent' => null,
            'reportedUser' => $this->user(30), 'selectedCategoryId' => 25,
        ])->render();

        self::assertMatchesRegularExpression('/<option value="25"\s+selected>User Report<\/option>/', $html);
        self::assertStringContainsString('Report user: Member &lt;unsafe&gt;', $html);
        self::assertStringContainsString('name="linked_user_id" value="30"', $html);
        self::assertStringContainsString('Describe the behavior you are reporting', $html);
        self::assertStringNotContainsString('name="linked_torrent_id"', $html);
    }

    public function test_report_restores_chosen_category_after_validation_error(): void
    {
        $this->session->flashInput(['category_id' => 26, 'title' => 'My report', 'description' => 'Details']);
        $categories = collect([25 => 'User Report', 26 => 'Other'])->map(function ($name, $id) {
            $category = new \App\Models\TicketCategory;

            return $category->forceFill(['id' => $id, 'name' => $name]);
        });
        $html = $this->views->make('tickets.create', [
            'categories' => $categories, 'torrent' => null,
            'reportedUser' => $this->user(30), 'selectedCategoryId' => 25,
        ])->render();

        self::assertMatchesRegularExpression('/<option value="26"\s+selected>Other<\/option>/', $html);
        self::assertStringNotContainsString('value="25" selected', $html);
        self::assertStringContainsString('value="My report"', $html);
        self::assertStringContainsString('>Details</textarea>', $html);
        self::assertStringContainsString('name="linked_user_id" value="30"', $html);
    }

    public function test_queue_and_empty_dashboard_render(): void
    {
        $html = $this->views->make('tickets.index', ['tickets' => new LengthAwarePaginator([$this->ticket()], 1, 20), 'categories' => collect(), 'stats' => ['total' => 1, 'active' => 1, 'waiting' => 0, 'resolved' => 0]])->render();
        self::assertStringContainsString('TK00042', $html);
        self::assertStringContainsString('name="keyword"', $html);
        self::assertStringContainsString('Unassigned', $html);
        $this->viewer = $this->user(20, UserClass::ADMIN);
        $html = $this->views->make('tickets.dashboard', ['recentTickets' => collect(), 'stats' => array_fill_keys(['open', 'waiting_staff', 'waiting_user', 'resolved', 'unassigned', 'my_tickets'], 0)])->render();
        self::assertStringContainsString('No tickets found', $html);
        self::assertStringContainsString('Team overview', $html);
    }
}
