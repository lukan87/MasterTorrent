@extends('layouts.app')



@section('content')



@php

    $roles = App\Models\UserClass::getClasses();



    $customOrder = [

        App\Models\UserClass::WEB_DEVELOPER,

        App\Models\UserClass::OWNER,

        App\Models\UserClass::ADMIN,

        App\Models\UserClass::MODERATOR,

        App\Models\UserClass::UPLOADER,

    ];



    $remainingRoles = array_diff(array_keys($roles), $customOrder);

    $orderedRoles = array_merge($customOrder, $remainingRoles);



    $roleIcons = [

        App\Models\UserClass::WEB_DEVELOPER => 'bi-code-slash',

        App\Models\UserClass::OWNER         => 'bi-crown-fill',

        App\Models\UserClass::ADMIN         => 'bi-shield-fill-check',

        App\Models\UserClass::MODERATOR     => 'bi-hammer',

        App\Models\UserClass::UPLOADER      => 'bi-cloud-arrow-up-fill',

    ];



    $roleDescriptions = [

        App\Models\UserClass::WEB_DEVELOPER => 'Building, improving and maintaining the platform',

        App\Models\UserClass::OWNER         => 'The founder and architect of the platform',

        App\Models\UserClass::ADMIN         => 'Full administrative control over the tracker',

        App\Models\UserClass::MODERATOR     => 'Keeping the community safe and on track',

        App\Models\UserClass::UPLOADER      => 'Contributors powering the content library',

    ];

@endphp





