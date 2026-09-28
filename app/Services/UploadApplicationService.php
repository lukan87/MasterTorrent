<?php

namespace App\Services;

use App\Models\UploadApplication;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UploadApplicationService
{
    public const REQUIRED_VOTES = 3;

    public function __construct(private User $users, private UploadApplication $applications) {}

    public function canReview(User $user): bool
    {
        return $user->user_class >= UserClass::ADMIN;
    }

    public function authorizeReviewer(User $user, ?UploadApplication $application = null): void
    {
        abort_unless($this->canReview($user), 403);
        if ($application) {
            abort_if((int) $application->applicant_id === (int) $user->id, 403, 'You cannot review your own application.');
        }
    }

    public function applicationBlocker(User $user): ?string
    {
        $eligibility = $user->canApplyForUploader();
        if (! $eligibility['allowed']) {
            return $eligibility['reason'];
        }

        if ($user->uploadApplications()->whereIn('status', UploadApplication::ACTIVE_STATUSES)->exists()) {
            return 'You already have an active uploader application.';
        }

        $cooldown = $user->uploaderApplicationCooldown();
        if ($cooldown['blocked']) {
            return "You may apply again in {$cooldown['days_remaining']} day(s).";
        }

        return null;
    }

    public function submit(User $applicant, array $answers): UploadApplication
    {
        return DB::transaction(function () use ($applicant, $answers) {
            // Serializes submissions for a member, including two browser tabs.
            $user = $this->users->newQuery()->whereKey($applicant->id)->lockForUpdate()->firstOrFail();
            if ($reason = $this->applicationBlocker($user)) {
                throw ValidationException::withMessages(['application' => $reason]);
            }

            $application = $user->uploadApplications()->create(array_merge($answers, ['status' => 'pending']));
            // Only notify members who can actually open and review the application.
            foreach ($this->users->newQuery()->where('user_class', '>=', UserClass::ADMIN)->pluck('id') as $staffId) {
                SystemMessageService::send(
                    (int) config('tracker.system_user_id', 2),
                    $staffId,
                    'New Uploader Application',
                    "Uploader application #{$application->id} is ready for review.\n\n".route('uploadapps.show', $application->id)
                );
            }

            return $application;
        });
    }

    public function comment(User $reviewer, int $id, string $comment): void
    {
        $this->authorizeReviewer($reviewer);
        DB::transaction(function () use ($reviewer, $id, $comment) {
            $application = $this->applications->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorizeReviewer($reviewer, $application);
            $this->ensureActive($application);
            $application->comments()->create(['user_id' => $reviewer->id, 'comment' => $comment]);
            if ($application->status === 'pending') {
                $application->update(['status' => 'discussion']);
            }
        });
    }

    public function vote(User $reviewer, int $id, string $vote): UploadApplication
    {
        $this->authorizeReviewer($reviewer);
        if (! in_array($vote, ['approve', 'reject'], true)) {
            throw ValidationException::withMessages(['vote' => 'Choose approve or reject.']);
        }

        return DB::transaction(function () use ($reviewer, $id, $vote) {
            $application = $this->applications->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorizeReviewer($reviewer, $application);
            $this->ensureActive($application);
            $application->votes()->updateOrCreate(['user_id' => $reviewer->id], ['vote' => $vote]);
            $for = $application->votes()->where('vote', 'approve')->count();
            $against = $application->votes()->where('vote', 'reject')->count();
            $application->update(['votes_for' => $for, 'votes_against' => $against, 'status' => 'voting']);

            if ($for >= self::REQUIRED_VOTES || $against >= self::REQUIRED_VOTES) {
                // Do not guess an outcome for inconsistent historical vote totals.
                if ($for >= self::REQUIRED_VOTES && $against >= self::REQUIRED_VOTES) {
                    throw ValidationException::withMessages(['vote' => 'Both thresholds have been reached. An administrator must make a final decision.']);
                }
                $this->finalize($application, $reviewer, $for >= self::REQUIRED_VOTES ? 'accepted' : 'rejected');
            }

            return $application;
        });
    }

    public function decide(User $reviewer, int $id, string $status, ?string $reason = null): UploadApplication
    {
        $this->authorizeReviewer($reviewer);
        if (! in_array($status, ['accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Choose a final decision.']);
        }

        return DB::transaction(function () use ($reviewer, $id, $status, $reason) {
            $application = $this->applications->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->authorizeReviewer($reviewer, $application);
            $this->ensureActive($application);
            $this->finalize($application, $reviewer, $status, $reason);

            return $application;
        });
    }

    private function ensureActive(UploadApplication $application): void
    {
        if (! $application->isActive()) {
            throw ValidationException::withMessages(['application' => 'This application has already been decided.']);
        }
    }

    private function finalize(UploadApplication $application, User $reviewer, string $status, ?string $reason = null): void
    {
        // Called only with the application row locked, inside the same transaction
        // as its votes, promotion and private message. A failed message rolls it back.
        $applicant = $this->users->newQuery()->whereKey($application->applicant_id)->lockForUpdate()->firstOrFail();
        $application->update(['status' => $status, 'reviewed_by' => $reviewer->id, 'decision_at' => now()]);

        if ($status === 'accepted' && $applicant->user_class < UserClass::UPLOADER) {
            $applicant->update(['user_class' => UserClass::UPLOADER]);
        }

        $body = $status === 'accepted'
            ? "Congratulations! Your uploader application #{$application->id} has been approved.\n\nUploader access is now enabled on your account. Please follow the upload rules and keep your uploads seeded."
            : "Your uploader application #{$application->id} has been rejected.\n\nYou may apply again from ".now()->addDays(User::UPLOADER_COOLDOWN_DAYS)->utc()->format('d F Y, H:i').' UTC, provided you meet the uploader requirements.';

        if ($reason !== null && trim($reason) !== '') {
            // Private messages support BBCode; keep reviewer feedback as plain text.
            $plainReason = str_replace(['[', ']'], ['&#91;', '&#93;'], e(trim($reason)));
            $body .= "\n\nReviewer feedback:\n".$plainReason;
        }
        $body .= "\n\nView your application:\n".route('uploadapps.show', $application->id);

        $senderId = (int) config('tracker.system_user_id', 2);
        SystemMessageService::send($senderId, $applicant->id,
            $status === 'accepted' ? 'Uploader Application Approved' : 'Uploader Application Rejected', $body);
        // Clear again after commit so an inbox read during the transaction cannot
        // leave the newly committed confirmation hidden behind a stale cache.
        DB::afterCommit(function () use ($senderId, $applicant) {
            SystemMessageService::forgetUserCache($senderId);
            SystemMessageService::forgetUserCache($applicant->id);
        });
    }
}
