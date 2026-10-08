@extends('layouts.admin')

@section('admin-content')
@include('admin.partials.page-header', ['eyebrow' => 'COMMUNITY EMAIL', 'title' => 'Compose Email', 'subtitle' => 'Choose an audience and write your next community update.'])

    <div class="container-fluid py-4">

        {{-- EMAIL ADMIN NAVIGATION --}}
        <div class="card glass shadow-sm border-0 mb-4">
            <div class="card-body p-2">
                <div class="d-flex flex-wrap gap-2">

                    <a href="{{ route('admin.emails.campaigns') }}"
                        class="btn {{ request()->routeIs('admin.emails.campaigns*') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-send-check me-1"></i>
                        Campaigns
                    </a>

                    <a href="{{ route('admin.emails.create') }}"
                        class="btn {{ request()->routeIs('admin.emails.create') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-pencil-square me-1"></i>
                        Compose Email
                    </a>

                    <a href="{{ route('admin.emails.index') }}"
                        class="btn {{ request()->routeIs('admin.emails.index') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-clock-history me-1"></i>
                        Email History
                    </a>

                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <div class="card shadow-sm border-0">

                    {{-- HEADER --}}
                    <div class="card-header bg-transparent border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                            <div class="d-flex align-items-center">

                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-3"
                                    style="width: 48px; height: 48px;">
                                    <i class="bi bi-envelope-paper-fill text-primary fs-4"></i>
                                </div>

                                <div>
                                    <h4 class="mb-1">
                                        Send Email
                                    </h4>

                                    <div class="text-muted small">
                                        Compose and send an email to selected FileIplay users.
                                    </div>
                                </div>

                            </div>

                            <div class="text-muted small">
                                <i class="bi bi-envelope-check me-1"></i>
                                From:
                                <strong>
                                    {{ config('mail.from.address') }}
                                </strong>
                            </div>

                        </div>
                    </div>

                    <div class="card-body p-4">

                        {{-- VALIDATION ERRORS --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">

                                <div class="fw-semibold mb-2">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Please fix the following:
                                </div>

                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>

                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.emails.send') }}" id="emailForm">

                            @csrf

                            {{-- =====================================================
                             EMAIL CONTENT
                        ====================================================== --}}

                            <div class="mb-4">

                                <h5 class="mb-1">
                                    <i class="bi bi-pencil-square me-1 text-primary"></i>
                                    Email Content
                                </h5>

                                <div class="text-muted small">
                                    Choose a template or write a custom email.
                                </div>

                            </div>

                            {{-- TEMPLATE --}}
                            <div class="mb-4">

                                <label for="templateSelect" class="form-label fw-semibold">
                                    <i class="bi bi-layout-text-window-reverse me-1 text-muted"></i>
                                    Template
                                </label>

                                <select id="templateSelect" name="template_id" class="form-select">

                                    <option value="">
                                        Custom Email
                                    </option>

                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" data-subject="{{ $template->subject }}"
                                            data-body="{{ $template->body }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach

                                </select>

                                <div class="form-text">
                                    Selecting a template will automatically fill the subject and message.
                                </div>

                            </div>

                            {{-- SUBJECT --}}
                            <div class="mb-4">

                                <label for="subject" class="form-label fw-semibold">
                                    <i class="bi bi-type me-1 text-muted"></i>
                                    Subject
                                </label>

                                <input type="text" name="subject" id="subject" class="form-control form-control-lg"
                                    value="{{ old('subject') }}" maxlength="255" placeholder="Enter email subject..."
                                    required>

                            </div>

                            {{-- BODY --}}
                            <div class="mb-4">

                                <label for="body" class="form-label fw-semibold">
                                    <i class="bi bi-card-text me-1 text-muted"></i>
                                    Message
                                </label>

                                <textarea name="body" id="body" rows="10" maxlength="100000" class="form-control"
                                    placeholder="Write your email here..." required>{{ old('body') }}</textarea>

                                <div class="d-flex align-items-center flex-wrap gap-2 mt-2">

                                    <span class="text-muted small">
                                        Available variables:
                                    </span>

                                    <button type="button" class="btn btn-sm btn-outline-secondary template-variable"
                                        data-variable="{name}">
                                        {name}
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-secondary template-variable"
                                        data-variable="{email}">
                                        {email}
                                    </button>

                                </div>

                            </div>

                            <hr class="my-4">

                            {{-- =====================================================
                             RECIPIENTS
                        ====================================================== --}}

                            <div class="mb-4">

                                <h5 class="mb-1">
                                    <i class="bi bi-people-fill me-1 text-primary"></i>
                                    Recipients
                                </h5>

                                <div class="text-muted small">
                                    Choose who should receive this email and how many users
                                    should be included in this campaign.
                                </div>

                            </div>

                            <div class="row g-4">

                                {{-- AUDIENCE --}}
                                <div class="col-lg-6">

                                    <label for="targetSelect" class="form-label fw-semibold">
                                        <i class="bi bi-bullseye me-1 text-muted"></i>
                                        Audience
                                    </label>

                                    <div class="alert alert-info py-2 px-3 small mb-3">
                                        <i class="bi bi-info-circle me-1"></i>

                                        <strong>Subscribed</strong> sends only to users
                                        who are subscribed.

                                        Other audiences use the selected account criteria
                                        and may include users who are not subscribed.
                                    </div>

                                    <select name="target" id="targetSelect" class="form-select" required>

                                        <option value="subscribed" @selected(old('target', 'subscribed') === 'subscribed')>
                                            Subscribed Users
                                        </option>

                                        <option value="all" @selected(old('target') === 'all')>
                                            All Users
                                        </option>

                                        <option value="user_class" @selected(old('target') === 'user_class')>
                                            Specific User Classes
                                        </option>

                                        <option value="active" @selected(old('target') === 'active')>
                                            Active Users — Last 30 Days
                                        </option>

                                        <option value="inactive" @selected(old('target') === 'inactive')>
                                            Inactive Users — 60+ Days
                                        </option>

                                        <option value="seeders" @selected(old('target') === 'seeders')>
                                            Current Seeders
                                        </option>

                                        <option value="leechers" @selected(old('target') === 'leechers')>
                                            Current Leechers
                                        </option>

                                        <option value="hit_and_run" @selected(old('target') === 'hit_and_run')>
                                            Users with Hit & Runs
                                        </option>

                                        <option value="donors" @selected(old('target') === 'donors')>
                                            Donors
                                        </option>

                                        <option value="warned" @selected(old('target') === 'warned')>
                                            Warned Users
                                        </option>

                                        <option value="disabled" @selected(old('target') === 'disabled')>
                                            Disabled Users
                                        </option>

                                    </select>

                                    <div id="audienceDescription" class="form-text mt-2"></div>

                                </div>

                                {{-- USER CLASS --}}
                                <div class="col-lg-6">

                                    <div id="userClassCard" class="border rounded p-3 h-100">

                                        <div class="d-flex align-items-center justify-content-between mb-2">

                                            <label for="userClassSelect" class="form-label fw-semibold mb-0">
                                                <i class="bi bi-person-badge me-1 text-muted"></i>
                                                User Class
                                            </label>

                                            <span id="classRequiredBadge" class="badge bg-warning text-dark d-none">
                                                Required
                                            </span>

                                        </div>

                                        <select name="user_class[]" id="userClassSelect" class="form-select" multiple
                                            size="7">

                                            @foreach ($classes as $value => $label)
                                                <option value="{{ $value }}" @selected(in_array((string) $value, array_map('strval', (array) old('user_class', [])), true))>
                                                    {{ $label }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <div id="classHelp" class="form-text mt-2">
                                            Optional — select classes to narrow the selected audience.
                                        </div>

                                        <div class="d-flex gap-2 mt-3">

                                            <button type="button" id="selectAllClasses"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-check-all me-1"></i>
                                                Select All
                                            </button>

                                            <button type="button" id="clearClasses"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-x-lg me-1"></i>
                                                Clear
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- =====================================================
                             SEND LIMIT
                        ====================================================== --}}

                            <div class="row g-4 mt-1">

                                <div class="col-lg-6">

                                    <div class="border rounded p-3 h-100">

                                        <label for="sendLimit" class="form-label fw-semibold">
                                            <i class="bi bi-speedometer2 me-1 text-muted"></i>
                                            Send Limit
                                        </label>

                                        <select name="send_limit" id="sendLimit" class="form-select" required>

                                            <option value="500" @selected(old('send_limit', '500') === '500')>
                                                500 users
                                            </option>

                                            <option value="1000" @selected(old('send_limit') === '1000')>
                                                1,000 users
                                            </option>

                                            <option value="1500" @selected(old('send_limit') === '1500')>
                                                1,500 users
                                            </option>

                                            <option value="2000" @selected(old('send_limit') === '2000')>
                                                2,000 users
                                            </option>

                                            <option value="all" @selected(old('send_limit') === 'all')>
                                                All matching users
                                            </option>

                                        </select>

                                        <div class="form-text mt-2">
                                            Choose the maximum number of matching users
                                            to include in this campaign.
                                        </div>

                                    </div>

                                </div>

                                <div class="col-lg-6">

                                    <div class="border rounded p-3 h-100">

                                        <div class="text-muted small mb-2">
                                            This Campaign Will Send To
                                        </div>

                                        <div class="d-flex align-items-center">

                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-3"
                                                style="width: 44px; height: 44px;">
                                                <i class="bi bi-send-fill text-primary fs-5"></i>
                                            </div>

                                            <div>

                                                <div>
                                                    <span id="actualSendCount" class="fs-3 fw-bold" aria-live="polite">
                                                        ...
                                                    </span>

                                                    <span class="text-muted ms-1">
                                                        users
                                                    </span>
                                                </div>

                                                <div id="sendLimitStatus" class="small text-muted">
                                                    Calculating...
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- =====================================================
                             RECIPIENT SUMMARY
                        ====================================================== --}}

                            <div class="card bg-body-tertiary border-0 mt-4 mb-4">

                                <div class="card-body">

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                                        <div>

                                            <div class="text-muted small mb-1">
                                                Matching Recipients
                                            </div>

                                            <div class="d-flex align-items-center">

                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-3"
                                                    style="width: 44px; height: 44px;">
                                                    <i class="bi bi-people-fill text-primary fs-5"></i>
                                                </div>

                                                <div>

                                                    <div>
                                                        <span id="userCount" class="fs-3 fw-bold" aria-live="polite">
                                                            ...
                                                        </span>

                                                        <span class="text-muted ms-1">
                                                            users
                                                        </span>
                                                    </div>

                                                    <div id="countStatus" class="small text-muted">
                                                        Calculating...
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="text-lg-end">

                                            <div class="text-muted small mb-1">
                                                Class Filter
                                            </div>

                                            <div id="selectedClasses" class="small fw-semibold">
                                                All Classes
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- =====================================================
                             LARGE SEND WARNING
                        ====================================================== --}}

                            <div id="confirmBox" class="alert alert-warning d-none mb-4">

                                <div class="d-flex">

                                    <div class="me-3">
                                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                    </div>

                                    <div class="flex-grow-1">

                                        <h6 class="alert-heading fw-bold">
                                            Large Send Detected
                                        </h6>

                                        <p class="mb-3">
                                            You are about to queue this email for

                                            <strong id="confirmCount">
                                                0
                                            </strong>

                                            users.
                                        </p>

                                        <label for="confirmInput" class="form-label">
                                            Type
                                            <strong>SEND</strong>
                                            to confirm:
                                        </label>

                                        <input type="text" id="confirmInput" class="form-control"
                                            placeholder="Type SEND" autocomplete="off">

                                    </div>

                                </div>

                            </div>

                            <input type="hidden" name="confirm" id="confirmHidden" value="{{ old('confirm') }}">

                            <hr class="my-4">

                            {{-- =====================================================
                             ACTIONS
                        ====================================================== --}}

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                                <div class="text-muted small">

                                    <i class="bi bi-shield-check me-1"></i>

                                    Batch size:
                                    <strong>500 – All matching users</strong>

                                </div>

                                <button type="submit" id="sendButton" class="btn btn-primary btn-lg px-4" disabled>

                                    <i class="bi bi-send-fill me-2"></i>

                                    <span id="sendButtonText">
                                        Send Email
                                    </span>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>

    {{-- =============================================================
     JAVASCRIPT
============================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const emailForm =
                document.getElementById('emailForm');

            const templateSelect =
                document.getElementById('templateSelect');

            const subject =
                document.getElementById('subject');

            const body =
                document.getElementById('body');

            const targetSelect =
                document.getElementById('targetSelect');

            const userClassSelect =
                document.getElementById('userClassSelect');

            const userClassCard =
                document.getElementById('userClassCard');

            const classRequiredBadge =
                document.getElementById('classRequiredBadge');

            const classHelp =
                document.getElementById('classHelp');

            const selectedClasses =
                document.getElementById('selectedClasses');

            const selectAllClasses =
                document.getElementById('selectAllClasses');

            const clearClasses =
                document.getElementById('clearClasses');

            const audienceDescription =
                document.getElementById('audienceDescription');

            const userCount =
                document.getElementById('userCount');

            const countStatus =
                document.getElementById('countStatus');

            const sendLimit =
                document.getElementById('sendLimit');

            const actualSendCount =
                document.getElementById('actualSendCount');

            const sendLimitStatus =
                document.getElementById('sendLimitStatus');

            const confirmBox =
                document.getElementById('confirmBox');

            const confirmCount =
                document.getElementById('confirmCount');

            const confirmInput =
                document.getElementById('confirmInput');

            const confirmHidden =
                document.getElementById('confirmHidden');

            const sendButton =
                document.getElementById('sendButton');

            let currentCount = 0;
            let currentSendCount = 0;


            /*
            |--------------------------------------------------------------------------
            | Audience descriptions
            |--------------------------------------------------------------------------
            */

            const descriptions = {

                subscribed: 'Users who are subscribed to receive FileIplay email.',

                all: 'All valid non-junk FileIplay users with an email address.',

                user_class: 'Send only to users belonging to the selected user classes.',

                active: 'Users who have been active during the last 30 days.',

                inactive: 'Users who have not been active for at least 60 days.',

                seeders: 'Users currently seeding at least one torrent.',

                leechers: 'Users currently downloading at least one torrent.',

                hit_and_run: 'Users who currently have one or more Hit & Runs.',

                donors: 'Users currently marked as donors.',

                warned: 'Users currently marked as warned.',

                disabled: 'User accounts that are currently disabled.'

            };


            /*
            |--------------------------------------------------------------------------
            | Audience description
            |--------------------------------------------------------------------------
            */

            function updateAudienceDescription() {

                const target =
                    targetSelect.value;

                audienceDescription.textContent =
                    descriptions[target] || '';

            }


            /*
            |--------------------------------------------------------------------------
            | User class UI
            |--------------------------------------------------------------------------
            */

            function updateClassUI() {

                const target =
                    targetSelect.value;

                if (target === 'user_class') {

                    classRequiredBadge.classList.remove('d-none');

                    classHelp.innerHTML =
                        '<strong>Select at least one class.</strong> ' +
                        'Only users in the selected classes will receive the email.';

                    userClassCard.classList.add('border-warning');

                } else {

                    classRequiredBadge.classList.add('d-none');

                    classHelp.textContent =
                        'Optional — select classes to narrow the selected audience.';

                    userClassCard.classList.remove('border-warning');

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Selected class summary
            |--------------------------------------------------------------------------
            */

            function updateSelectedClassSummary() {

                const selected =
                    Array.from(userClassSelect.selectedOptions);

                if (selected.length === 0) {

                    if (targetSelect.value === 'user_class') {

                        selectedClasses.innerHTML =
                            '<span class="text-warning">' +
                            'No classes selected' +
                            '</span>';

                    } else {

                        selectedClasses.textContent =
                            'All Classes';

                    }

                    return;
                }

                const names =
                    selected.map(option => option.text.trim());

                selectedClasses.textContent =
                    names.join(', ');

            }


            /*
            |--------------------------------------------------------------------------
            | Template selection
            |--------------------------------------------------------------------------
            */

            templateSelect.addEventListener(
                'change',
                function() {

                    const option =
                        this.options[this.selectedIndex];

                    subject.value =
                        option.dataset.subject || '';

                    body.value =
                        option.dataset.body || '';

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Insert template variable
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('.template-variable')
                .forEach(function(button) {

                    button.addEventListener(
                        'click',
                        function() {

                            const variable =
                                this.dataset.variable;

                            const start =
                                body.selectionStart;

                            const end =
                                body.selectionEnd;

                            const value =
                                body.value;

                            body.value =
                                value.substring(0, start) +
                                variable +
                                value.substring(end);

                            body.focus();

                            const cursor =
                                start + variable.length;

                            body.setSelectionRange(
                                cursor,
                                cursor
                            );

                        }
                    );

                });


            /*
            |--------------------------------------------------------------------------
            | Confirmation UI
            |--------------------------------------------------------------------------
            */

            function updateConfirmation() {

                if (currentSendCount > 1000) {

                    confirmBox.classList.remove('d-none');

                    confirmCount.textContent =
                        currentSendCount.toLocaleString();

                } else {

                    confirmBox.classList.add('d-none');

                    confirmInput.value = '';
                    confirmHidden.value = '';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Send limit
            |--------------------------------------------------------------------------
            */

            function updateSendLimit() {

                if (currentCount <= 0) {

                    currentSendCount = 0;

                    actualSendCount.textContent = '0';

                    sendLimitStatus.textContent =
                        'No matching recipients.';

                    updateConfirmation();

                    return;
                }

                const selectedLimit =
                    sendLimit.value;

                if (selectedLimit === 'all') {

                    currentSendCount =
                        currentCount;

                    actualSendCount.textContent =
                        currentSendCount.toLocaleString();

                    sendLimitStatus.innerHTML =
                        '<span class="text-warning fw-semibold">' +
                        '<i class="bi bi-exclamation-triangle-fill me-1"></i>' +
                        'All matching users selected' +
                        '</span>';

                } else {

                    const limit =
                        Number(selectedLimit);

                    currentSendCount =
                        Math.min(
                            currentCount,
                            limit
                        );

                    actualSendCount.textContent =
                        currentSendCount.toLocaleString();

                    if (currentCount > limit) {

                        sendLimitStatus.innerHTML =
                            '<span class="text-primary fw-semibold">' +
                            currentSendCount.toLocaleString() +
                            '</span> of ' +
                            currentCount.toLocaleString() +
                            ' matching users will be included.';

                    } else {

                        sendLimitStatus.innerHTML =
                            '<span class="text-success fw-semibold">' +
                            '<i class="bi bi-check-circle-fill me-1"></i>' +
                            'All matching users fit within this limit' +
                            '</span>';

                    }

                }

                updateConfirmation();

            }


            /*
            |--------------------------------------------------------------------------
            | Select all classes
            |--------------------------------------------------------------------------
            */

            selectAllClasses.addEventListener(
                'click',
                function() {

                    Array.from(userClassSelect.options)
                        .forEach(function(option) {
                            option.selected = true;
                        });

                    updateSelectedClassSummary();
                    updateUserCount();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Clear classes
            |--------------------------------------------------------------------------
            */

            clearClasses.addEventListener(
                'click',
                function() {

                    Array.from(userClassSelect.options)
                        .forEach(function(option) {
                            option.selected = false;
                        });

                    updateSelectedClassSummary();
                    updateUserCount();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Recipient count
            |--------------------------------------------------------------------------
            */

            async function updateUserCount() {

                const selectedClassOptions =
                    Array.from(
                        userClassSelect.selectedOptions
                    );

                const classes =
                    selectedClassOptions.map(
                        option => option.value
                    );

                const target =
                    targetSelect.value;


                /*
                 * Specific User Classes requires at least one class.
                 */
                if (
                    target === 'user_class' &&
                    classes.length === 0
                ) {

                    currentCount = 0;

                    userCount.textContent = '0';

                    countStatus.innerHTML =
                        '<span class="text-warning fw-semibold">' +
                        'Select at least one user class' +
                        '</span>';

                    sendButton.disabled = true;

                    updateSendLimit();

                    return;
                }


                userCount.textContent = '...';

                countStatus.textContent =
                    'Calculating...';

                actualSendCount.textContent = '...';

                sendLimitStatus.textContent =
                    'Calculating...';

                sendButton.disabled = true;


                try {

                    const response = await fetch(
                        "{{ route('admin.emails.count') }}", {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },

                            body: JSON.stringify({
                                target: target,
                                user_class: classes
                            })
                        }
                    );


                    if (!response.ok) {

                        throw new Error(
                            'Unable to calculate recipients.'
                        );

                    }


                    const data =
                        await response.json();


                    currentCount =
                        Number(data.count || 0);


                    userCount.textContent =
                        currentCount.toLocaleString();


                    if (currentCount === 0) {

                        countStatus.innerHTML =
                            '<span class="text-warning fw-semibold">' +
                            'No matching users' +
                            '</span>';

                        sendButton.disabled = true;

                    } else {

                        countStatus.innerHTML =
                            '<span class="text-success fw-semibold">' +
                            '<i class="bi bi-check-circle-fill me-1"></i>' +
                            'Audience ready' +
                            '</span>';

                        sendButton.disabled = false;

                    }


                    updateSendLimit();

                } catch (error) {

                    currentCount = 0;
                    currentSendCount = 0;

                    userCount.textContent =
                        'Error';

                    actualSendCount.textContent =
                        '0';

                    countStatus.innerHTML =
                        '<span class="text-danger">' +
                        'Could not calculate recipients' +
                        '</span>';

                    sendLimitStatus.textContent =
                        'Unable to calculate campaign size.';

                    sendButton.disabled = true;

                    updateConfirmation();

                    console.error(error);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Send-limit change
            |--------------------------------------------------------------------------
            */

            sendLimit.addEventListener(
                'change',
                function() {

                    updateSendLimit();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Confirmation input
            |--------------------------------------------------------------------------
            */

            confirmInput.addEventListener(
                'input',
                function() {

                    confirmHidden.value =
                        this.value
                        .trim()
                        .toUpperCase();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Audience change
            |--------------------------------------------------------------------------
            */

            targetSelect.addEventListener(
                'change',
                function() {

                    updateAudienceDescription();
                    updateClassUI();
                    updateSelectedClassSummary();
                    updateUserCount();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | User class change
            |--------------------------------------------------------------------------
            */

            userClassSelect.addEventListener(
                'change',
                function() {

                    updateSelectedClassSummary();
                    updateUserCount();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Final safety checks
            |--------------------------------------------------------------------------
            */

            emailForm.addEventListener(
                'submit',
                function(event) {

                    const target =
                        targetSelect.value;

                    const classes =
                        Array.from(
                            userClassSelect.selectedOptions
                        );


                    /*
                     * Specific classes require a selection.
                     */
                    if (
                        target === 'user_class' &&
                        classes.length === 0
                    ) {

                        event.preventDefault();

                        alert(
                            'Please select at least one user class.'
                        );

                        userClassSelect.focus();

                        return;
                    }


                    /*
                     * No matching recipients.
                     */
                    if (currentCount === 0) {

                        event.preventDefault();

                        alert(
                            'No users match the selected audience.'
                        );

                        return;
                    }


                    /*
                     * No recipients in campaign.
                     */
                    if (currentSendCount === 0) {

                        event.preventDefault();

                        alert(
                            'There are no recipients in this campaign.'
                        );

                        return;
                    }


                    /*
                     * Large-send confirmation.
                     */
                    if (
                        currentSendCount > 1000 &&
                        confirmHidden.value !== 'SEND'
                    ) {

                        event.preventDefault();

                        alert(
                            'Please type SEND to confirm this large email campaign.'
                        );

                        confirmInput.focus();

                        return;
                    }


                    /*
                     * Prevent double submission.
                     */
                    sendButton.disabled = true;

                    sendButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        'Creating Campaign...';

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Initial page load
            |--------------------------------------------------------------------------
            */

            updateAudienceDescription();
            updateClassUI();
            updateSelectedClassSummary();
            updateUserCount();

        });
    </script>

@endsection
