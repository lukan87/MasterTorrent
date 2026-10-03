<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Throwable;

class ImportLegacyUsers extends Command
{
    protected $signature = 'users:import-legacy
                        {file=/var/www/fileiplay.org/users1.sql : Path to the legacy users1 SQL dump}
                        {--import : Actually import safe users into the current users table}
                        {--email= : Import only the legacy user with this email address}';

    protected $description = 'Analyse or import legacy users1 accounts into the current FileIplay users table';

    /**
     * Fields that exist in the legacy database and that we want
     * to preserve where possible.
     */
    private const LEGACY_FIELDS = [
        'invites',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'last_upload',
        'last_browsex',
        'last_browse',
        'recovery_code',
        'profile_image',
        'user_class',
        'last_activity',
        'acceptpm',
        'title',
        'enabled',
        'donor',
        'info',
        'IP',
        'uploadpos',
        'downloadpos',
        'gender',
        'seedbonus',
        'is_immune',
        'is_freeleech',
        'passkey',
        'uploaded',
        'downloaded',
        'vip_until',
        'failed_attempts',
        'banned_until',
        'rsskey',
        'hit_and_run_count',
        'can_delete',
        'invites',
        'invited_by',
        'invite_code',
        'warned',
        'warned_until',
        'warned_reason',
        'slots',
        'timezone',
    ];

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        $performImport = (bool) $this->option('import');

        $onlyEmail = $this->option('email');

        $onlyEmail = $onlyEmail !== null
         ? $this->normalizeEmail($onlyEmail)
         : null;

        $this->newLine();
        $this->info('FileIplay Legacy User Import');

        if ($performImport) {
            $this->warn('IMPORT MODE — safe legacy users can be inserted.');
        } else {
            $this->line('DRY RUN ONLY — no database changes will be made.');
        }

        $this->newLine();