<div class="container team-page py-4">



    {{-- HERO --}}

    <div class="team-hero">



        <div class="hero-orbs">

            <div class="orb orb-1"></div>

            <div class="orb orb-2"></div>

            <div class="orb orb-3"></div>

        </div>



        <div class="hero-content text-center">



            <div class="hero-badge">

                <i class="bi bi-people-fill"></i>

                {{ config('app.name') }}

            </div>



            <h1 class="hero-title">Our Team</h1>



            <p class="hero-subtitle">

                The dedicated people who keep everything running 24/7

            </p>



            <div class="hero-stats">



                <div class="hero-stat">

                    <span class="stat-number">{{ $staff->count() }}</span>

                    <span class="stat-label">Members</span>

                </div>



                <div class="hero-stat-divider"></div>



                @foreach($customOrder as $classId)



                    @php

                        $count = $staff->where('user_class', $classId)->count();

                    @endphp



                    @if($count > 0)



                        <div class="hero-stat">

                            <span

                                class="stat-number"

                                style="--member-color: {{ App\Models\UserClass::getClassColor($classId) }}; color: var(--member-color)"

                            >

                                {{ $count }}

                            </span>



                            <span class="stat-label">

                                {{ $roles[$classId] ?? 'Role' }}

                            </span>

                        </div>



                        @if(!$loop->last)

                            <div class="hero-stat-divider"></div>

                        @endif



                    @endif



                @endforeach



            </div>



        </div>



    </div>





    {{-- FILTER PILLS --}}

    <div class="team-filters" id="teamFilters">



        <button class="filter-pill active" data-role="all">

            <i class="bi bi-grid-3x3-gap me-1"></i>

            All

        </button>



        @foreach ($orderedRoles as $classId)



            @php

                $roleName = $roles[$classId] ?? null;

                $roleMembers = $staff->where('user_class', $classId);

            @endphp



            @if ($roleName && $roleMembers->isNotEmpty())



                <button

                    class="filter-pill"

                    data-role="{{ $classId }}"

                >

                    <i class="bi {{ $roleIcons[$classId] ?? 'bi-person-fill' }} me-1"></i>



                    {{ $roleName }}



                    <span class="pill-count">

                        {{ $roleMembers->count() }}

                    </span>

                </button>



            @endif



        @endforeach



    </div>





    {{-- ROLE SECTIONS --}}

    @foreach ($orderedRoles as $classId)



        @php

            $roleName = $roles[$classId] ?? null;

            $roleMembers = $staff->where('user_class', $classId);

            $classColor = App\Models\UserClass::getClassColor($classId);

        @endphp





        @if ($roleName && $roleMembers->isNotEmpty())



            <div

                class="role-section"

                data-role-section="{{ $classId }}"

            >



                {{-- ROLE HEADER --}}

                <div

                    class="role-header"

                    style="--role-color: {{ $classColor }}"

                >



                    <div class="role-icon-wrap">

                        <i class="bi {{ $roleIcons[$classId] ?? 'bi-person-fill' }}"></i>

                    </div>



                    <div class="role-info">



                        <h2 class="role-title">

                            {{ $roleName }}s

                        </h2>



                        @if(isset($roleDescriptions[$classId]))

                            <p class="role-desc">

                                {{ $roleDescriptions[$classId] }}

                            </p>

                        @endif



                    </div>



                    <div

                        class="role-count-badge"

                        style="--role-color: {{ $classColor }}"

                    >

                        {{ $roleMembers->count() }}

                        {{ Str::plural('member', $roleMembers->count()) }}

                    </div>



                </div>





                {{-- MEMBERS --}}

                <div class="row g-4">



                    @foreach ($roleMembers as $member)



                        @php

                            $memberColor = App\Models\UserClass::getClassColor($member->user_class);



                            $leaderClasses = [

                                App\Models\UserClass::OWNER,

                                App\Models\UserClass::ADMIN,

                            ];



                            $isLeader = in_array(

                                $member->user_class,

                                $leaderClasses

                            );

                        @endphp





                        <div

                            class="{{ $isLeader ? 'col-md-6 col-xl-4' : 'col-md-6 col-lg-4 col-xl-3' }} member-col"

                            data-role-col="{{ $member->user_class }}"

                        >



                            <div

                                class="team-card {{ $isLeader ? 'team-card--leader' : '' }}"

                                style="--card-color: {{ $memberColor }}"

                            >



                                <div class="card-accent"></div>

                                <div class="card-glow-blob"></div>





                                <div class="card-inner">



                                    {{-- AVATAR --}}

                                    <div class="card-avatar">



                                        <img

                                            src="{{ $member->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"

                                            alt="{{ $member->name }}"

                                            loading="lazy"

                                        >



                                        <div class="avatar-ring"></div>



                                        @if($isLeader)



                                            <div class="avatar-crown">

                                                <i class="bi bi-star-fill"></i>

                                            </div>



                                        @endif



                                    </div>





                                    {{-- USERNAME --}}

                                    <a

                                        href="{{ route('profile.show', [

                                            'id' => $member->id,

                                            'name' => $member->name

                                        ]) }}"

                                        class="card-name"

                                    >

                                        {{ $member->name }}

                                    </a>





                                    {{-- ROLE --}}

                                    <div class="card-role-tag">



                                        <i class="bi {{ $roleIcons[$member->user_class] ?? 'bi-person-fill' }}"></i>



                                        {{ $roleName }}



                                    </div>





                                    {{-- ACTIONS --}}

                                    <div class="card-actions">



                                        <a

                                            href="{{ route('messages.create', [

                                                'receiver_id' => $member->id

                                            ]) }}"

                                            class="card-btn card-btn--primary"

                                        >

                                            <i class="bi bi-chat-dots-fill"></i>

                                            <span>Message</span>

                                        </a>





                                        <a

                                            href="{{ route('profile.show', [

                                                'id' => $member->id,

                                                'name' => $member->name

                                            ]) }}"

                                            class="card-btn card-btn--ghost"

                                        >

                                            <i class="bi bi-person-fill"></i>

                                            <span>Profile</span>

                                        </a>



                                    </div>



                                </div>



                            </div>



                        </div>



                    @endforeach



                </div>



            </div>



        @endif



    @endforeach



</div>





@push('scripts')



<script>



document.addEventListener('DOMContentLoaded', function () {



    /*

    |--------------------------------------------------------------------------

    | Team filters

    |--------------------------------------------------------------------------

    */



    const pills = document.querySelectorAll('.filter-pill');

    const sections = document.querySelectorAll('[data-role-section]');



    pills.forEach(pill => {



        pill.addEventListener('click', function () {



            pills.forEach(p => p.classList.remove('active'));



            this.classList.add('active');



            const role = this.dataset.role;



            if (role === 'all') {



                sections.forEach(section => {

                    section.style.display = '';

                });



            } else {



                sections.forEach(section => {



                    section.style.display =

                        section.dataset.roleSection === role

                            ? ''

                            : 'none';



                });



            }



        });



    });





    /*

    |--------------------------------------------------------------------------

    | Scroll reveal

    |--------------------------------------------------------------------------

    */



    const revealEls = document.querySelectorAll(

        '.team-card, .role-header, .hero-content'

    );



    const observer = new IntersectionObserver((entries) => {



        entries.forEach(entry => {



            if (entry.isIntersecting) {



                entry.target.classList.add('revealed');



                observer.unobserve(entry.target);



            }



        });



    }, {

        threshold: 0.1

    });





    revealEls.forEach(el => observer.observe(el));



});



