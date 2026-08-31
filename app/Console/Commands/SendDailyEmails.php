<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginReminderMail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendDailyEmails extends Command
{
    protected $signature = 'emails:send-login-reminders';
    protected $description = 'Send up to 100 login reminder emails daily';

    public function handle(): int
    {
        $allowedDomains = [
            'gmail.com',
            'yahoo.com',
            'outlook.com',
            'hotmail.com',
        ];

        $sent = 0;
        $deleted = 0;
        $failed = 0;
        $skipped = 0;

        $query = User::query()
            
            ->where('subscribed', true)

            ->orderBy('id');

        $query->limit(100)->chunkById(50, function ($users) use (&$sent, &$deleted, &$failed, &$skipped, $allowedDomains) {

            foreach ($users as $user) {

                // 🔍 Extract domain safely
                $emailDomain = null;
                if (strpos($user->email, '@') !== false) {
                    $emailDomain = strtolower(substr(strrchr($user->email, "@"), 1));
                }

                // ❌ Skip invalid or unknown domains
                if (!$emailDomain || !in_array($emailDomain, $allowedDomains)) {
                    $this->warn("Skipped (invalid/unknown domain): {$user->email}");
                    $skipped++;
                    continue;
                }

                // 🔴 Delete users with "test" in email
                if (stripos($user->email, 'test') !== false) {
                    $this->warn("Deleted test email user: {$user->email}");
                    $user->delete();
                    $deleted++;
                    continue;
                }

                try {

                    Mail::to($user->email)
                        ->send(new LoginReminderMail($user));

                    $user->update([
                        'email_sent' => true,
                        'email_sent_at' => now(),
                    ]);

                    $this->info("Reminder sent to {$user->email}");
                    $sent++;

                } catch (TransportExceptionInterface $e) {

                    $this->error("Mail transport failed for {$user->email}");
                    $this->error($e->getMessage());
                    $failed++;

                } catch (\Throwable $e) {

                    $this->error("Unexpected error for {$user->email}");
                    $this->error($e->getMessage());
                    $failed++;
                }
            }
        });

        $this->line('');
        $this->info("Finished.");
        $this->info("Sent: {$sent}");
        $this->warn("Skipped (unknown domains): {$skipped}");
        $this->warn("Deleted (test emails): {$deleted}");
        $this->error("Failed: {$failed}");

        return self::SUCCESS;
    }
}