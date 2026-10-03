<?php

namespace App\Console\Commands;

use App\Models\EmailCampaignRecipient;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class ProcessEmailBounces extends Command
{
    protected $signature = 'email:process-bounces
                            {--since=30m : How far back to inspect Mailcow Postfix logs}
                            {--delete : Soft-delete users with confirmed permanent invalid-address bounces}';

    protected $description = 'Process confirmed permanent email bounces from Mailcow/Postfix';

/**
 * DSN codes that are safe for automatic account deletion.
 *
 * 5.1.1 = Bad destination mailbox address / recipient does not exist.
 *
 * Other permanent 5.1.x responses are deliberately excluded because
 * they can represent addressing, routing or configuration problems
 * rather than a confirmed nonexistent mailbox.
 */
private const HARD_BOUNCE_CODES = [
    '5.1.1',
];

    public function handle(): int
    {
        $since = trim((string) $this->option('since'));

        if (!preg_match('/^\d+[smhd]$/', $since)) {
            $this->error(
                'Invalid --since value. Examples: 30m, 2h, 1d.'
            );

            return self::FAILURE;
        }

        $deleteUsers = (bool) $this->option('delete');

        $this->info(
            "Reading Mailcow Postfix logs from the last {$since}..."
        );

        if (!$deleteUsers) {
            $this->warn(
                'DRY RUN: users will NOT be deleted and bounce events will NOT be recorded.'
            );
        }

        $lines = $this->readPostfixLogs($since);

        if ($lines === null) {
            return self::FAILURE;
        }

        $bounces = $this->extractHardBounces($lines);

        if (empty($bounces)) {
            $this->info(
                'No confirmed permanent invalid-address bounces found.'
            );

            return self::SUCCESS;
        }

        $this->info(
            'Found '
            . count($bounces)
            . ' hard-bounce event(s) in the selected log window.'
        );

        $processed = 0;
        $deleted = 0;
        $alreadyProcessed = 0;
        $notFound = 0;
        $errors = 0;

        foreach ($bounces as $bounce) {
            $email = $bounce['email'];
            $dsn = $bounce['dsn'];
            $message = $bounce['message'];
            $queueId = $bounce['queue_id'];
            $fingerprint = $bounce['fingerprint'];

            $this->newLine();

            $this->line("Email: {$email}");
            $this->line("DSN: {$dsn}");

            if ($queueId) {
                $this->line("Postfix queue ID: {$queueId}");
            }

            $this->line("Reason: {$message}");

            /*
             * Has this exact Postfix bounce already been processed?
             *
             * We only consult this during a real processing run.
             * Dry-run mode intentionally remains read-only.
             */
            if (
                $deleteUsers &&
                DB::table('email_bounce_events')
                    ->where('fingerprint', $fingerprint)
                    ->exists()
            ) {
                $this->line(
                    'Already processed - skipping this bounce event.'
                );

                $alreadyProcessed++;

                continue;
            }

            $user = User::withTrashed()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [mb_strtolower($email)]
                )
                ->first();

            $campaignRecipient = EmailCampaignRecipient::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [mb_strtolower($email)]
                )
                ->latest('id')
                ->first();

            if ($campaignRecipient) {
                $this->line(
                    "Campaign recipient: #{$campaignRecipient->id}"
                );
            } else {
                $this->line('Campaign recipient: not found');
            }

            if ($user) {
                $this->line(
                    "User: #{$user->id} {$user->name}"
                );

                if ($user->trashed()) {
                    $this->line(
                        'User is already soft-deleted.'
                    );
                }
            } else {
                $this->line('User: not found');
            }

            if (!$deleteUsers) {
                if (!$user && !$campaignRecipient) {
                    $this->warn(
                        'No FileIplay user or campaign recipient matched this address.'
                    );

                    $notFound++;
                } else {
                    $this->warn(
                        'DRY RUN: confirmed hard bounce; no database changes made.'
                    );

                    $processed++;
                }

                continue;
            }

            try {
                $result = DB::transaction(function () use (
                    $email,
                    $dsn,
                    $message,
                    $queueId,
                    $fingerprint
                ) {
                    /*
                     * Check again inside the transaction.
                     *
                     * The fingerprint column also has a UNIQUE constraint,
                     * providing a second layer of protection.
                     */
                    if (
                        DB::table('email_bounce_events')
                            ->where('fingerprint', $fingerprint)
                            ->exists()
                    ) {
                        return [
                            'already_processed' => true,
                            'deleted' => false,
                            'matched' => false,
                        ];
                    }

                    $recipient = EmailCampaignRecipient::query()
                        ->whereRaw(
                            'LOWER(email) = ?',
                            [mb_strtolower($email)]
                        )
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();

                    $user = User::withTrashed()
                        ->whereRaw(
                            'LOWER(email) = ?',
                            [mb_strtolower($email)]
                        )
                        ->lockForUpdate()
                        ->first();

                    $matched = (bool) ($recipient || $user);
                    $userDeleted = false;

                    /*
                     * Preserve delivery failure information against
                     * the campaign-recipient snapshot.
                     */
                    if ($recipient) {
                        $recipient->bounce_type = 'hard';
                        $recipient->bounce_code = $dsn;
                        $recipient->bounce_message = mb_substr(
                            $message,
                            0,
                            65535
                        );
                        $recipient->bounced_at = now();
                        $recipient->save();
                    }

                    /*
                     * Only delete an ACTIVE user whose CURRENT email
                     * is still the address that bounced.
                     *
                     * Because the lookup itself uses the bounced email,
                     * a user who has since changed their email will not
                     * be matched or deleted.
                     */
                    if ($user && !$user->trashed()) {
    $user->email_bounced = true;
    $user->email_bounce_type = 'hard';
    $user->save();

    /*
     * Never automatically delete a Web Developer account.
     * user_class 8 = Web Developer.
     *
     * The bounce is still recorded so the email address
     * can be investigated manually.
     */
    if ((int) $user->user_class === 9) {
        $this->warn(
            "Protected Web Developer account #{$user->id} {$user->name} was NOT deleted."
        );
    } else {
        $user->delete();
        $userDeleted = true;
    }
}

                    /*
                     * Record the event even if there is no matching
                     * FileIplay user. This prevents the same Postfix
                     * bounce from being reconsidered on the next
                     * overlapping scheduler run.
                     */
                    DB::table('email_bounce_events')->insert([
                        'fingerprint' => $fingerprint,
                        'queue_id' => $queueId,
                        'email' => $email,
                        'dsn' => $dsn,
                        'bounce_type' => 'hard',
                        'message' => mb_substr(
                            $message,
                            0,
                            65535
                        ),
                        'user_id' => $user?->id,
                        'email_campaign_recipient_id' => $recipient?->id,
                        'bounced_at' => now(),
                        'processed_at' => now(),
                        'user_deleted' => $userDeleted,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return [
                        'already_processed' => false,
                        'deleted' => $userDeleted,
                        'matched' => $matched,
                    ];
                });

                if ($result['already_processed']) {
                    $alreadyProcessed++;

                    $this->line(
                        'Already processed - skipping this bounce event.'
                    );

                    continue;
                }

                $processed++;

                if ($result['deleted']) {
                    $deleted++;

                    $this->info(
                        'Hard bounce recorded and matching user soft-deleted.'
                    );
                } elseif ($result['matched']) {
                    $this->info(
                        'Hard bounce recorded. No active matching user required deletion.'
                    );
                } else {
                    $notFound++;

                    $this->warn(
                        'Hard bounce recorded, but no FileIplay user or campaign recipient matched the address.'
                    );
                }
            } catch (Throwable $exception) {
                report($exception);

                Log::error(
                    'Failed processing email hard bounce.',
                    [
                        'email' => $email,
                        'dsn' => $dsn,
                        'queue_id' => $queueId,
                        'fingerprint' => $fingerprint,
                        'error' => $exception->getMessage(),
                    ]
                );

                $errors++;

                $this->error(
                    'Database update failed: '
                    . $exception->getMessage()
                );
            }
        }

        $this->newLine();

        $this->info('Bounce processing finished.');

        $this->table(
            ['Result', 'Count'],
            [
                ['Hard-bounce events detected', count($bounces)],
                ['Processed', $processed],
                ['Already processed', $alreadyProcessed],
                ['Users soft-deleted', $deleted],
                ['No matching FileIplay record', $notFound],
                ['Errors', $errors],
            ]
        );

        return $errors > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * Read Postfix logs from the Mailcow container.
     */
    /**
 * Read Postfix logs from Mailcow through the restricted
 * root-owned wrapper script.
 */
private function readPostfixLogs(string $since): ?array
{
    $process = new Process([
        'sudo',
        '-n',
        '/usr/local/sbin/fileiplay-mailcow-postfix-logs',
        $since,
    ]);

    $process->setTimeout(60);

    try {
        $process->run();
    } catch (Throwable $exception) {
        $this->error(
            'Could not execute Mailcow Postfix log reader: '
            . $exception->getMessage()
        );

        return null;
    }

    if (!$process->isSuccessful()) {
        $this->error(
            'Could not read Mailcow Postfix logs.'
        );

        $error = trim(
            $process->getErrorOutput()
        );

        if ($error !== '') {
            $this->line($error);
        }

        $this->newLine();

        $this->warn(
            'The Laravel scheduler user could not execute the restricted Mailcow log reader.'
        );

        return null;
    }

    return preg_split(
        '/\R/',
        $process->getOutput(),
        -1,
        PREG_SPLIT_NO_EMPTY
    ) ?: [];
}

    /**
     * Extract confirmed permanent recipient-address failures.
     *
     * Unlike the previous version, events are keyed by fingerprint,
     * not merely by email address. This means two genuine bounces for
     * the same address remain two distinct Postfix events.
     */
    private function extractHardBounces(array $lines): array
    {
        $bounces = [];

        foreach ($lines as $line) {
            if (!str_contains($line, 'status=bounced')) {
                continue;
            }

            if (
                !preg_match(
                    '/\bdsn=(5\.1\.[0-9]+)\b/i',
                    $line,
                    $dsnMatch
                )
            ) {
                continue;
            }

            if (
                !preg_match(
                    '/\bto=<([^>]+)>/i',
                    $line,
                    $emailMatch
                )
            ) {
                continue;
            }

            $dsn = $dsnMatch[1];

            if (
                !in_array(
                    $dsn,
                    self::HARD_BOUNCE_CODES,
                    true
                )
            ) {
                continue;
            }

            $email = mb_strtolower(
                trim($emailMatch[1])
            );

            if (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                continue;
            }

            $queueId = $this->extractQueueId($line);

            $message = $this->extractDiagnosticMessage(
                $line
            );

            /*
             * Hash the complete normalized Postfix log line.
             *
             * The exact same event appearing in overlapping log
             * windows therefore produces the exact same fingerprint.
             */
            $normalizedLine = trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $line
                )
            );

            $fingerprint = hash(
                'sha256',
                $normalizedLine
            );

            $bounces[$fingerprint] = [
                'fingerprint' => $fingerprint,
                'queue_id' => $queueId,
                'email' => $email,
                'dsn' => $dsn,
                'message' => $message,
            ];
        }

        return array_values($bounces);
    }

    /**
     * Extract the Postfix queue ID.
     *
     * Example:
     * postfix/smtp[123]: 3925741229A: to=<user@example.com>...
     */
    private function extractQueueId(string $line): ?string
    {
        if (
            preg_match(
                '/postfix\/smtp\[[^\]]+\]:\s+([A-F0-9]+):\s+/i',
                $line,
                $match
            )
        ) {
            return strtoupper(
                $match[1]
            );
        }

        return null;
    }

    /**
     * Extract the remote SMTP diagnostic.
     */
    private function extractDiagnosticMessage(string $line): string
    {
        if (
            preg_match(
                '/status=bounced\s+\((.*)\)\s*$/i',
                $line,
                $match
            )
        ) {
            return trim(
                $match[1]
            );
        }

        return mb_substr(
            trim($line),
            0,
            65535
        );
    }
}