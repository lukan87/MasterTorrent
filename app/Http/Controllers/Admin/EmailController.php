<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminEmail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class EmailController extends Controller
{
    /**
     * Only Web Developers can access email administration.
     */
    private function authorizeEmailAdministration(): void
    {
        abort_unless(
            auth()->check() &&
            (int) auth()->user()->user_class === (int) UserClass::WEB_DEVELOPER,
            403
        );
    }

    /**
     * Display email history.
     */
    public function index()
    {
        $this->authorizeEmailAdministration();

        $emails = EmailLog::with('user')
            ->latest('sent_at')
            ->paginate(50);

        $subscribedCount = User::query()
            ->where('subscribed', true)
            ->where('is_junk', false)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->count();

        return view(
            'admin.emails.index',
            compact(
                'emails',
                'subscribedCount'
            )
        );
    }

    /**
     * Display email composer.
     */
    public function create()
    {
        $this->authorizeEmailAdministration();

        $templates = EmailTemplate::orderBy('name')->get();
        $classes = UserClass::getClasses();

        return view(
            'admin.emails.create',
            compact(
                'templates',
                'classes'
            )
        );
    }

    /**
     * Create the first campaign/batch and queue it.
     */
    public function send(Request $request)
    {
        $this->authorizeEmailAdministration();

        $this->validateSendRequest($request);

        $sendLimit = (string) $request->input(
            'send_limit',
            '100'
        );

        /*
        |--------------------------------------------------------------------------
        | Build recipient query
        |--------------------------------------------------------------------------
        */

        $recipientQuery = $this->recipients($request)
            ->orderBy('id');

        if ($sendLimit !== 'all') {
            $recipientQuery->limit(
                (int) $sendLimit
            );
        }

        $users = $recipientQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Safety checks
        |--------------------------------------------------------------------------
        */

        if ($users->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(
                    'No users match your selected filters.'
                );
        }

        if (
            $users->count() > 1000 &&
            strtoupper(
                trim((string) $request->confirm)
            ) !== 'SEND'
        ) {
            return back()
                ->withInput()
                ->withErrors(
                    'Large send detected. Type SEND to confirm sending to more than 1,000 users.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Save audience filters
        |--------------------------------------------------------------------------
        */

        $filters = [];

        if ($request->filled('user_class')) {
            $filters['user_class'] = array_map(
                'intval',
                $request->input(
                    'user_class',
                    []
                )
            );
        }

        $filters['send_limit'] =
            $sendLimit === 'all'
                ? 'all'
                : (int) $sendLimit;

        /*
        |--------------------------------------------------------------------------
        | Create mailing-series ID
        |--------------------------------------------------------------------------
        |
        | Every batch belonging to this mailing shares this UUID.
        |
        */

        $batchGroupId = (string) Str::uuid();

        $batchSize =
            $sendLimit === 'all'
                ? $users->count()
                : (int) $sendLimit;

        /*
        |--------------------------------------------------------------------------
        | Create campaign and recipient snapshots
        |--------------------------------------------------------------------------
        */

        try {
            $campaign = DB::transaction(
                function () use (
                    $request,
                    $users,
                    $filters,
                    $batchGroupId,
                    $batchSize
                ) {
                    $campaign = EmailCampaign::create([
                        'batch_group_id' => $batchGroupId,
                        'batch_number' => 1,
                        'batch_size' => $batchSize,
                        'previous_campaign_id' => null,

                        'subject' => trim(
                            (string) $request->subject
                        ),

                        'body' => $request->body,
                        'target' => $request->target,

                        'filters' => empty($filters)
                            ? null
                            : $filters,

                        'total_recipients' =>
                            $users->count(),

                        'queued_count' => 0,
                        'sent_count' => 0,
                        'failed_count' => 0,

                        'status' => 'queued',

                        'started_at' => null,
                        'completed_at' => null,
                    ]);

                    foreach ($users as $user) {
                        EmailCampaignRecipient::create([
                            'email_campaign_id' =>
                                $campaign->id,

                            'user_id' =>
                                $user->id,

                            'name' =>
                                $user->name,

                            'email' =>
                                $user->email,

                            'status' =>
                                'queued',

                            'attempts' => 0,
                            'error' => null,

                            'queued_at' => now(),
                            'processing_at' => null,
                            'sent_at' => null,
                            'failed_at' => null,
                        ]);
                    }

                    return $campaign;
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(
                    'The email campaign could not be created. No emails were queued.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Queue first batch
        |--------------------------------------------------------------------------
        */

        $queued = $this->dispatchCampaign(
            $campaign
        );

        /*
        |--------------------------------------------------------------------------
        | Queue failure
        |--------------------------------------------------------------------------
        */

        if ($queued < $campaign->total_recipients) {
            return redirect()
                ->route(
                    'admin.emails.campaigns.show',
                    $campaign
                )
                ->with(
                    'error',
                    number_format($queued)
                    . ' of '
                    . number_format(
                        $campaign->total_recipients
                    )
                    . ' email(s) were queued. A queue error occurred. Check the campaign before retrying.'
                );
        }

        return redirect()
            ->route(
                'admin.emails.campaigns.show',
                $campaign
            )
            ->with(
                'success',
                'Batch 1 created. '
                . number_format($queued)
                . ' email(s) queued for delivery.'
            );
    }

    /**
     * Create and queue the next batch in a mailing series.
     */
public function nextBatch(
    EmailCampaign $campaign
) {
    $this->authorizeEmailAdministration();

    if (!$campaign->batch_group_id) {
        return back()->with(
            'error',
            'This campaign was created before batch continuation was enabled.'
        );
    }

    $batchSize = (int) $campaign->batch_size;

    if ($batchSize < 1) {
        return back()->with(
            'error',
            'This campaign does not have a valid batch size.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | "All" campaigns do not have another batch
    |--------------------------------------------------------------------------
    */

    $originalFilters = (array) (
        $campaign->filters ?? []
    );

    if (($originalFilters['send_limit'] ?? null) === 'all') {
        return back()->with(
            'success',
            'This campaign already included the complete matching audience.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Atomic batch-series lock
    |--------------------------------------------------------------------------
    |
    | Prevent two requests from creating the same next batch if the button is
    | double-clicked or two browser requests arrive at almost the same time.
    |
    */

    $lock = Cache::lock(
        'email-batch-group:' . $campaign->batch_group_id,
        30
    );

    if (!$lock->get()) {
        return back()->with(
            'error',
            'Another batch request is already being processed. Please wait a moment.'
        );
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | Always work from the latest campaign
        |--------------------------------------------------------------------------
        */

        $latestCampaign = EmailCampaign::query()
            ->where(
                'batch_group_id',
                $campaign->batch_group_id
            )
            ->orderByDesc('batch_number')
            ->orderByDesc('id')
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Do not start another batch while the series is running
        |--------------------------------------------------------------------------
        */

        $seriesStillRunning =
            EmailCampaignRecipient::query()
                ->whereHas(
                    'campaign',
                    function ($query) use ($campaign) {
                        $query->where(
                            'batch_group_id',
                            $campaign->batch_group_id
                        );
                    }
                )
                ->whereIn(
                    'status',
                    [
                        'queued',
                        'processing',
                    ]
                )
                ->exists();

        if ($seriesStillRunning) {
            return redirect()
                ->route(
                    'admin.emails.campaigns.show',
                    $latestCampaign
                )
                ->with(
                    'error',
                    'Wait for the current batch to finish before starting the next batch.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Restore original audience
        |--------------------------------------------------------------------------
        */

        $filters = (array) (
            $latestCampaign->filters ?? []
        );

        $requestData = [
            'target' => $latestCampaign->target,
        ];

        if (!empty($filters['user_class'])) {
            $requestData['user_class'] =
                $filters['user_class'];
        }

        $recipientRequest = Request::create(
            '/',
            'GET',
            $requestData
        );

        $recipientQuery = $this->recipients(
            $recipientRequest
        );

        /*
        |--------------------------------------------------------------------------
        | Exclude every user already used in this mailing series
        |--------------------------------------------------------------------------
        */

        $alreadyUsedUserIds =
            EmailCampaignRecipient::query()
                ->whereNotNull('user_id')
                ->whereHas(
                    'campaign',
                    function ($query) use ($campaign) {
                        $query->where(
                            'batch_group_id',
                            $campaign->batch_group_id
                        );
                    }
                )
                ->select('user_id');

        $users = $recipientQuery
            ->whereNotIn(
                'users.id',
                $alreadyUsedUserIds
            )
            ->orderBy('users.id')
            ->limit($batchSize)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Mailing series complete
        |--------------------------------------------------------------------------
        */

        if ($users->isEmpty()) {
            return redirect()
                ->route(
                    'admin.emails.campaigns.show',
                    $latestCampaign
                )
                ->with(
                    'success',
                    'This mailing series is complete. There are no remaining matching users.'
                );
        }

        $nextBatchNumber =
            ((int) $latestCampaign->batch_number) + 1;

        /*
        |--------------------------------------------------------------------------
        | Create next campaign + snapshots
        |--------------------------------------------------------------------------
        */

        try {
            $nextCampaign = DB::transaction(
                function () use (
                    $latestCampaign,
                    $users,
                    $filters,
                    $nextBatchNumber,
                    $batchSize
                ) {
                    $nextCampaign =
                        EmailCampaign::create([
                            'batch_group_id' =>
                                $latestCampaign->batch_group_id,

                            'batch_number' =>
                                $nextBatchNumber,

                            'batch_size' =>
                                $batchSize,

                            'previous_campaign_id' =>
                                $latestCampaign->id,

                            'subject' =>
                                $latestCampaign->subject,

                            'body' =>
                                $latestCampaign->body,

                            'target' =>
                                $latestCampaign->target,

                            'filters' =>
                                empty($filters)
                                    ? null
                                    : $filters,

                            'total_recipients' =>
                                $users->count(),

                            'queued_count' => 0,
                            'sent_count' => 0,
                            'failed_count' => 0,

                            'status' => 'queued',

                            'started_at' => null,
                            'completed_at' => null,
                        ]);

                    foreach ($users as $user) {
                        EmailCampaignRecipient::create([
                            'email_campaign_id' =>
                                $nextCampaign->id,

                            'user_id' =>
                                $user->id,

                            'name' =>
                                $user->name,

                            'email' =>
                                $user->email,

                            'status' =>
                                'queued',

                            'attempts' => 0,
                            'error' => null,

                            'queued_at' => now(),
                            'processing_at' => null,
                            'sent_at' => null,
                            'failed_at' => null,
                        ]);
                    }

                    return $nextCampaign;
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'The next batch could not be created.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Dispatch next batch
        |--------------------------------------------------------------------------
        */

        $queued = $this->dispatchCampaign(
            $nextCampaign
        );

        if (
            $queued <
            $nextCampaign->total_recipients
        ) {
            return redirect()
                ->route(
                    'admin.emails.campaigns.show',
                    $nextCampaign
                )
                ->with(
                    'error',
                    'Batch '
                    . number_format($nextBatchNumber)
                    . ' was created, but only '
                    . number_format($queued)
                    . ' of '
                    . number_format(
                        $nextCampaign->total_recipients
                    )
                    . ' email job(s) were submitted. Check the queue before continuing.'
                );
        }

        return redirect()
            ->route(
                'admin.emails.campaigns.show',
                $nextCampaign
            )
            ->with(
                'success',
                'Batch '
                . number_format($nextBatchNumber)
                . ' created with '
                . number_format($queued)
                . ' recipient(s).'
            );

    } finally {

        /*
        |--------------------------------------------------------------------------
        | Always release the Redis lock
        |--------------------------------------------------------------------------
        */

        $lock->release();
    }
}

    /**
     * Dispatch all queued recipients belonging to a campaign.
     *
     * Returns the number of jobs successfully submitted to Redis.
     */
private function dispatchCampaign(
    EmailCampaign $campaign
): int {
    /*
    |--------------------------------------------------------------------------
    | Initialise counters BEFORE dispatch
    |--------------------------------------------------------------------------
    |
    | The worker may start processing immediately after the first job reaches
    | Redis. Therefore queued_count must already contain the number of queued
    | recipient snapshots before we dispatch anything.
    |
    */

    $totalQueued = EmailCampaignRecipient::query()
        ->where('email_campaign_id', $campaign->id)
        ->where('status', 'queued')
        ->count();

    if ($totalQueued === 0) {
        $campaign->update([
            'queued_count' => 0,
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        return 0;
    }

    DB::transaction(function () use (
        $campaign,
        $totalQueued
    ) {
        $lockedCampaign = EmailCampaign::query()
            ->whereKey($campaign->id)
            ->lockForUpdate()
            ->firstOrFail();

        $lockedCampaign->queued_count = $totalQueued;
        $lockedCampaign->status = 'sending';
        $lockedCampaign->started_at ??= now();
        $lockedCampaign->completed_at = null;

        $lockedCampaign->save();
    });

    /*
    |--------------------------------------------------------------------------
    | Dispatch jobs
    |--------------------------------------------------------------------------
    */

    $dispatched = 0;
    $dispatchFailed = false;

    try {
        EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->orderBy('id')
            ->chunkById(
                500,
                function ($recipients) use (&$dispatched) {
                    foreach ($recipients as $recipient) {
                        SendAdminEmail::dispatch(
                            $recipient->id
                        );

                        $dispatched++;
                    }
                }
            );
    } catch (Throwable $exception) {
        report($exception);

        $dispatchFailed = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Handle a partial Redis dispatch failure
    |--------------------------------------------------------------------------
    |
    | Jobs successfully submitted may already be processing, so we must not
    | overwrite queued_count with $dispatched.
    |
    | Any recipients still in queued state after a dispatch exception may
    | include successfully-dispatched jobs waiting in Redis, so we also must
    | not blindly mark every queued row failed.
    |
    | Instead we leave their snapshots intact and report the partial dispatch.
    | This avoids corrupting campaign counters or accidentally double-sending.
    |
    */

    if ($dispatchFailed) {
        $campaign->refresh();

        return $dispatched;
    }

    /*
    |--------------------------------------------------------------------------
    | Important
    |--------------------------------------------------------------------------
    |
    | Do NOT set queued_count here.
    |
    | SendAdminEmail changes queued -> processing transactionally and
    | decrements queued_count itself. Writing the original total here would
    | race with the live worker and could restore a stale value.
    |
    */

    return $dispatched;
}

    /**
     * Return live matching-recipient count.
     *
     * This deliberately returns the complete matching audience.
     * The composer can then separately show how many will actually
     * be selected by send_limit.
     */
    public function count(Request $request)
    {
        $this->authorizeEmailAdministration();

        $this->validateTargetRequest($request);

        return response()->json([
            'count' =>
                $this->recipients($request)
                    ->count(),
        ]);
    }

    /*
|--------------------------------------------------------------------------
| Build recipient query
|--------------------------------------------------------------------------
|
| Only users with a valid, non-junk and non-bounced email address may
| be selected for an email campaign.
|
| Once users.email_bounced is set to true, that user will never be
| selected for another campaign.
|
*/
private function recipients(Request $request)
{
    $query = User::query()
        ->where('is_junk', false)

        /*
         * Never send campaign emails to an address that has
         * previously failed delivery.
         *
         * NULL is treated the same as false for older users.
         */
        ->where(function ($query) {
            $query
                ->where('email_bounced', false)
                ->orWhereNull('email_bounced');
        })

        ->whereNotNull('email')
        ->where('email', '!=', '');

    switch ($request->target) {
        /*
         * Subscribed users only.
         */
        case 'subscribed':
            $query->where(
                'subscribed',
                true
            );
            break;

        /*
         * All valid non-junk, non-bounced users with an email.
         */
        case 'all':
            break;

        /*
         * Selected user classes.
         */
        case 'user_class':
            $query->whereIn(
                'user_class',
                $request->input(
                    'user_class',
                    []
                )
            );
            break;

        /*
         * Active during last 30 days.
         */
        case 'active':
            $query
                ->whereNotNull(
                    'last_activity'
                )
                ->where(
                    'last_activity',
                    '>=',
                    now()->subDays(30)
                );
            break;

        /*
         * Inactive for at least 60 days.
         */
        case 'inactive':
            $query->where(
                function ($query) {
                    $query
                        ->whereNull(
                            'last_activity'
                        )
                        ->orWhere(
                            'last_activity',
                            '<=',
                            now()->subDays(60)
                        );
                }
            );
            break;

        /*
         * Seeders.
         */
        case 'seeders':
            $query->whereHas(
                'peers',
                function ($query) {
                    $query->where(
                        'seeder',
                        true
                    );
                }
            );
            break;

        /*
         * Leechers.
         */
        case 'leechers':
            $query->whereHas(
                'peers',
                function ($query) {
                    $query->where(
                        'seeder',
                        false
                    );
                }
            );
            break;

        /*
         * Hit & Run users.
         */
        case 'hit_and_run':
            $query->where(
                'hit_and_run_count',
                '>',
                0
            );
            break;

        /*
         * Donors.
         */
        case 'donors':
            $query->where(
                'donor',
                'yes'
            );
            break;

        /*
         * Warned users.
         */
        case 'warned':
            $query->where(
                'warned',
                true
            );
            break;

        /*
         * Disabled users.
         */
        case 'disabled':
            $query->where(
                'enabled',
                'no'
            );
            break;
    }

    /*
    |--------------------------------------------------------------------------
    | Optional class filter
    |--------------------------------------------------------------------------
    */

    if (
        $request->target !== 'user_class' &&
        $request->filled('user_class')
    ) {
        $query->whereIn(
            'user_class',
            $request->input(
                'user_class',
                []
            )
        );
    }

    return $query;
}

    /**
     * Validate full send request.
     */
    private function validateSendRequest(
        Request $request
    ): void {
        $request->validate([
            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
                'max:100000',
            ],

            'target' => [
                'required',
                Rule::in(
                    $this->allowedTargets()
                ),
            ],

            'send_limit' => [
                'required',
                Rule::in([
                    '100',
                    '500',
                    '1000',
                    '1500',
                    '2000',
                    'all',
                ]),
            ],

            'user_class' => [
                'nullable',
                'array',
                'required_if:target,user_class',
            ],

            'user_class.*' => [
                'integer',
                'distinct',
                Rule::in(
                    $this->allowedUserClasses()
                ),
            ],

            'confirm' => [
                'nullable',
                'string',
            ],
        ]);
    }

    /**
     * Validate recipient-count request.
     */
    private function validateTargetRequest(
        Request $request
    ): void {
        $request->validate([
            'target' => [
                'required',
                Rule::in(
                    $this->allowedTargets()
                ),
            ],

            'user_class' => [
                'nullable',
                'array',
                'required_if:target,user_class',
            ],

            'user_class.*' => [
                'integer',
                'distinct',
                Rule::in(
                    $this->allowedUserClasses()
                ),
            ],
        ]);
    }

    /**
     * Allowed audiences.
     */
    private function allowedTargets(): array
    {
        return [
            'subscribed',
            'all',
            'user_class',
            'active',
            'inactive',
            'seeders',
            'leechers',
            'hit_and_run',
            'donors',
            'warned',
            'disabled',
        ];
    }

    /**
     * Allowed user classes.
     */
    private function allowedUserClasses(): array
    {
        return array_map(
            'intval',
            array_keys(
                UserClass::getClasses()
            )
        );
    }

    /**
     * Parse template variables.
     */
    private function parse(
        string $text,
        User $user
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
                $user->name,
                $user->name,
                $user->name,
                $user->name,
                $user->email,
                $user->email,
            ],
            $text
        );
    }

    /**
     * Display campaigns.
     */
    public function campaigns(
        Request $request
    ) {
        $this->authorizeEmailAdministration();

        $query = EmailCampaign::query()
            ->withCount([
                'recipients as processing_count' =>
                    function ($query) {
                        $query->where(
                            'status',
                            'processing'
                        );
                    },
            ])
            ->latest('id');

        if ($request->filled('status')) {
            $allowedStatuses = [
                'queued',
                'sending',
                'completed',
                'completed_with_failures',
                'failed',
            ];

            if (
                in_array(
                    $request->status,
                    $allowedStatuses,
                    true
                )
            ) {
                $query->where(
                    'status',
                    $request->status
                );
            }
        }

        $campaigns = $query
            ->paginate(25)
            ->withQueryString();

        return view(
            'admin.emails.campaigns.index',
            compact('campaigns')
        );
    }

    /**
     * Display one campaign.
     */
    public function showCampaign(
        Request $request,
        EmailCampaign $campaign
    ) {
        $this->authorizeEmailAdministration();

        $recipients = $campaign->recipients()
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $allowedStatuses = [
                        'queued',
                        'processing',
                        'sent',
                        'failed',
                    ];

                    if (
                        in_array(
                            $request->status,
                            $allowedStatuses,
                            true
                        )
                    ) {
                        $query->where(
                            'status',
                            $request->status
                        );
                    }
                }
            )
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->search
                    );

                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $recipientCounts =
            $campaign->recipients()
                ->selectRaw(
                    'status, COUNT(*) as total'
                )
                ->groupBy('status')
                ->pluck(
                    'total',
                    'status'
                );

        /*
        |--------------------------------------------------------------------------
        | Batch-series information
        |--------------------------------------------------------------------------
        */

        $seriesCampaigns = collect();
        $seriesTotalRecipients = 0;
        $seriesRemaining = null;

        if ($campaign->batch_group_id) {
            $seriesCampaigns =
                EmailCampaign::query()
                    ->where(
                        'batch_group_id',
                        $campaign->batch_group_id
                    )
                    ->orderBy('batch_number')
                    ->get();

            $seriesTotalRecipients =
                $seriesCampaigns->sum(
                    'total_recipients'
                );

            /*
             * Determine how many CURRENTLY matching users have not yet
             * appeared in this mailing series.
             */

            $filters = (array) (
                $campaign->filters ?? []
            );

            $requestData = [
                'target' => $campaign->target,
            ];

            if (!empty($filters['user_class'])) {
                $requestData['user_class'] =
                    $filters['user_class'];
            }

            $seriesRequest = Request::create(
                '/',
                'GET',
                $requestData
            );

            $usedUserIds =
                EmailCampaignRecipient::query()
                    ->whereNotNull('user_id')
                    ->whereHas(
                        'campaign',
                        function ($query) use ($campaign) {
                            $query->where(
                                'batch_group_id',
                                $campaign->batch_group_id
                            );
                        }
                    )
                    ->select('user_id');

            $seriesRemaining =
                $this->recipients($seriesRequest)
                    ->whereNotIn(
                        'users.id',
                        $usedUserIds
                    )
                    ->count();
        }

        return view(
            'admin.emails.campaigns.show',
            compact(
                'campaign',
                'recipients',
                'recipientCounts',
                'seriesCampaigns',
                'seriesTotalRecipients',
                'seriesRemaining'
            )
        );
    }


    /**
     * Delete an email log.
     */
    public function destroy(
        EmailLog $email
    ) {
        $this->authorizeEmailAdministration();

        $email->delete();

        return back()->with(
            'success',
            'Email log deleted.'
        );
    }

    /**
     * Delete old email logs.
     */
    public function deleteOld(
        Request $request
    ) {
        $this->authorizeEmailAdministration();

        $request->validate([
            'days' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $count = EmailLog::where(
            'sent_at',
            '<',
            now()->subDays(
                $request->days
            )
        )->delete();

        return back()->with(
            'success',
            number_format($count)
            . ' old emails deleted.'
        );
    }

/**
 * Delete a completed/stopped email campaign.
 */
public function destroyCampaign(
    EmailCampaign $campaign
) {
    $this->authorizeEmailAdministration();

    /*
    |--------------------------------------------------------------------------
    | Never delete a campaign while jobs may still be running
    |--------------------------------------------------------------------------
    */

    $hasActiveRecipients = $campaign->recipients()
        ->whereIn('status', [
            'queued',
            'processing',
        ])
        ->exists();

    if (
        in_array(
            $campaign->status,
            ['queued', 'sending'],
            true
        ) ||
        $hasActiveRecipients
    ) {
        return back()->with(
            'error',
            'This campaign cannot be deleted while emails are still queued or processing.'
        );
    }

    $campaignId = $campaign->id;
    $batchNumber = $campaign->batch_number;

    /*
    |--------------------------------------------------------------------------
    | Delete campaign
    |--------------------------------------------------------------------------
    |
    | email_campaign_recipients are removed automatically by the database
    | because their campaign foreign key uses cascadeOnDelete().
    |
    */

    $campaign->delete();

    return redirect()
        ->route('admin.emails.campaigns')
        ->with(
            'success',
            'Campaign #'
            . number_format($campaignId)
            . ($batchNumber
                ? ' (Batch ' . number_format($batchNumber) . ')'
                : '')
            . ' was deleted successfully.'
        );
}

}