</script>



@endpush





<style>
/* =========================================================
   FILEIPLAY TEAM PAGE
   Clean forum-style redesign — structure/functionality preserved
   ========================================================= */

.team-page{
    position:relative;
    z-index:1;
    max-width:1320px;
    color:var(--theme-text, #e5e7eb);
}

/* HERO */
.team-hero{
    position:relative;
    overflow:hidden;
    margin-bottom:1.4rem;
    padding:2rem 1.5rem;
    background:linear-gradient(135deg,var(--theme-surface, rgba(14,21,33,.96)),var(--theme-surface, rgba(10,15,27,.9)));
    border:1px solid var(--theme-border, rgba(148,163,184,.16));
    border-radius:.8rem;
    box-shadow:0 14px 34px var(--theme-shadow, rgba(0,0,0,.25));
}

.hero-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.orb{position:absolute;border-radius:50%;filter:blur(65px);opacity:.22}
.orb-1{width:220px;height:220px;top:-130px;left:8%;background:var(--theme-teal-soft, rgba(45,212,191,.28))}
.orb-2{width:180px;height:180px;right:5%;top:-70px;background:var(--theme-teal-soft, rgba(6,182,212,.2))}
.orb-3{width:160px;height:160px;bottom:-110px;left:46%;background:var(--theme-teal-soft, rgba(20,184,166,.2))}

.hero-content{position:relative;z-index:2}

.hero-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    margin-bottom:.65rem;
    padding:.35rem .65rem;
    border:1px solid var(--theme-teal-border, rgba(45,212,191,.22));
    border-radius:.45rem;
    background:var(--theme-teal-soft, rgba(20,184,166,.08));
    color:var(--theme-teal-text, #67e8df);
    font-size:var(--site-font-small, 13px);
    font-weight:800;
    letter-spacing:.7px;
    text-transform:uppercase;
}

.hero-title{
    margin:0 0 .35rem;
    color:var(--theme-text, #f8fafc);
    font-size:clamp(1.75rem,3vw,2.35rem);
    font-weight:800;
    line-height:1.1;
}

.hero-subtitle{
    max-width:620px;
    margin:0 auto 1.35rem;
    color:var(--theme-muted, #94a3b8);
    font-size:var(--site-font-body, 13px);
    line-height:1.5;
}

.hero-stats{
    display:flex;
    align-items:center;
    justify-content:center;
    flex-wrap:wrap;
    gap:1rem;
}

.hero-stat{display:flex;flex-direction:column;align-items:center;gap:.15rem}
.stat-number{color:var(--theme-text, #f8fafc);font-size:1.15rem;font-weight:800;line-height:1}
.stat-label{color:var(--theme-muted, #64748b);font-size:var(--site-font-small, 13px);font-weight:700;text-transform:uppercase;letter-spacing:.65px}
.hero-stat-divider{width:1px;height:28px;background:var(--theme-surface-alt, rgba(148,163,184,0.105))}

/* FILTERS */
.team-filters{
    display:flex;
    align-items:center;
    gap:.45rem;
    margin-bottom:1.4rem;
    padding:.55rem;
    overflow-x:auto;
    scrollbar-width:none;
    background:var(--theme-surface, rgba(10,15,27,.55));
    border:1px solid var(--theme-border, rgba(148,163,184,.13));
    border-radius:.65rem;
}
.team-filters::-webkit-scrollbar{display:none}

.filter-pill{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    flex:0 0 auto;
    padding:.48rem .7rem;
    border:1px solid var(--theme-border, rgba(148,163,184,.15));
    border-radius:.5rem;
    background:var(--theme-surface, rgba(20,27,38,.55));
    color:var(--theme-muted, #94a3b8);
    font-size:var(--site-font-body, 13px);
    font-weight:700;
    white-space:nowrap;
    cursor:pointer;
    transition:.18s ease;
}
.filter-pill:hover{background:var(--theme-surface, rgba(33,42,55,.65));color:var(--theme-text, #e2e8f0);border-color:var(--theme-border, rgba(148,163,184,.25))}
.filter-pill.active{background:var(--theme-teal-soft, rgba(20,184,166,.12));color:var(--theme-teal-text, #67e8df);border-color:var(--theme-teal-border, rgba(45,212,191,.3))}
.pill-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:20px;
    height:20px;
    padding:0 .3rem;
    border-radius:.35rem;
    background:var(--theme-surface-alt, rgba(148,163,184,0.07));
    color:var(--theme-text, #cbd5e1);
    font-size:var(--site-font-small, 13px);
}
.filter-pill.active .pill-count{background:var(--theme-teal-soft, rgba(20,184,166,.16));color:var(--theme-teal-text, #99f6e4)}

/* ROLE SECTION */
.role-section{margin-bottom:2rem}

.role-header{
    display:flex;
    align-items:center;
    gap:.8rem;
    margin-bottom:.9rem;
    padding:.75rem .9rem;
    background:linear-gradient(135deg,var(--theme-surface, rgba(14,21,33,.92)),var(--theme-surface, rgba(10,15,27,.82)));
    border:1px solid var(--theme-border, rgba(148,163,184,.14));
    border-left:3px solid var(--role-color,var(--theme-teal-border, rgba(45,212,191,.5)));
    border-radius:.65rem;
}

.role-icon-wrap{
    display:flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    flex-shrink:0;
    border-radius:.5rem;
    background:var(--theme-surface, rgba(20,27,38,.72));
    border:1px solid var(--theme-border, rgba(148,163,184,.14));
    color:var(--role-color,var(--theme-teal-text, #67e8df));
    font-size:1rem;
}
.role-info{flex:1;min-width:0}
.role-title{margin:0;color:var(--theme-text, #f8fafc);font-size:1rem;font-weight:800;line-height:1.2}
.role-desc{margin:.18rem 0 0;color:var(--theme-muted, #64748b);font-size:var(--site-font-body, 13px);line-height:1.35}
.role-count-badge{
    flex-shrink:0;
    padding:.35rem .55rem;
    border-radius:.4rem;
    background:var(--theme-surface, rgba(20,27,38,.6));
    border:1px solid var(--theme-border, rgba(148,163,184,.14));
    color:var(--role-color,var(--theme-text, #cbd5e1));
    font-size:var(--site-font-small, 13px);
    font-weight:700;
    white-space:nowrap;
}

/* MEMBER CARDS */
.team-card{
    position:relative;
    overflow:hidden;
    height:100%;
    background:linear-gradient(135deg,var(--theme-surface, rgba(14,21,33,.94)),var(--theme-surface, rgba(10,15,27,.86)));
    border:1px solid var(--theme-border, rgba(148,163,184,.14));
    border-radius:.7rem;
    box-shadow:0 8px 22px var(--theme-shadow, rgba(0,0,0,.18));
    transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease;
}
.team-card:hover{
    transform:translateY(-3px);
    border-color:color-mix(in srgb,var(--card-color) 35%,var(--theme-border, rgba(148,163,184,.18)));
    box-shadow:0 12px 28px var(--theme-shadow, rgba(0,0,0,.26));
}
.team-card--leader{border-color:color-mix(in srgb,var(--card-color) 22%,var(--theme-border, rgba(148,163,184,.15)))}
.card-accent{height:2px;background:var(--card-color);opacity:.7}
.card-glow-blob{
    position:absolute;
    width:120px;
    height:120px;
    top:-75px;
    right:-65px;
    border-radius:50%;
    background:var(--card-color);
    opacity:.05;
    filter:blur(30px);
    pointer-events:none;
}
.card-inner{
    position:relative;
    z-index:2;
    display:flex;
    flex-direction:column;
    align-items:center;
    height:100%;
    padding:1.25rem 1rem 1rem;
    text-align:center;
}

/* AVATAR */
.card-avatar{
    position:relative;
    width:76px;
    height:76px;
    margin-bottom:.8rem;
    flex-shrink:0;
}
.card-avatar img{
    position:relative;
    z-index:2;
    width:100%;
    height:100%;
    object-fit:cover;
    border-radius:50%;
    border:2px solid var(--theme-border, rgba(148,163,184,.16));
    box-shadow:0 6px 18px var(--theme-shadow, rgba(0,0,0,.3));
}
.avatar-ring{
    position:absolute;
    inset:-4px;
    border-radius:50%;
    border:1px solid var(--card-color);
    opacity:.28;
}
.avatar-crown{
    position:absolute;
    top:-2px;
    right:-3px;
    z-index:3;
    display:flex;
    align-items:center;
    justify-content:center;
    width:23px;
    height:23px;
    border-radius:50%;
    background:var(--theme-amber-soft, #92400e);
    border:1px solid var(--theme-amber-border, rgba(251,191,36,.45));
    color:var(--theme-amber-text, #fde68a);
    font-size:var(--site-font-small, 13px);
    box-shadow:0 3px 8px var(--theme-shadow, rgba(0,0,0,.3));
}

/* MEMBER DETAILS */
.card-name{
    display:block;
    max-width:100%;
    margin-bottom:.3rem;
    overflow:hidden;
    color:var(--theme-text, #f8fafc);
    font-size:var(--site-font-body, 13px);
    font-weight:800;
    text-decoration:none;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.card-name:hover{color:var(--card-color)}

.card-role-tag{
    display:inline-flex;
    align-items:center;
    gap:.3rem;
    margin-bottom:.85rem;
    padding:.28rem .5rem;
    border-radius:.4rem;
    background:var(--theme-surface, rgba(20,27,38,.62));
    border:1px solid var(--theme-border, rgba(148,163,184,.13));
    color:var(--card-color);
    font-size:var(--site-font-small, 13px);
    font-weight:700;
    letter-spacing:.35px;
    text-transform:uppercase;
}

/* ACTIONS */
.card-actions{
    display:flex;
    gap:.45rem;
    width:100%;
    margin-top:auto;
}
.card-btn{
    flex:1;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:.3rem;
    min-height:34px;
    padding:.4rem .5rem;
    border-radius:.45rem;
    font-size:var(--site-font-small, 13px);
    font-weight:700;
    text-decoration:none;
    transition:.18s ease;
}
.card-btn--primary{
    background:var(--theme-teal-soft, rgba(20,184,166,.12));
    border:1px solid var(--theme-teal-border, rgba(45,212,191,.28));
    color:var(--theme-teal-text, #67e8df);
}
.card-btn--primary:hover{background:var(--theme-teal-soft, rgba(20,184,166,.2));border-color:var(--theme-teal-border, rgba(45,212,191,.45));color:var(--theme-teal-text, #99f6e4)}
.card-btn--ghost{
    background:var(--theme-surface, rgba(20,27,38,.58));
    border:1px solid var(--theme-border, rgba(148,163,184,.14));
    color:var(--theme-muted, #94a3b8);
}
.card-btn--ghost:hover{background:var(--theme-surface, rgba(33,42,55,.68));border-color:var(--theme-border, rgba(148,163,184,.25));color:var(--theme-text, #e2e8f0)}

/* The original JS adds .revealed. Keep content visible even if IntersectionObserver is unavailable. */
.hero-content,.role-header,.team-card{opacity:1;transform:none}

/* RESPONSIVE — doubled @ is required inside Blade */
@@media (max-width:768px){
    .team-page{padding-left:.75rem!important;padding-right:.75rem!important}
    .team-hero{padding:1.5rem 1rem;margin-bottom:1rem;border-radius:.65rem}
    .hero-title{font-size:1.75rem}
    .hero-subtitle{font-size:var(--site-font-body, 13px);margin-bottom:1rem}
    .hero-stats{gap:.7rem}
    .stat-number{font-size:1rem}
    .team-filters{margin-bottom:1rem}
    .role-header{padding:.7rem .75rem;gap:.65rem}
    .role-desc{display:none}
    .role-count-badge{font-size:var(--site-font-small, 13px)}
    .card-inner{padding:1rem .8rem .8rem}
    .card-avatar{width:68px;height:68px}
}

@@media (max-width:480px){
    .hero-stat-divider{display:none}
    .hero-stats{column-gap:1rem;row-gap:.75rem}
    .role-header{align-items:center}
    .role-icon-wrap{width:34px;height:34px}
    .role-title{font-size:var(--site-font-body, 13px)}
    .card-actions{flex-direction:row}
}
</style>



@endsection
