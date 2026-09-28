<?php

namespace Tests\Unit;

use App\Models\UploadApplication;
use App\Models\User;
use App\Models\UserClass;
use App\Services\UploadApplicationService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
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
use Symfony\Component\HttpKernel\Exception\HttpException;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UploadApplicationServiceTest extends TestCase
{
    private UploadApplicationService $service;

    private User $users;

    private UploadApplication $applications;

    private object $messages;

    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        $app->instance('config', new Repository(['tracker' => ['system_user_id' => 99]]));
        $app->instance('validator', new Factory(new Translator(new ArrayLoader, 'en'), $app));
        $url = Mockery::mock();
        $url->shouldReceive('route')->andReturn('https://example.test/upload-applications/42');
        $app->instance('url', $url);
        Carbon::setTestNow('2026-09-25 12:00:00');
        DB::swap(Mockery::mock());
        DB::shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
        DB::shouldReceive('afterCommit')->andReturnUsing(fn ($callback) => $callback());
        $this->users = Mockery::mock(User::class)->makePartial();
        $this->applications = Mockery::mock(UploadApplication::class)->makePartial();
        $this->service = new UploadApplicationService($this->users, $this->applications);
        $this->messages = Mockery::mock('alias:App\\Services\\SystemMessageService');
        $this->messages->shouldReceive('forgetUserCache')->with(99)->byDefault();
        $this->messages->shouldReceive('forgetUserCache')->with(10)->byDefault();
    }

    protected function tearDown(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
        Carbon::setTestNow();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }

    private function user(int $id = 10, int $class = UserClass::USER): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->setDateFormat('Y-m-d H:i:s');
        $user->forceFill(['id' => $id, 'user_class' => $class, 'created_at' => now()->subDays(31), 'uploaded' => User::UPLOADER_MIN_UPLOAD, 'downloaded' => 0]);

        return $user;
    }

    private function application(string $status = 'pending'): UploadApplication
    {
        $application = Mockery::mock(UploadApplication::class)->makePartial();
        $application->setDateFormat('Y-m-d H:i:s');
        $application->forceFill(['id' => 42, 'applicant_id' => 10, 'status' => $status, 'votes_for' => 0, 'votes_against' => 0]);
        $query = Mockery::mock(Builder::class);
        $this->applications->shouldReceive('newQuery')->andReturn($query);
        $query->shouldReceive('whereKey')->with(42)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->atLeast()->once()->andReturnSelf();
        $query->shouldReceive('firstOrFail')->andReturn($application);

        return $application;
    }

    private function lockApplicant(User $applicant): void
    {
        $query = Mockery::mock(Builder::class);
        $this->users->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('whereKey')->once()->with(10)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('firstOrFail')->once()->andReturn($applicant);
    }

    private function expectDecision(UploadApplication $application, string $status, int $class = UserClass::USER): void
    {
        $applicant = $this->user(10, $class);
        $this->lockApplicant($applicant);
        $application->shouldReceive('update')->once()->with(Mockery::on(fn ($data) => $data['status'] === $status && $data['reviewed_by'] === 20 && $data['decision_at']->eq(now())))
            ->andReturnUsing(function ($data) use ($application) {
                $application->forceFill($data);

                return true;
            });
        if ($status === 'accepted' && $class < UserClass::UPLOADER) {
            $applicant->shouldReceive('update')->once()->with(['user_class' => UserClass::UPLOADER])->andReturn(true);
        } else {
            $applicant->shouldNotReceive('update');
        }
    }

    public function test_manual_approval_promotes_and_sends_one_confirmation(): void
    {
        $application = $this->application();
        $this->expectDecision($application, 'accepted');
        $this->messages->shouldReceive('send')->once()->with(99, 10, 'Uploader Application Approved', Mockery::on(fn ($body) => str_contains($body, '#42') && str_contains($body, 'https://example.test/upload-applications/42')));
        self::assertSame('accepted', $this->service->decide($this->user(20, UserClass::ADMIN), 42, 'accepted')->status);
    }

    public function test_rejection_sends_feedback_and_exact_cooldown_without_promotion(): void
    {
        $application = $this->application();
        $this->expectDecision($application, 'rejected');
        $this->messages->shouldReceive('send')->once()->with(99, 10, 'Uploader Application Rejected', Mockery::on(fn ($body) => str_contains($body, '25 October 2026, 12:00') && str_contains($body, '&#91;b&#93;') && ! str_contains($body, '<script>')));
        $this->service->decide($this->user(20, UserClass::ADMIN), 42, 'rejected', 'More experience [b]please[/b] <script>');
    }

    public function test_approval_never_demotes_an_existing_staff_member(): void
    {
        $application = $this->application();
        $this->expectDecision($application, 'accepted', UserClass::MODERATOR);
        $this->messages->shouldReceive('send')->once();
        $this->service->decide($this->user(20, UserClass::ADMIN), 42, 'accepted');
    }

    public function test_a_repeated_decision_does_not_send_a_second_message(): void
    {
        $application = $this->application('accepted');
        $application->shouldNotReceive('update');
        $this->messages->shouldNotReceive('send');
        $this->expectException(ValidationException::class);
        $this->service->decide($this->user(20, UserClass::ADMIN), 42, 'rejected');
    }

    public function test_regular_users_cannot_decide_vote_or_comment(): void
    {
        foreach (['decide' => ['accepted'], 'vote' => ['approve'], 'comment' => ['Test']] as $method => $arguments) {
            try {
                $this->service->$method($this->user(), 42, ...$arguments);
                self::fail('Unauthorized operation allowed: '.$method);
            } catch (HttpException $e) {
                self::assertSame(403, $e->getStatusCode());
            }
        }
    }

    public function test_moderators_cannot_bypass_admin_review_boundary(): void
    {
        $this->expectException(HttpException::class);
        $this->service->vote($this->user(20, UserClass::MODERATOR), 42, 'approve');
    }

    public function test_an_administrator_cannot_review_their_own_application(): void
    {
        $this->application();
        $this->expectException(HttpException::class);
        $this->service->decide($this->user(10, UserClass::ADMIN), 42, 'accepted');
    }

    private function expectVote(UploadApplication $application, int $for, int $against, string $vote): void
    {
        $relation = Mockery::mock(HasMany::class);
        $application->shouldReceive('votes')->andReturn($relation);
        $relation->shouldReceive('updateOrCreate')->once()->with(['user_id' => 20], ['vote' => $vote]);
        $approve = Mockery::mock(HasMany::class);
        $reject = Mockery::mock(HasMany::class);
        $relation->shouldReceive('where')->with('vote', 'approve')->andReturn($approve);
        $relation->shouldReceive('where')->with('vote', 'reject')->andReturn($reject);
        $approve->shouldReceive('count')->once()->andReturn($for);
        $reject->shouldReceive('count')->once()->andReturn($against);
        $application->shouldReceive('update')->once()->with(['votes_for' => $for, 'votes_against' => $against, 'status' => 'voting'])
            ->andReturnUsing(function ($data) use ($application) {
                $application->forceFill($data);

                return true;
            });
    }

    public function test_third_approval_vote_uses_the_same_promotion_and_messaging_path(): void
    {
        $application = $this->application('voting');
        $this->expectVote($application, 3, 1, 'approve');
        $this->expectDecision($application, 'accepted');
        $this->messages->shouldReceive('send')->once()->with(99, 10, 'Uploader Application Approved', Mockery::type('string'));
        self::assertSame('accepted', $this->service->vote($this->user(20, UserClass::ADMIN), 42, 'approve')->status);
    }

    public function test_third_rejection_vote_sends_one_rejection_and_no_promotion(): void
    {
        $application = $this->application('voting');
        $this->expectVote($application, 1, 3, 'reject');
        $this->expectDecision($application, 'rejected');
        $this->messages->shouldReceive('send')->once()->with(99, 10, 'Uploader Application Rejected', Mockery::type('string'));
        self::assertSame('rejected', $this->service->vote($this->user(20, UserClass::ADMIN), 42, 'reject')->status);
    }

    public function test_changing_vote_recounts_totals_without_premature_confirmation(): void
    {
        $application = $this->application('voting');
        $this->expectVote($application, 1, 2, 'reject');
        $this->messages->shouldNotReceive('send');
        $result = $this->service->vote($this->user(20, UserClass::ADMIN), 42, 'reject');
        self::assertSame(1, $result->votes_for);
        self::assertSame(2, $result->votes_against);
        self::assertTrue($result->isActive());
    }

    public function test_closed_application_cannot_receive_votes_or_comments(): void
    {
        $application = $this->application('rejected');
        $application->shouldNotReceive('votes', 'comments', 'update');
        foreach (['vote' => 'approve', 'comment' => 'Test'] as $method => $value) {
            try {
                $this->service->$method($this->user(20, UserClass::ADMIN), 42, $value);
                self::fail('Closed application was modified.');
            } catch (ValidationException $e) {
                self::assertArrayHasKey('application', $e->errors());
            }
        }
    }

    public function test_first_comment_moves_pending_application_into_discussion(): void
    {
        $application = $this->application();
        $relation = Mockery::mock(HasMany::class);
        $application->shouldReceive('comments')->once()->andReturn($relation);
        $relation->shouldReceive('create')->once()->with(['user_id' => 20, 'comment' => 'Good plan.']);
        $application->shouldReceive('update')->once()->with(['status' => 'discussion']);
        $this->service->comment($this->user(20, UserClass::ADMIN), 42, 'Good plan.');
    }

    public function test_direct_submission_rechecks_eligibility_after_locking_member(): void
    {
        $applicant = $this->user();
        $applicant->uploaded = 300;
        $this->lockApplicant($applicant);
        $this->expectException(ValidationException::class);
        $this->service->submit($applicant, []);
    }

    public function test_direct_submission_rejects_duplicate_active_application(): void
    {
        $applicant = $this->user();
        $this->lockApplicant($applicant);
        $relation = Mockery::mock(HasMany::class);
        $applicant->shouldReceive('uploadApplications')->once()->andReturn($relation);
        $relation->shouldReceive('whereIn')->with('status', UploadApplication::ACTIVE_STATUSES)->andReturnSelf();
        $relation->shouldReceive('exists')->once()->andReturn(true);
        $this->expectException(ValidationException::class);
        $this->service->submit($applicant, []);
    }

    public function test_cooldown_blocks_submission_even_with_valid_requirements(): void
    {
        $applicant = $this->user();
        $this->lockApplicant($applicant);
        $relation = Mockery::mock(HasMany::class);
        $applicant->shouldReceive('uploadApplications')->once()->andReturn($relation);
        $relation->shouldReceive('whereIn')->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn(false);
        $applicant->shouldReceive('uploaderApplicationCooldown')->once()->andReturn(['blocked' => true, 'days_remaining' => 1]);
        $this->expectException(ValidationException::class);
        $this->service->submit($applicant, []);
    }

    public function test_upload_and_ratio_requirements_handle_bytes_and_zero_downloads(): void
    {
        $user = $this->user();
        self::assertTrue($user->canApplyForUploader()['allowed']);
        $user->uploaded = 300;
        self::assertFalse($user->canApplyForUploader()['allowed']);
        $user->uploaded = User::UPLOADER_MIN_UPLOAD;
        $user->downloaded = $user->uploaded;
        self::assertFalse($user->canApplyForUploader()['allowed']);
        $user->downloaded = 0;
        $user->created_at = now()->subDays(29);
        self::assertFalse($user->canApplyForUploader()['allowed']);
        $user->created_at = now()->subDays(30);
        self::assertTrue($user->canApplyForUploader()['allowed']);
        $user->user_class = UserClass::UPLOADER;
        self::assertFalse($user->canApplyForUploader()['allowed']);
    }

    public function test_message_failure_propagates_out_of_decision_transaction(): void
    {
        $application = $this->application();
        $this->expectDecision($application, 'accepted');
        $this->messages->shouldReceive('send')->once()->andThrow(new \RuntimeException('Message failed'));
        DB::shouldReceive('afterCommit')->never();
        $this->expectExceptionMessage('Message failed');
        $this->service->decide($this->user(20, UserClass::ADMIN), 42, 'accepted');
    }

    public function test_successful_submission_notifies_only_reviewers_and_forces_pending_status(): void
    {
        $applicant = $this->user();
        $locked = Mockery::mock(Builder::class);
        $staff = Mockery::mock(Builder::class);
        $this->users->shouldReceive('newQuery')->twice()->andReturn($locked, $staff);
        $locked->shouldReceive('whereKey')->once()->with(10)->andReturnSelf();
        $locked->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $locked->shouldReceive('firstOrFail')->once()->andReturn($applicant);
        $staff->shouldReceive('where')->once()->with('user_class', '>=', UserClass::ADMIN)->andReturnSelf();
        $staff->shouldReceive('pluck')->once()->with('id')->andReturn(collect([20, 21]));
        $relation = Mockery::mock(HasMany::class);
        $applicant->shouldReceive('uploadApplications')->twice()->andReturn($relation);
        $relation->shouldReceive('whereIn')->with('status', UploadApplication::ACTIVE_STATUSES)->andReturnSelf();
        $relation->shouldReceive('exists')->once()->andReturn(false);
        $applicant->shouldReceive('uploaderApplicationCooldown')->once()->andReturn(['blocked' => false, 'days_remaining' => 0]);
        $application = new UploadApplication;
        $application->id = 42;
        $relation->shouldReceive('create')->once()->with(['why_promoted' => 'My plan', 'status' => 'pending'])->andReturn($application);
        foreach ([20, 21] as $id) {
            $this->messages->shouldReceive('send')->once()->with(99, $id, 'New Uploader Application', Mockery::on(fn ($body) => str_contains($body, '/upload-applications/42')));
        }
        self::assertSame($application, $this->service->submit($applicant, ['why_promoted' => 'My plan', 'status' => 'accepted']));
    }

    public function test_conflicting_historical_thresholds_require_a_manual_decision(): void
    {
        $application = $this->application('voting');
        $this->expectVote($application, 3, 3, 'approve');
        $this->messages->shouldNotReceive('send');
        $this->expectException(ValidationException::class);
        $this->service->vote($this->user(20, UserClass::ADMIN), 42, 'approve');
    }

    public function test_unknown_decision_and_vote_values_are_rejected_before_mutations(): void
    {
        foreach (['decide', 'vote'] as $method) {
            try {
                $this->service->$method($this->user(20, UserClass::ADMIN), 42, 'invalid');
                self::fail('Unknown decision was accepted.');
            } catch (ValidationException $e) {
                self::assertNotEmpty($e->errors());
            }
        }
    }
}
