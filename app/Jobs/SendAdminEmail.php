<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAdminEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Only ONE delivery attempt.
     *
     * If sending throws an exception, Laravel will call failed()
     * immediately. The email address is then permanently suppressed
     * by setting users.email_bounced = true.
     */
    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        public int $campaignRecipientId
    ) {
        $this->onQueue('emails');
    }

    /**
     * Process the email.
     */
    public function handle(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Move recipient from queued -> processing
        |--------------------------------------------------------------------------
        */

        $recipient = DB::transaction(function () {
            $recipient = EmailCampaignRecipient::query()
                ->whereKey($this->campaignRecipientId)
                ->lockForUpdate()
                ->first();

            if (!$recipient) {
                return null;
            }

            /*
             * Never send an already completed recipient again.
             */
            if (in_array($recipient->status, ['sent', 'failed'], true)) {
                return null;
            }

            /*
             * Extra safety:
             *
             * Even if this job somehow exists in Redis for a user whose
             * address has subsequently been marked as bounced, do not send it.
             */
            if ($recipient->user_id) {
                $user = User::query()
                    ->whereKey($recipient->user_id)
                    ->first();

                if ($user && (bool) $user->email_bounced) {
                    return null;
                }
            }

            $campaign = EmailCampaign::query()
                ->whereKey($recipient->email_campaign_id)
                ->lockForUpdate()
                ->first();

            if (!$campaign) {
                return null;
            }

            /*
             * Only decrement queued_count when leaving queued state.
             */
            if ($recipient->status === 'queued') {
                if ($campaign->queued_count > 0) {
                    $campaign->decrement('queued_count');
                }
            }

            $recipient->update([
                'status'        => 'processing',
                'attempts'      => $this->attempts(),
                'processing_at' => now(),
                'error'         => null,
            ]);

            if ($campaign->status === 'queued') {
                $campaign->update([
                    'status'     => 'sending',
                    'started_at' => $campaign->started_at ?? now(),
                ]);
            }

            return $recipient->fresh([
                'campaign',
            ]);
        });

        if (!$recipient) {
            return;
        }

        $campaign = $recipient->campaign;

        if (!$campaign) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Personalise message
        |--------------------------------------------------------------------------
        */

        $subject = trim(
            $this->parse(
                $campaign->subject,
                $recipient
            )
        );

        $body = $this->parse(
            $campaign->body,
            $recipient
        );

        /*
        |--------------------------------------------------------------------------
        | Send through Mailcow
        |--------------------------------------------------------------------------
        |
        | Any exception thrown here causes this job to fail.
        |
        | Because $tries = 1, Laravel will NOT automatically retry it.
        | failed() will then permanently suppress the user's email.
        |
        */

        Mail::raw(
            $body,
            function ($message) use ($recipient, $subject) {
                $message
                    ->to($recipient->email)
                    ->subject($subject);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Mark successful
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $recipient,
            $subject,
            $body
        ) {
            $lockedRecipient = EmailCampaignRecipient::query()
                ->whereKey($recipient->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedRecipient) {
                return;
            }

            /*
             * Protect counters against duplicate execution.
             */
            if ($lockedRecipient->status === 'sent') {
                return;
            }

            $lockedRecipient->update([
                'status'    => 'sent',
                'sent_at'   => now(),
                'failed_at' => null,
                'error'     => null,
            ]);

            /*
             * Preserve email history.
             */
            EmailLog::create([
                'user_id' => $lockedRecipient->user_id,
                'subject' => $subject,
                'body'    => $body,
                'sent_at' => now(),
            ]);

            EmailCampaign::whereKey(
                $lockedRecipient->email_campaign_id
            )->increment('sent_count');

            $this->refreshCampaignStatus(
                $lockedRecipient->email_campaign_id
            );
        });

        /*
         * Keep the existing gentle sending speed.
         *
         * With one dedicated "emails" worker this gives approximately
         * 30 successfully processed messages per minute.
         */
        sleep(2);
    }

    /**
     * Called after the single sending attempt fails.
     *
     * FileIPlay policy:
     *
     * ONE sending error =
     * users.email_bounced = true =
     * never send campaign email to that address again.
     */
    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }

        DB::transaction(function () use ($exception) {
            $recipient = EmailCampaignRecipient::query()
                ->whereKey($this->campaignRecipientId)
                ->lockForUpdate()
                ->first();

            if (!$recipient) {
                return;
            }

            /*
             * Never overwrite a successful delivery.
             */
            if ($recipient->status === 'sent') {
                return;
            }

            /*
             * Never count the same failure twice.
             */
            if ($recipient->status === 'failed') {
                return;
            }

            $campaign = EmailCampaign::query()
                ->whereKey($recipient->email_campaign_id)
                ->lockForUpdate()
                ->first();

            if (!$campaign) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Permanently suppress the user's email
            |--------------------------------------------------------------------------
            |
            | Any delivery exception permanently marks the email as bounced.
            | Future campaign recipient queries must exclude email_bounced=true.
            |
            */

            if ($recipient->user_id) {
                User::query()
                    ->whereKey($recipient->user_id)
                    ->update([
                        'email_bounced' => true,
                    ]);
            }

            /*
             * Usually the recipient has already moved from queued to
             * processing. This also handles a failure that happened before
             * that transition completed.
             */
            if (
                $recipient->status === 'queued' &&
                $campaign->queued_count > 0
            ) {
                $campaign->decrement('queued_count');
            }

            $recipient->update([
                'status'     => 'failed',
                'attempts'   => $this->attempts(),
                'failed_at'  => now(),
                'error'      => $exception
                    ? mb_substr(
                        $exception->getMessage(),
                        0,
                        65535
                    )
                    : 'Email delivery failed.',
            ]);

            $campaign->increment('failed_count');

            $this->refreshCampaignStatus(
                $campaign->id
            );
        });
    }

    /**
     * Update the overall campaign status.
     */
    private function refreshCampaignStatus(int $campaignId): void
    {
        $campaign = EmailCampaign::query()
            ->whereKey($campaignId)
            ->lockForUpdate()
            ->first();

        if (!$campaign) {
            return;
        }

        $processed =
            $campaign->sent_count +
            $campaign->failed_count;

        /*
         * Some recipients still haven't finished.
         */
        if ($processed < $campaign->total_recipients) {
            $campaign->status = 'sending';
            $campaign->started_at ??= now();
            $campaign->save();

            return;
        }

        /*
         * Every recipient has reached a final state.
         */
        $campaign->status = $campaign->failed_count > 0
            ? 'completed_with_failures'
            : 'completed';

        $campaign->queued_count = 0;
        $campaign->completed_at = now();

        $campaign->save();
    }

    /**
     * Replace supported template variables using the saved
     * campaign recipient snapshot.
     */
    private function parse(
        string $text,
        EmailCampaignRecipient $recipient
    ): string {
        return str_replace(
            [
                '{name}',
                '{ name }',
                '{Name}',
                '{NAME}',
                '{email}',
                '{ email }',
            ],
            [
                $recipient->name ?? '',
                $recipient->name ?? '',
                $recipient->name ?? '',
                $recipient->name ?? '',
                $recipient->email,
                $recipient->email,
            ],
            $text
        );
    }
}