        if (!is_file($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        if (!is_readable($file)) {
            $this->error("File is not readable: {$file}");
            return self::FAILURE;
        }

        if (!Schema::hasTable('users')) {
            $this->error('The current users table does not exist.');
            return self::FAILURE;
        }

        $this->line("Source: {$file}");
        $this->line('Size: ' . $this->formatBytes(filesize($file)));
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | Parse legacy dump
        |--------------------------------------------------------------------------
        */

        $this->info('Reading legacy SQL dump...');

        try {
            $legacyUsers = $this->parseSqlDump($file);
        } catch (Throwable $e) {
            $this->error('Unable to parse the SQL dump.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (empty($legacyUsers)) {
            $this->error('No users were found in the SQL dump.');
            return self::FAILURE;
        }

        $this->info(
            'Parsed ' . number_format(count($legacyUsers)) . ' legacy users.'
        );

        /*
        |--------------------------------------------------------------------------
        | Current users
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('Loading current FileIplay users...');

        $currentUsers = DB::table('users')
            ->select('id', 'name', 'email', 'passkey')
            ->get();

        $this->line(
            'Current users: ' . number_format($currentUsers->count())
        );

        /*
        |--------------------------------------------------------------------------
        | Current indexes
        |--------------------------------------------------------------------------
        */

        $currentByEmail = [];
        $currentByName = [];
        $currentByPasskey = [];

        foreach ($currentUsers as $user) {
            $email = $this->normalizeEmail($user->email);
            $name = $this->normalizeName($user->name);
            $passkey = $this->normalizePasskey($user->passkey);

            if ($email !== '') {
                $currentByEmail[$email] = true;
            }

            if ($name !== '') {
                $currentByName[$name] = true;
            }

            if ($passkey !== '') {
                $currentByPasskey[$passkey] = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Legacy duplicate indexes
        |--------------------------------------------------------------------------
        */

        $legacyEmailCounts = [];
        $legacyNameCounts = [];
        $legacyPasskeyCounts = [];

        foreach ($legacyUsers as $legacy) {
            $email = $this->normalizeEmail($legacy['email'] ?? null);
            $name = $this->normalizeName($legacy['name'] ?? null);
            $passkey = $this->normalizePasskey($legacy['passkey'] ?? null);

            if ($email !== '') {
                $legacyEmailCounts[$email] =
                    ($legacyEmailCounts[$email] ?? 0) + 1;
            }

            if ($name !== '') {
                $legacyNameCounts[$name] =
                    ($legacyNameCounts[$name] ?? 0) + 1;
            }

            if ($passkey !== '') {
                $legacyPasskeyCounts[$passkey] =
                    ($legacyPasskeyCounts[$passkey] ?? 0) + 1;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Analyse safe users
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' => count($legacyUsers),
            'valid_email' => 0,
            'invalid_email' => 0,
            'safe_new' => 0,
            'existing_email' => 0,
            'existing_name' => 0,
            'existing_passkey' => 0,
            'duplicate_email' => 0,
            'duplicate_name' => 0,
            'duplicate_passkey' => 0,
        ];

        $safeUsers = [];
        $invalidEmails = [];
        $conflicts = [];

        foreach ($legacyUsers as $legacy) {
            $legacyId = (string) ($legacy['id'] ?? '');
            $nameRaw = trim((string) ($legacy['name'] ?? ''));
            $emailRaw = trim((string) ($legacy['email'] ?? ''));
            $passkeyRaw = trim((string) ($legacy['passkey'] ?? ''));

            $email = $this->normalizeEmail($emailRaw);
            $name = $this->normalizeName($nameRaw);
            $passkey = $this->normalizePasskey($passkeyRaw);

            /*
            |--------------------------------------------------------------------------
            | Basic required data
            |--------------------------------------------------------------------------
            */

            if (
                $email === '' ||
                filter_var($email, FILTER_VALIDATE_EMAIL) === false
            ) {
                $stats['invalid_email']++;

                if (count($invalidEmails) < 50) {
                    $invalidEmails[] = [
                        'id' => $legacyId,
                        'name' => $nameRaw,
                        'email' => $emailRaw,
                    ];
                }

                continue;
            }

            $stats['valid_email']++;

            $reasons = [];

            /*
            |--------------------------------------------------------------------------
            | Current database conflicts
            |--------------------------------------------------------------------------
            */

            if (isset($currentByEmail[$email])) {
                $stats['existing_email']++;
                $reasons[] = 'email exists';
            }

            if ($name !== '' && isset($currentByName[$name])) {
                $stats['existing_name']++;
                $reasons[] = 'username exists';
            }

            if ($passkey !== '' && isset($currentByPasskey[$passkey])) {
                $stats['existing_passkey']++;
                $reasons[] = 'passkey exists';
            }

            /*
            |--------------------------------------------------------------------------
            | Legacy conflicts
            |--------------------------------------------------------------------------
            */

            if (($legacyEmailCounts[$email] ?? 0) > 1) {
                $stats['duplicate_email']++;
                $reasons[] = 'duplicate legacy email';
            }

            if ($name !== '' && ($legacyNameCounts[$name] ?? 0) > 1) {
                $stats['duplicate_name']++;
                $reasons[] = 'duplicate legacy username';
            }

            if (
                $passkey !== '' &&
                ($legacyPasskeyCounts[$passkey] ?? 0) > 1
            ) {
                $stats['duplicate_passkey']++;
                $reasons[] = 'duplicate legacy passkey';
            }

            $reasons = array_values(array_unique($reasons));

            if (!empty($reasons)) {
                if (count($conflicts) < 100) {
                    $conflicts[] = [
                        'id' => $legacyId,
                        'name' => $nameRaw,
                        'email' => $emailRaw,
                        'reason' => implode(', ', $reasons),
                    ];
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Required import fields
            |--------------------------------------------------------------------------
            */

            if ($nameRaw === '') {
                if (count($conflicts) < 100) {
                    $conflicts[] = [
                        'id' => $legacyId,
                        'name' => '',
                        'email' => $emailRaw,
                        'reason' => 'empty username',
                    ];
                }

                continue;
            }

            if (trim((string) ($legacy['password'] ?? '')) === '') {
                if (count($conflicts) < 100) {
                    $conflicts[] = [
                        'id' => $legacyId,
                        'name' => $nameRaw,
                        'email' => $emailRaw,
                        'reason' => 'empty password',
                    ];
                }

                continue;
            }

            if ($passkeyRaw === '') {
                if (count($conflicts) < 100) {
                    $conflicts[] = [
                        'id' => $legacyId,
                        'name' => $nameRaw,
                        'email' => $emailRaw,
                        'reason' => 'empty passkey',
                    ];
                }

                continue;
            }

            $safeUsers[] = $legacy;
            $stats['safe_new']++;
        }

        /*
|--------------------------------------------------------------------------
| Optional single-user test
|--------------------------------------------------------------------------
|
| When --email is supplied, restrict the actual import to that one
| already-validated legacy account.
|
*/

if ($onlyEmail !== null) {
    $matchingSafeUsers = array_values(array_filter(
        $safeUsers,
        fn (array $legacy) =>
            $this->normalizeEmail($legacy['email'] ?? null) === $onlyEmail
    ));

    $this->newLine();
    $this->info('SINGLE USER MODE');
    $this->line("Requested email: {$onlyEmail}");

    if (empty($matchingSafeUsers)) {
        $this->error(
            'This email was not found among the safe legacy users.'
        );

        $this->line(
            'It may be invalid, conflicting with an existing account, ' .
            'or not present in the legacy dump.'
        );

        return self::FAILURE;
    }

    $safeUsers = $matchingSafeUsers;

    $this->line(
        'User selected for import: ' .
        ($safeUsers[0]['name'] ?? 'Unknown') .
        ' <' .
        ($safeUsers[0]['email'] ?? '') .
        '>'
    );
}

        /*
        |--------------------------------------------------------------------------
        | Report
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('IMPORT ANALYSIS');

        $this->table(
            ['Check', 'Count'],
            [
                ['Legacy users', number_format($stats['total'])],
                ['Valid email addresses', number_format($stats['valid_email'])],
                ['Invalid email addresses', number_format($stats['invalid_email'])],
                ['Safe users to import', number_format($stats['safe_new'])],
                ['Existing email conflicts', number_format($stats['existing_email'])],
                ['Existing username conflicts', number_format($stats['existing_name'])],
                ['Existing passkey conflicts', number_format($stats['existing_passkey'])],
                ['Legacy duplicate emails', number_format($stats['duplicate_email'])],
                ['Legacy duplicate usernames', number_format($stats['duplicate_name'])],
                ['Legacy duplicate passkeys', number_format($stats['duplicate_passkey'])],
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Invalid email report
        |--------------------------------------------------------------------------
        */

        if (!empty($invalidEmails)) {
            $this->newLine();
            $this->warn('Invalid email addresses — these will NOT be imported.');

            $this->table(
                ['Legacy ID', 'Name', 'Email'],
                array_map(
                    static fn (array $row) => [
                        $row['id'],
                        $row['name'],
                        $row['email'],
                    ],
                    $invalidEmails
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Conflict report
        |--------------------------------------------------------------------------
        */

        if (!empty($conflicts)) {
            $this->newLine();
            $this->warn('Conflicting accounts — these will NOT be imported.');

            $this->table(
                ['Legacy ID', 'Name', 'Email', 'Reason'],
                array_map(
                    static fn (array $row) => [
                        $row['id'],
                        $row['name'],
                        $row['email'],
                        $row['reason'],
                    ],
                    $conflicts
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preservation report
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('LEGACY DATA PRESERVED');

        $this->line('✓ password — existing hash copied unchanged');
        $this->line('✓ passkey — existing tracker passkey copied unchanged');
        $this->line('✓ seedbonus');
        $this->line('✓ title');
        $this->line('✓ profile_image');
        $this->line('✓ uploaded / downloaded totals');
        $this->line('✓ user class');
        $this->line('✓ donor status');
        $this->line('✓ account enabled status');
        $this->line('✓ activity dates');
        $this->line('✓ warnings');
        $this->line('✓ VIP date');
        $this->line('✓ hit-and-run count');
        $this->line('✓ freeleech / immunity');
        $this->line('✓ tracker permissions');

        /*
        |--------------------------------------------------------------------------
        | Dry run stops here
        |--------------------------------------------------------------------------
        */

        if (!$performImport) {
            $this->newLine();

            $this->warn(
                'DRY RUN COMPLETE — no rows were inserted, updated or deleted.'
            );

            $this->newLine();

            $this->line('To perform the import, run:');

            $this->line(
                'php artisan users:import-legacy ' .
                escapeshellarg($file) .
                ' --import'
            );

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Import confirmation
        |--------------------------------------------------------------------------
        */

        if (empty($safeUsers)) {
            $this->newLine();
            $this->warn('There are no safe users to import.');

            return self::SUCCESS;
        }

        $this->newLine();

        $this->warn(
            'You are about to insert ' .
            number_format(count($safeUsers)) .
            ' users into the LIVE users table.'
        );

        $this->line(
            'Existing users will not be updated or deleted.'
        );

        $this->line(
            'Legacy IDs will NOT be reused. MySQL will generate new IDs.'
        );

        $this->line(
            'Legacy passwords and passkeys will be preserved.'
        );

        $this->newLine();

        if (!$this->confirm('Do you want to continue with the import?', false)) {
            $this->warn('Import cancelled.');

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Prepare insert rows
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('Preparing users for import...');

        $insertRows = [];

        foreach ($safeUsers as $legacy) {
            $insertRows[] = $this->mapLegacyUser($legacy);
        }

        /*
        |--------------------------------------------------------------------------
        | Import
        |--------------------------------------------------------------------------
        |
        | We use a single transaction so that if any chunk fails, the entire
        | import is rolled back.
        |
        */

        $inserted = 0;
        $chunkSize = 250;

        $this->newLine();
        $this->info('Importing users...');

        $progress = $this->output->createProgressBar(count($insertRows));

        $progress->start();

        try {
            DB::transaction(function () use (
                $insertRows,
                $chunkSize,
                &$inserted,
                $progress
            ) {
                foreach (array_chunk($insertRows, $chunkSize) as $chunk) {
                    DB::table('users')->insert($chunk);

                    $inserted += count($chunk);
                    $progress->advance(count($chunk));
                }
            }, 1);
        } catch (Throwable $e) {
            $progress->finish();

            $this->newLine(2);
            $this->error('IMPORT FAILED.');
            $this->error('The database transaction was rolled back.');
            $this->error('No partial import should remain.');
            $this->newLine();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $progress->finish();

        /*
        |--------------------------------------------------------------------------
        | Verify
        |--------------------------------------------------------------------------
        */

        $this->newLine(2);
        $this->info('Import transaction completed.');

        $currentCountAfter = DB::table('users')->count();

        $this->newLine();

        $this->table(
            ['Result', 'Count'],
            [
                ['Imported users', number_format($inserted)],
                ['Skipped invalid emails', number_format($stats['invalid_email'])],
                [
                    'Skipped existing/conflicting accounts',
                    number_format(
                        $stats['total']
                        - $stats['invalid_email']
                        - $stats['safe_new']
                    ),
                ],
                ['Users now in FileIplay', number_format($currentCountAfter)],
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Post-import verification
        |--------------------------------------------------------------------------
        */

        $missingPasswords = DB::table('users')
            ->whereNull('password')
            ->orWhere('password', '')
            ->count();

        $missingPasskeys = DB::table('users')
            ->whereNull('passkey')
            ->orWhere('passkey', '')
            ->count();

        $duplicateEmails = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $duplicatePasskeys = DB::table('users')
            ->select('passkey')
            ->groupBy('passkey')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $duplicateNames = DB::table('users')
            ->selectRaw('LOWER(name) AS normalized_name')
            ->groupByRaw('LOWER(name)')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $this->newLine();
        $this->info('POST-IMPORT CHECKS');

        $this->table(
            ['Check', 'Result'],
            [
                ['Missing passwords', number_format($missingPasswords)],
                ['Missing passkeys', number_format($missingPasskeys)],
                ['Duplicate emails', number_format($duplicateEmails)],
                ['Duplicate passkeys', number_format($duplicatePasskeys)],
                ['Duplicate usernames', number_format($duplicateNames)],
            ]
        );

        if (
            $missingPasswords === 0 &&
            $missingPasskeys === 0 &&
            $duplicateEmails === 0 &&
            $duplicatePasskeys === 0 &&
            $duplicateNames === 0
        ) {
            $this->newLine();
            $this->info('Legacy user import completed successfully.');
        } else {
            $this->newLine();

            $this->warn(
                'Import completed, but review the post-import checks above.'
            );
        }

        return self::SUCCESS;
    }

    /**
     * Map a legacy users1 row into the current users table.
     *
     * IMPORTANT:
     * We deliberately do not include "id".
     * MySQL generates a new ID.
     */
    private function mapLegacyUser(array $legacy): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Account
            |--------------------------------------------------------------------------
            */

            'invites' => $this->unsignedInt(
                $legacy['invites'] ?? 1,
                1
            ),

            'name' => trim((string) $legacy['name']),

            'email' => trim((string) $legacy['email']),

            /*
            |--------------------------------------------------------------------------
            | New email system
            |--------------------------------------------------------------------------
            |
            | The legacy database has no subscription-consent field.
            | Therefore imported accounts are NOT automatically subscribed.
            |
            */

            'subscribed' => 0,
            'email_sent' => 0,
            'is_junk' => 0,
            'email_sent_at' => null,

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            'email_verified_at' => $this->nullableDate(
                $legacy['email_verified_at'] ?? null
            ),

            // PRESERVE ORIGINAL PASSWORD HASH.
            // Do NOT Hash::make() this value.
            'password' => (string) $legacy['password'],

            'security_version' => 1,

            'remember_token' => $this->nullableString(
                $legacy['remember_token'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->nullableDate(
                $legacy['created_at'] ?? null
            ),

            'updated_at' => $this->nullableDate(
                $legacy['updated_at'] ?? null
            ),

            'last_upload' => $this->nullableDate(
                $legacy['last_upload'] ?? null
            ),

            'last_browsex' => $this->nullableDate(
                $legacy['last_browsex'] ?? null
            ),

            'last_browse' => $this->nullableDate(
                $legacy['last_browse'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | Profile
            |--------------------------------------------------------------------------
            */

            // Temporary recovery code for migrated legacy accounts.
// User can change this from their profile after logging in.
            'recovery_code' => $this->temporaryRecoveryCodeHash(),

            // PRESERVE LEGACY PROFILE IMAGE.
            'profile_image' => $this->nullableString(
                $legacy['profile_image'] ?? null
            ),

            'cover' => null,
            'background' => null,

            /*
            |--------------------------------------------------------------------------
            | Class / activity
            |--------------------------------------------------------------------------
            */

            'user_class' => $this->unsignedInt(
                $legacy['user_class'] ?? 1,
                1
            ),

            'can_bump_unlimited' => 0,

            'last_activity' => $this->nullableDate(
                $legacy['last_activity'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | User preferences
            |--------------------------------------------------------------------------
            */

            'acceptpm' => $this->yesNo(
                $legacy['acceptpm'] ?? 'yes',
                'yes'
            ),

            // PRESERVE LEGACY TITLE.
            'title' => $this->nullableString(
                $legacy['title'] ?? null,
                60
            ),

            'enabled' => $this->yesNo(
                $legacy['enabled'] ?? 'yes',
                'yes'
            ),

            'donor' => $this->yesNo(
                $legacy['donor'] ?? 'no',
                'no'
            ),

            'info' => $legacy['info'] ?? null,

            'IP' => $this->nullableString(
                $legacy['IP'] ?? null,
                45
            ),

            'rsskey' => $this->nullableString(
                $legacy['rsskey'] ?? null,
                255
            ),

            /*
            |--------------------------------------------------------------------------
            | Tracker permissions
            |--------------------------------------------------------------------------
            */

            'uploadpos' => $this->yesNo(
                $legacy['uploadpos'] ?? 'no',
                'no'
            ),

            'downloadpos' => $this->yesNo(
                $legacy['downloadpos'] ?? 'no',
                'no'
            ),

            'gender' => $this->gender(
                $legacy['gender'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | Tracker stats
            |--------------------------------------------------------------------------
            */

            // PRESERVE LEGACY SEED BONUS.
            'seedbonus' => $this->decimal(
                $legacy['seedbonus'] ?? 0
            ),

            'seeding_reputation' => 0,

            'seeder_rank' => 0,

            'is_immune' => $this->booleanInt(
                $legacy['is_immune'] ?? 0
            ),

            'is_freeleech' => $this->booleanInt(
                $legacy['is_freeleech'] ?? 0
            ),

            // PRESERVE ORIGINAL TRACKER PASSKEY.
            'passkey' => trim((string) $legacy['passkey']),

            /*
            |--------------------------------------------------------------------------
            | Invitations
            |--------------------------------------------------------------------------
            */

            // The current column is VARCHAR, while the old value was numeric.
            // Preserve the legacy reference as text.
            'invited_by' => $this->nullableString(
                $legacy['invited_by'] ?? null
            ),

            'invite_code' => $this->nullableString(
                $legacy['invite_code'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | Ratio history
            |--------------------------------------------------------------------------
            */

            'uploaded' => $this->unsignedBigInt(
                $legacy['uploaded'] ?? 0
            ),

            'downloaded' => $this->unsignedBigInt(
                $legacy['downloaded'] ?? 0
            ),

            /*
            |--------------------------------------------------------------------------
            | VIP / security
            |--------------------------------------------------------------------------
            */

            'vip_until' => $this->nullableDate(
                $legacy['vip_until'] ?? null
            ),

            'failed_attempts' => $this->unsignedInt(
                $legacy['failed_attempts'] ?? 0
            ),

            'banned_until' => $this->nullableDate(
                $legacy['banned_until'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | H&R
            |--------------------------------------------------------------------------
            */

            'hit_and_run_count' => $this->unsignedInt(
                $legacy['hit_and_run_count'] ?? 0
            ),

            'can_delete' => $this->booleanInt(
                $legacy['can_delete'] ?? 0
            ),

            /*
            |--------------------------------------------------------------------------
            | Warnings
            |--------------------------------------------------------------------------
            */

            'warned' => $this->booleanInt(
                $legacy['warned'] ?? 0
            ),

            'warned_until' => $this->nullableDate(
                $legacy['warned_until'] ?? null
            ),

            'chatblock' => 0,
            'commentblock' => 0,
            'forumblock' => 0,

            'warned_reason' => $legacy['warned_reason'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | Slots / timezone
            |--------------------------------------------------------------------------
            */

            'slots' => $this->unsignedInt(
                $legacy['slots'] ?? 0
            ),

            'timezone' => $this->timezone(
                $legacy['timezone'] ?? null
            ),

            /*
            |--------------------------------------------------------------------------
            | New FileIplay fields
            |--------------------------------------------------------------------------
            */

            'cookie_consent' => null,
            'cookie_consent_at' => null,

            'rank_rewarded' => 0,
            'reputation_dirty' => 0,

            'deleted_at' => null,
            'deleted_by' => null,

            'email_bounced' => 0,
            'email_bounce_type' => null,
        ];
    }

    /**
     * Parse INSERT statements from users1.sql.
     */
    private function parseSqlDump(string $file): array
    {
        $handle = fopen($file, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "Unable to open SQL file: {$file}"
            );
        }

        $users = [];

        $statement = '';
        $collecting = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = ltrim($line);

                if (!$collecting) {
                    if (
                        str_starts_with(
                            $trimmed,
                            'INSERT INTO `users1`'
                        )
                    ) {
                        $statement = $line;
                        $collecting = true;

                        if ($this->statementComplete($statement)) {
                            foreach (
                                $this->parseInsertStatement($statement)
                                as $user
                            ) {
                                $users[] = $user;
                            }

                            $statement = '';
                            $collecting = false;
                        }
                    }

                    continue;
                }

                $statement .= $line;

                if ($this->statementComplete($statement)) {
                    foreach (
                        $this->parseInsertStatement($statement)
                        as $user
                    ) {
                        $users[] = $user;
                    }

                    $statement = '';
                    $collecting = false;
                }
            }

            if ($collecting && trim($statement) !== '') {
                throw new RuntimeException(
                    'The SQL file ended in the middle of an INSERT statement.'
                );
            }
        } finally {
            fclose($handle);
        }

        return $users;
    }

    private function statementComplete(string $statement): bool
    {
        $length = strlen($statement);

        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $statement[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                continue;
            }

            if ($char === ';') {
                return trim(substr($statement, $i + 1)) === '';
            }
        }

        return false;
    }

    private function parseInsertStatement(string $statement): array
    {
        if (
            !preg_match(
                '/INSERT\s+INTO\s+`users1`\s*\((.*?)\)\s*VALUES\s*(.*);/is',
                $statement,
                $matches
            )
        ) {
            throw new RuntimeException(
                'Unable to understand a users1 INSERT statement.'
            );
        }

        $columns = $this->parseColumns($matches[1]);
        $rows = $this->parseValueRows($matches[2]);

        $users = [];

        foreach ($rows as $row) {
            if (count($row) !== count($columns)) {
                throw new RuntimeException(
                    'Column/value count mismatch while parsing users1.'
                );
            }

            $combined = array_combine($columns, $row);

            if ($combined === false) {
                throw new RuntimeException(
                    'Unable to combine users1 columns and values.'
                );
            }

            $users[] = $combined;
        }

        return $users;
    }

    private function parseColumns(string $columnList): array
    {
        $columns = str_getcsv(
            $columnList,
            ',',
            '`',
            '\\'
        );

        return array_map(
            static fn ($column) => trim(
                trim((string) $column),
                " \t\n\r\0\x0B`"
            ),
            $columns
        );
    }

    private function parseValueRows(string $values): array
    {
        $rows = [];

        $length = strlen($values);

        $inString = false;
        $escaped = false;
        $depth = 0;

        $rowBuffer = '';
        $collectingRow = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];

            if (!$collectingRow) {
                if ($char === '(') {
                    $collectingRow = true;
                    $depth = 1;
                    $rowBuffer = '';
                }

                continue;
            }

            if ($inString) {
                $rowBuffer .= $char;

                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $rowBuffer .= $char;
                continue;
            }

            if ($char === '(') {
                $depth++;
                $rowBuffer .= $char;
                continue;
            }

            if ($char === ')') {
                $depth--;

                if ($depth === 0) {
                    $rows[] = $this->parseSqlRow($rowBuffer);
                    $rowBuffer = '';
                    $collectingRow = false;
                    continue;
                }

                $rowBuffer .= $char;
                continue;
            }

            $rowBuffer .= $char;
        }

        return $rows;
    }

    private function parseSqlRow(string $row): array
    {
        $values = [];

        $length = strlen($row);

        $buffer = '';
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $row[$i];

            if ($inString) {
                if ($escaped) {
                    $buffer .= $this->decodeMysqlEscape($char);
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                    continue;
                }

                $buffer .= $char;
                continue;
            }

            if ($char === "'") {
                $inString = true;
                continue;
            }

            if ($char === ',') {
                $values[] = $this->convertSqlValue($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->convertSqlValue($buffer);

        return $values;
    }

    private function convertSqlValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        return $value;
    }

    private function decodeMysqlEscape(string $char): string
    {
        return match ($char) {
            '0' => "\0",
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            'b' => "\x08",
            'Z' => "\x1A",
            default => $char,
        };
    }

    private function normalizeEmail(mixed $value): string
    {
        return mb_strtolower(
            trim((string) $value)
        );
    }

    private function normalizeName(mixed $value): string
    {
        return mb_strtolower(
            trim((string) $value)
        );
    }

    private function normalizePasskey(mixed $value): string
    {
        return strtolower(
            trim((string) $value)
        );
    }

    private function nullableString(
        mixed $value,
        ?int $maxLength = null
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if ($value === '') {
            return null;
        }

        if ($maxLength !== null) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if (
            $value === '' ||
            $value === '0000-00-00 00:00:00' ||
            $value === '0000-00-00'
        ) {
            return null;
        }

        return $value;
    }

    private function yesNo(
        mixed $value,
        string $default = 'no'
    ): string {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['yes', 'no'], true)
            ? $value
            : $default;
    }

    private function gender(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['male', 'female'], true)
            ? $value
            : null;
    }

    private function booleanInt(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $value = strtolower(trim((string) $value));

        return in_array(
            $value,
            ['1', 'true', 'yes', 'on'],
            true
        ) ? 1 : 0;
    }

    private function unsignedInt(
        mixed $value,
        int $default = 0
    ): int {
        if ($value === null || $value === '') {
            return $default;
        }

        $number = (int) $value;

        return max(0, $number);
    }

    private function unsignedBigInt(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = trim((string) $value);

        if (!preg_match('/^\d+$/', $value)) {
            return '0';
        }

        return ltrim($value, '0') ?: '0';
    }

    private function decimal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        if (!is_numeric($value)) {
            return '0.00';
        }

        return number_format(
            max(0, (float) $value),
            2,
            '.',
            ''
        );
    }

    private function timezone(mixed $value): string
    {
        $timezone = trim((string) $value);

        if ($timezone === '') {
            return 'Europe/London';
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            return 'Europe/London';
        }

        return mb_substr($timezone, 0, 50);
    }

    private function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $size = (float) $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, 2) . ' ' . $units[$unit];
    }

    /**
 * Generate the temporary migrated-user recovery code hash once
 * and reuse it for the entire import process.
 */
private function temporaryRecoveryCodeHash(): string
{
    static $hash = null;

    if ($hash === null) {
        $hash = Hash::make('fileiplay');
    }

    return $hash;
}
}