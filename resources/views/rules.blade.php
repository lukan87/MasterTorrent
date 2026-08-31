@extends('layouts.app')

@section('content')

<div class="my-5">
    <div class="card glass shadow-lg border-0">

        <div class="card-header bg-gradient-primary text-white text-center">
            <h2 class="mb-0">{{ config('app.name') }}</h2>
            <p class="small fst-italic mt-1">
                Private Tracker Rules • Read Carefully • Seed Generously
            </p>
        </div>

        <div class="card-body">

            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ro">
                        🇷🇴 RO
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#en">
                        🇬🇧 EN
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-4">

                {{-- ===================== ROMANIAN TAB ===================== --}}
                <div class="tab-pane fade show active" id="ro">

                    <div class="accordion" id="rulesAccordionRo">

                        {{-- 1 General --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#roGeneral"
                                        aria-expanded="true">
                                    📜 1. Reguli Generale
                                </button>
                            </h2>
                            <div id="roGeneral"
                                 class="accordion-collapse collapse show"
                                 data-bs-parent="#rulesAccordionRo">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Respectați staff-ul – deciziile lor sunt finale.</li>
                                        <li>Conturile multiple sunt strict interzise.</li>
                                        <li>Imitarea staff-ului prin username este interzisă.</li>
                                        <li>Redistribuirea torrentelor pe alte trackere este interzisă.</li>
                                        <li>Vânzarea conturilor sau invitațiilor este interzisă.</li>
                                        <li>Accesul pe {{ config('app.name') }} este un privilegiu, nu un drept.</li>
                                        <li>Comportamentul rasist, discriminatoriu sau ofensator este strict interzis.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 2 Seeding --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#roSeeding">
                                    ⬇ 2. Download & Seeding
                                </button>
                            </h2>
                            <div id="roSeeding"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionRo">
                                <div class="accordion-body">
                                    <ul>
                                        <li>După descărcare, mențineți torrentul la seed pentru un minim de 12 ore in 7 zile.</li>
                                        <li>Rația minimă obligatorie: <strong>1.0</strong>, asta daca nu aveți timpul minim de seed.</li>
                                        <li>Hit & Run peste 20 → restricționare download.</li>
                                        <li>Freeleech nu înseamnă free seed – regulile se aplică normal.</li>
                                        <li>Clasa VIP este exceptată de la regulile Hit & Run.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 3 Forum --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#roForum">
                                    💬 3. Forum & Mesaje Private
                                </button>
                            </h2>
                            <div id="roForum"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionRo">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Spam-ul și comportamentul agresiv sunt sancționate.</li>
                                        <li>Folosiți edit în loc de multi-post.</li>
                                        <li>Este interzisă promovarea altor trackere.</li>
                                        <li>Comentariile trebuie să fie constructive.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 4 Classes --}}
                        <div class="accordion-item bg-black text-light border-warning">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-black text-warning"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#roClasses">
                                    🏆 4. Clase Utilizatori
                                </button>
                            </h2>
                            <div id="roClasses"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionRo">
                                <div class="accordion-body">

                                    <p>Clasele reflectă contribuția și nivelul de încredere câștigat pe tracker.</p>

                                    <ul class="list-unstyled">
                                        <li><span style="color: SlateGrey;"><strong>User</strong></span> – Clasa de bază.</li>
                                        <li><span style="color: cyan;"><strong>Elite User</strong></span> – Utilizator stabil, cu activitate și rație bună. Pot crea request-uri și trimite invitații.</li>
                                        <li><span style="color: orange;"><strong>Uploader</strong></span> – Contributor activ de conținut.</li>
                                        <li><span style="color: green;"><strong>VIP</strong></span> – Exceptat de la Hit & Run. Acces la online users</li>
                                        <li><span style="color: gold;"><strong>Special User</strong></span> – Membru cu contribuție ridicată.</li>
                                        <li><span style="color: yellow;"><strong>Moderator</strong></span> – Aplică regulile.</li>
                                        <li><span style="color: red;"><strong>Admin</strong></span> – Control administrativ complet.</li>
                                        <li><span style="color: DarkCyan;"><strong>Owner</strong></span> – Autoritate supremă.</li>
                                        <li><span style="color: BurlyWood;"><strong>Web Developer</strong></span> – Dezvoltatorul platformei.</li>
                                    </ul>

                                    <hr>

                                    <h6 class="text-warning">⬆ Promovare automată: User → Elite User</h6>

                                    <p class="text-danger fw-bold">
                                        Promovarea este complet automată. Nu se acceptă cereri sau excepții.
                                    </p>

                                    <ul>
                                        <li>Contul trebuie să fie mai vechi de 5 luni.</li>
                                        <li>Minim 500GB upload și 250GB download.</li>
                                        <li>Rație generală minim 1.1.</li>
                                        <li>Activitate constantă de seeding.</li>
                                        <li>Fără Hit & Run active.</li>
                                        <li>Fără avertismente sau sancțiuni.</li>
                                        <li>Minim 50 postări pe forum</li>
                                        <li>Minim 50 comentarii la torrente</li>
                                        <li>Minim 50 aprecieri la torrente</li>
                                        <li>0 Hit & Run.</li>
                                        
                                    </ul>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ===================== ENGLISH TAB ===================== --}}
                <div class="tab-pane fade" id="en">

                    <div class="accordion" id="rulesAccordionEn">

                        {{-- 1 General --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#enGeneral"
                                        aria-expanded="true">
                                    📜 1. General Rules
                                </button>
                            </h2>
                            <div id="enGeneral"
                                 class="accordion-collapse collapse show"
                                 data-bs-parent="#rulesAccordionEn">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Respect staff – their decisions are final.</li>
                                        <li>Multiple accounts are strictly forbidden.</li>
                                        <li>Impersonating staff members is prohibited.</li>
                                        <li>Redistributing torrents to other trackers is prohibited.</li>
                                        <li>Selling accounts or invites is forbidden.</li>
                                        <li>Access to {{ config('app.name') }} is a privilege, not a right.</li>
                                        <li>Racist, discriminatory or offensive behavior is strictly prohibited.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 2 Seeding --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#enSeeding">
                                    ⬇ 2. Downloading & Seeding
                                </button>
                            </h2>
                            <div id="enSeeding"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionEn">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Keep torrents seeding after download.</li>
                                        <li>Minimum required ratio: <strong>1.0</strong>.</li>
                                        <li>Hit & Run over 20 → download restriction.</li>
                                        <li>Freeleech torrents still require seeding.</li>
                                        <li>VIP class is exempt from Hit & Run rules.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 3 Forum --}}
                        <div class="accordion-item bg-dark text-light border-secondary">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-light"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#enForum">
                                    💬 3. Forum & Private Messages
                                </button>
                            </h2>
                            <div id="enForum"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionEn">
                                <div class="accordion-body">
                                    <ul>
                                        <li>No spam or aggressive behavior.</li>
                                        <li>Use edit instead of multi-posting.</li>
                                        <li>Advertising other trackers is prohibited.</li>
                                        <li>Comments must be constructive.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 4 Classes --}}
                        <div class="accordion-item bg-black text-light border-warning">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-black text-warning"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#enClasses">
                                    🏆 4. User Classes
                                </button>
                            </h2>
                            <div id="enClasses"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#rulesAccordionEn">
                                <div class="accordion-body">

                                    <p>User classes represent contribution level and trust within the tracker.</p>

                                    <ul class="list-unstyled">
                                        <li><span style="color: SlateGrey;"><strong>User</strong></span> – Default class.</li>
                                        <li><span style="color: cyan;"><strong>Elite User</strong></span> – Stable member with solid ratio and activity. Can create requests and send invites.</li>
                                        <li><span style="color: orange;"><strong>Uploader</strong></span> – Active content contributor.</li>
                                        <li><span style="color: green;"><strong>VIP</strong></span> – Exempt from Hit & Run rules.</li>
                                        <li><span style="color: gold;"><strong>Special User</strong></span> – High contribution member.</li>
                                        <li><span style="color: yellow;"><strong>Moderator</strong></span> – Enforces rules.</li>
                                        <li><span style="color: red;"><strong>Admin</strong></span> – Full administrative authority.</li>
                                        <li><span style="color: DarkCyan;"><strong>Owner</strong></span> – Final authority.</li>
                                        <li><span style="color: BurlyWood;"><strong>Web Developer</strong></span> – Platform architect.</li>
                                    </ul>

                                    <hr>

                                    <h6 class="text-warning">⬆ Automatic Promotion: User → Elite User</h6>

                                    <p class="text-danger fw-bold">
                                        Promotion is fully automatic. No requests. No exceptions.
                                    </p>

                                    <ul>
                                        <li>Account must be at least 5 months old.</li>
                                        <li>Minimum 500GB upload and 250GB download.</li>
                                        <li>Minimum overall ratio of 1.1.</li>
                                        <li>Consistent seeding activity.</li>
                                        <li>No active Hit & Run violations.</li>
                                        <li>No rule violations or warnings.</li>
                                        <li>At least 50 forum posts.</li>
                                        <li>At least 50 torrent comments.</li>
                                        <li>At least 50 torrent likes.</li>
                                        <li>Hit and Run count to be 0.</li>

                                    </ul>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-center bg-secondary">
            <small class="fst-italic">
                {{ config('app.name') }} • Seed More Than You Take • Quality Over Quantity
            </small>
        </div>

    </div>
</div>

@endsection