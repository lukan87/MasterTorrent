<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\Admin\SystemInfoController;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;

class AdminWorkflowTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        restore_error_handler();
        restore_exception_handler();
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_series_directory_renders_populated_and_empty_paginators(): void
    {
        // Render the real page body without the site layout's database queries.
        $template = file_get_contents(resource_path('views/admin/series/index.blade.php'));
        $template = str_replace([
            "@extends('layouts.admin')", "@section('admin-content')", '@endsection',
        ], '', $template);

        $record = new \App\Models\Series();
        $record->id = 123;
        $record->name = 'Pagination regression series';
        $series = new \Illuminate\Pagination\LengthAwarePaginator([$record], 51, 50, 1, [
            'path' => '/admin/series',
        ]);
        $html = \Illuminate\Support\Facades\Blade::render($template, compact('series'), deleteCachedView: true);
        self::assertStringContainsString('Pagination regression series', $html);
        self::assertStringContainsString('page=2', $html);

        $series = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        $html = \Illuminate\Support\Facades\Blade::render($template, compact('series'), deleteCachedView: true);
        self::assertStringContainsString('No results found.', $html);
    }

    public function test_all_registered_admin_actions_exist_and_require_staff_authentication(): void
    {
        $count = 0;
        foreach ($this->app['router']->getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_starts_with($action, 'App\\Http\\Controllers\\Admin\\')) {
                continue;
            }
            [$class, $method] = explode('@', $action);
            self::assertTrue(method_exists($class, $method), $action);
            self::assertContains('auth', $route->gatherMiddleware(), $route->uri());
            self::assertContains('admin', $route->gatherMiddleware(), $route->uri());
            $count++;
        }
        self::assertGreaterThan(40, $count);
    }

    public function test_system_routes_require_the_developer_gate(): void
    {
        foreach ($this->app['router']->getRoutes() as $route) {
            if (str_starts_with($route->getActionName(), SystemInfoController::class.'@')) {
                self::assertContains('can:manage-admin-system', $route->gatherMiddleware());
            }
        }
        $staff = new User;
        $staff->user_class = UserClass::MODERATOR;
        self::assertFalse(Gate::forUser($staff)->allows('manage-admin-system'));
        $staff->user_class = UserClass::WEB_DEVELOPER;
        self::assertTrue(Gate::forUser($staff)->allows('manage-admin-system'));
    }

    public function test_unknown_bulk_action_is_rejected_before_querying_messages(): void
    {
        $this->expectException(ValidationException::class);
        (new MessagesController)->bulk(Request::create('/', 'POST', ['action' => 'archive', 'ids' => []]));
    }

    public function test_empty_bulk_selection_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new MessagesController)->bulk(Request::create('/', 'POST', ['action' => 'delete', 'ids' => []]));
    }

    public function test_email_preview_rejects_unbounded_recipient_target(): void
    {
        $this->expectException(ValidationException::class);
        (new EmailController)->count(Request::create('/', 'POST', ['target' => 'everyone']));
    }

    public function test_email_preview_rejects_scalar_class_filter(): void
    {
        $this->expectException(ValidationException::class);
        (new EmailController)->count(Request::create('/', 'POST', ['target' => 'subscribed', 'user_class' => '1']));
    }

    public function test_failed_system_command_reports_failure(): void
    {
        Artisan::shouldReceive('call')->once()->with('view:clear')->andReturn(1);
        Log::shouldReceive('error')->once();
        $response = new RedirectResponse('/admin/system-info');
        $session = new Store('test', new ArraySessionHandler(120));
        $response->setSession($session);
        Redirect::shouldReceive('back')->once()->andReturn($response);
        (new SystemInfoController)->clearViews();
        self::assertTrue($session->has('error'));
        self::assertFalse($session->has('success'));
    }

    public function test_successful_system_command_reports_success(): void
    {
        Artisan::shouldReceive('call')->once()->with('view:clear')->andReturn(0);
        $response = new RedirectResponse('/admin/system-info');
        $session = new Store('test', new ArraySessionHandler(120));
        $response->setSession($session);
        Redirect::shouldReceive('route')->once()->with('admin.systemInfo.index')->andReturn($response);
        (new SystemInfoController)->clearViews();
        self::assertTrue($session->has('success'));
        self::assertFalse($session->has('error'));
    }
}
