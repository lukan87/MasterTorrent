```blade
@extends('layouts.app')

@section('content')

<div class="my-5 rules-page">

    <div class="card glass shadow-lg border-0 rules-card">

        {{-- HEADER --}}
        <div class="card-header text-white text-center rules-header">

            <h2 class="mb-0">
                {{ config('app.name') }}
            </h2>

            <p class="small fst-italic mt-1">
                Private Tracker Rules • Read Carefully • Seed Generously
            </p>

        </div>


        {{-- BODY --}}
        <div class="card-body rules-body">

            <div class="accordion rules-accordion" id="rulesAccordion">


                {{-- =========================================================
                     1. GENERAL RULES
                     ========================================================= --}}
                <div class="accordion-item bg-dark text-light border-secondary">

                    <h2 class="accordion-header">

                        <button class="accordion-button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#generalRules"
                                aria-expanded="true"
                                aria-controls="generalRules">

                            📜 1. General Rules

                        </button>

                    </h2>

                    <div id="generalRules"
                         class="accordion-collapse collapse show"
                         data-bs-parent="#rulesAccordion">

                        <div class="accordion-body">

                            <ul>

                                <li>
                                    Respect staff – their decisions are final.
                                </li>

                                <li>
                                    Multiple accounts are strictly forbidden.
                                </li>

                                <li>
                                    Impersonating staff members is prohibited.
                                </li>

                                <li>
                                    Redistributing torrents to other trackers is prohibited.
                                </li>

                                <li>
                                    Selling accounts or invites is forbidden.
                                </li>

                                <li>
                                    Access to {{ config('app.name') }} is a privilege, not a right.
                                </li>

                                <li>
                                    Racist, discriminatory or offensive behavior is strictly prohibited.
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>


                {{-- =========================================================
                     2. DOWNLOADING & SEEDING
                     ========================================================= --}}
                <div class="accordion-item bg-dark text-light border-secondary">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#seedingRules"
                                aria-expanded="false"
                                aria-controls="seedingRules">

                            ⬇ 2. Downloading & Seeding

                        </button>

                    </h2>

                    <div id="seedingRules"
                         class="accordion-collapse collapse"
                         data-bs-parent="#rulesAccordion">

                        <div class="accordion-body">

                            <ul>

                                <li>
                                    Keep torrents seeding after download.
                                </li>

                                <li>
                                    Minimum required ratio:
                                    <strong>1.0</strong>.
                                </li>

                                <li>
                                    Hit & Run over 20 → download restriction.
                                </li>

                                <li>
                                    Freeleech torrents still require seeding.
                                </li>

                                <li>
                                    VIP class is exempt from Hit & Run rules.
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>


                {{-- =========================================================
                     3. FORUM & PRIVATE MESSAGES
                     ========================================================= --}}
                <div class="accordion-item bg-dark text-light border-secondary">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#forumRules"
                                aria-expanded="false"
                                aria-controls="forumRules">

                            💬 3. Forum & Private Messages

                        </button>

                    </h2>

                    <div id="forumRules"
                         class="accordion-collapse collapse"
                         data-bs-parent="#rulesAccordion">

                        <div class="accordion-body">

                            <ul>

                                <li>
                                    No spam or aggressive behavior.
                                </li>

                                <li>
                                    Use edit instead of multi-posting.
                                </li>

                                <li>
                                    Advertising other trackers is prohibited.
                                </li>

                                <li>
                                    Comments must be constructive.
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>


                {{-- =========================================================
                     4. USER CLASSES
                     ========================================================= --}}
                <div class="accordion-item bg-black text-light border-warning">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#userClasses"
                                aria-expanded="false"
                                aria-controls="userClasses">

                            🏆 4. User Classes

                        </button>

                    </h2>

                    <div id="userClasses"
                         class="accordion-collapse collapse"
                         data-bs-parent="#rulesAccordion">

                        <div class="accordion-body">

                            <p>
                                User classes represent contribution level and trust within the tracker.
                            </p>


                            {{-- USER CLASSES --}}
                            <ul class="list-unstyled">

                                <li>
                                    <span style="color: SlateGrey;">
                                        <strong>User</strong>
                                    </span>
                                    – Default class.
                                </li>

                                <li>
                                    <span style="color: cyan;">
                                        <strong>Elite User</strong>
                                    </span>
                                    – Stable member with solid ratio and activity.
                                    Can create requests and send invites.
                                </li>

                                <li>
                                    <span style="color: orange;">
                                        <strong>Uploader</strong>
                                    </span>
                                    – Active content contributor.
                                </li>

                                <li>
                                    <span style="color: green;">
                                        <strong>VIP</strong>
                                    </span>
                                    – Exempt from Hit & Run rules.
                                </li>

                                <li>
                                    <span style="color: gold;">
                                        <strong>Special User</strong>
                                    </span>
                                    – High contribution member.
                                </li>

                                <li>
                                    <span style="color: yellow;">
                                        <strong>Moderator</strong>
                                    </span>
                                    – Enforces rules.
                                </li>

                                <li>
                                    <span style="color: red;">
                                        <strong>Admin</strong>
                                    </span>
                                    – Full administrative authority.
                                </li>

                                <li>
                                    <span style="color: DarkCyan;">
                                        <strong>Owner</strong>
                                    </span>
                                    – Final authority.
                                </li>

                                <li>
                                    <span style="color: BurlyWood;">
                                        <strong>Web Developer</strong>
                                    </span>
                                    – Platform architect.
                                </li>

                            </ul>


                            <hr>


                            {{-- AUTOMATIC PROMOTION --}}
                            <h6 class="text-warning">
                                ⬆ Automatic Promotion: User → Elite User
                            </h6>

                            <p class="text-danger fw-bold">
                                Promotion is fully automatic. No requests. No exceptions.
                            </p>

                            <ul>

                                <li>
                                    Account must be at least 5 months old.
                                </li>

                                <li>
                                    Minimum 500GB upload and 250GB download.
                                </li>

                                <li>
                                    Minimum overall ratio of 1.1.
                                </li>

                                <li>
                                    Consistent seeding activity.
                                </li>

                                <li>
                                    No active Hit & Run violations.
                                </li>

                                <li>
                                    No rule violations or warnings.
                                </li>

                                <li>
                                    At least 50 forum posts.
                                </li>

                                <li>
                                    At least 50 torrent comments.
                                </li>

                                <li>
                                    At least 50 torrent likes.
                                </li>

                                <li>
                                    Hit and Run count to be 0.
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>


            </div>

        </div>


        {{-- FOOTER --}}
        <div class="card-footer rules-footer">

            <small class="fst-italic">
                {{ config('app.name') }} • Seed More Than You Take • Quality Over Quantity
            </small>

        </div>

    </div>

</div>


<style>

/* =========================================================
   FILEIPLAY — RULES PAGE
   English only
   Dark navy glass + teal forum style
   ========================================================= */

.rules-page {
    margin-top: 28px;
    margin-bottom: 28px;
}


/* =========================================================
   CARD
   ========================================================= */

.rules-card {

    overflow: hidden;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .96),
        rgba(15, 23, 42, .88)
    );

    border: 1px solid var(--ui-border) !important;

    border-radius: .85rem !important;

    box-shadow: 0 18px 45px rgba(0, 0, 0, .32);

    backdrop-filter: blur(14px);
}


/* =========================================================
   HEADER
   ========================================================= */

.rules-header {

    padding: 20px;

    text-align: center;

    background: rgba(45, 212, 191, .045);

    border-bottom: 1px solid var(--ui-border);
}


.rules-header h2 {

    color: #fff;

    font-size: 18px;

    font-weight: 700;

    margin: 0;
}


.rules-header p {

    color: rgba(255, 255, 255, .48);

    font-size: 12px;

    margin-bottom: 0;
}


/* =========================================================
   BODY
   ========================================================= */

.rules-body {
    padding: 18px;
}


/* =========================================================
   ACCORDION
   ========================================================= */

.rules-accordion .accordion-item {

    margin-bottom: 8px;

    overflow: hidden;

    background: rgba(15, 23, 42, .72) !important;

    border: 1px solid var(--ui-border) !important;

    border-radius: .65rem !important;
}


.rules-accordion .accordion-item:last-child {
    margin-bottom: 0;
}


.rules-accordion .accordion-header {
    margin: 0;
}


.rules-accordion .accordion-button {

    color: rgba(255, 255, 255, .82) !important;

    background: rgba(22, 32, 51, .78) !important;

    border: 0 !important;

    box-shadow: none !important;

    font-size: 14px;

    font-weight: 700;

    padding: 12px 14px;
}


.rules-accordion .accordion-button:hover {

    color: #fff !important;

    background: rgba(45, 212, 191, .055) !important;
}


.rules-accordion .accordion-button:not(.collapsed) {

    color: var(--ui-accent) !important;

    background: rgba(45, 212, 191, .065) !important;

    box-shadow: inset 3px 0 0 var(--ui-accent) !important;
}


.rules-accordion .accordion-button::after {

    filter: invert(1);

    opacity: .55;
}


.rules-accordion .accordion-button:not(.collapsed)::after {
    opacity: .85;
}


/* =========================================================
   ACCORDION CONTENT
   ========================================================= */

.rules-accordion .accordion-body {

    color: rgba(255, 255, 255, .67);

    background: rgba(9, 16, 29, .48);

    border-top: 1px solid rgba(255, 255, 255, .045);

    padding: 14px 16px;

    font-size: 13px;

    line-height: 1.65;
}


.rules-accordion ul {

    margin-bottom: 0;

    padding-left: 19px;
}


.rules-accordion li {
    margin-bottom: 6px;
}


.rules-accordion li:last-child {
    margin-bottom: 0;
}


.rules-accordion strong {
    color: rgba(255, 255, 255, .88);
}


.rules-accordion hr {

    border-color: var(--ui-border);

    opacity: 1;

    margin: 16px 0;
}


.rules-accordion h6 {

    font-size: 13px;

    font-weight: 700;
}


/* =========================================================
   SPECIAL TEXT
   ========================================================= */

.rules-accordion .text-warning {
    color: #fcd34d !important;
}


.rules-accordion .text-danger {
    color: #fca5a5 !important;
}


/* =========================================================
   FOOTER
   ========================================================= */

.rules-footer {

    padding: 11px 16px;

    color: rgba(255, 255, 255, .42);

    background: rgba(9, 16, 29, .55);

    border-top: 1px solid var(--ui-border);

    text-align: center;
}


.rules-footer small {
    font-size: 11px;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .rules-page {
        margin-top: 18px;
        margin-bottom: 18px;
    }


    .rules-header {
        padding: 16px 12px;
    }


    .rules-header h2 {
        font-size: 16px;
    }


    .rules-body {
        padding: 12px;
    }


    .rules-accordion .accordion-button {

        font-size: 13px;

        padding: 11px 12px;
    }


    .rules-accordion .accordion-body {

        padding: 12px;

        font-size: 13px;

        line-height: 1.6;
    }


    .rules-footer {
        padding: 10px 12px;
    }

}

</style>

@endsection
```