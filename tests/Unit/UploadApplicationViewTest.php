<?php

namespace Tests\Unit;

use App\Models\UploadApplication;
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
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Mockery;
use PHPUnit\Framework\TestCase;

class UploadApplicationViewTest extends TestCase
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
        $this->temporary = sys_get_temp_dir().'/uploader-views-'.bin2hex(random_bytes(6));
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
        $request = Request::create('/upload-applications');
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

    private function application(string $status = 'pending'): UploadApplication
    {
        $application = new UploadApplication;
        $application->setDateFormat('Y-m-d H:i:s');
        $application->forceFill(['id' => 42, 'applicant_id' => 10, 'status' => $status, 'created_at' => now(), 'votes_for' => 0, 'votes_against' => 0, 'why_promoted' => '<script>alert(1)</script>', 'internal_speed' => 'javascript:alert(1)', 'external_speed' => 'https://example.test/speed']);
        $application->setRelation('applicant', $this->user(10));
        $application->setRelation('reviewer', null);
        $application->setRelation('votes', collect());

        return $application;
    }

    public function test_applicant_sees_status_and_escaped_answers_without_staff_controls(): void
    {
        $html = $this->views->make('uploadapps.show', ['application' => $this->application(), 'canReview' => false, 'canAct' => false, 'comments' => null])->render();
        self::assertStringContainsString('waiting for review', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('javascript:', $html);
        self::assertStringContainsString('https://example.test/speed', $html);
        self::assertStringNotContainsString('Staff discussion', $html);
        self::assertStringNotContainsString('Vote to approve', $html);
        self::assertStringNotContainsString('Final decision', $html);
    }

    public function test_staff_can_review_an_open_application_and_rejection_requires_feedback(): void
    {
        $this->viewer = $this->user(20, UserClass::ADMIN);
        $html = $this->views->make('uploadapps.show', ['application' => $this->application(), 'canReview' => true, 'canAct' => true, 'comments' => new LengthAwarePaginator([], 0, 20)])->render();
        self::assertStringContainsString('Staff discussion', $html);
        self::assertStringContainsString('3 matching votes decide', $html);
        self::assertStringContainsString('name="reason" rows="3" maxlength="2000" required', $html);
        self::assertStringContainsString('Vote to approve', $html);
    }

    public function test_finalized_application_hides_mutation_forms_and_shows_outcome(): void
    {
        $this->viewer = $this->user(20, UserClass::ADMIN);
        $application = $this->application('accepted');
        $application->decision_at = now();
        $html = $this->views->make('uploadapps.show', ['application' => $application, 'canReview' => true, 'canAct' => false, 'comments' => new LengthAwarePaginator([], 0, 20)])->render();
        self::assertStringContainsString('This application was approved.', $html);
        self::assertStringContainsString('Staff discussion', $html);
        self::assertStringNotContainsString('<form', $html);
    }

    public function test_form_restores_no_answers_after_validation_failure(): void
    {
        $this->session->flashInput(['scene_access' => '0', 'know_torrents' => '0', 'understand_seeding' => '1', 'experience' => 'My original answer']);
        $html = $this->views->make('uploadapps.create')->render();
        self::assertStringContainsString('My original answer', $html);
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        foreach (['scene_access' => '0', 'know_torrents' => '0', 'understand_seeding' => '1'] as $field => $value) {
            self::assertSame($value, $xpath->query('//select[@name="'.$field.'"]/option[@selected]')->item(0)->getAttribute('value'));
        }
    }

    public function test_index_shows_pending_blocker_and_accurate_requirement_units(): void
    {
        $html = $this->views->make('uploadapps.index', ['applications' => new LengthAwarePaginator([$this->application()], 1, 25), 'statusCounts' => collect(['pending' => 1]), 'canReview' => false, 'blocker' => 'You already have an active uploader application.'])->render();
        self::assertStringContainsString('At least 300 GB', $html);
        self::assertStringContainsString('active uploader application', $html);
        self::assertStringNotContainsString('Start application', $html);
        self::assertStringContainsString('Member &lt;unsafe&gt;', $html);
    }

    public function test_empty_staff_queue_renders_without_eligibility_requirements(): void
    {
        $this->viewer = $this->user(20, UserClass::ADMIN);
        $html = $this->views->make('uploadapps.index', ['applications' => new LengthAwarePaginator([], 0, 25), 'statusCounts' => collect(), 'canReview' => true, 'blocker' => 'Already an uploader.'])->render();
        self::assertStringContainsString('Review queue', $html);
        self::assertStringContainsString('No applications', $html);
        self::assertStringNotContainsString('Your current eligibility', $html);
    }
}
