@extends('layouts.app')

@section('content')

<div class="container py-5 donation-page">

    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold donation-title">
            <i class="bi bi-cash-stack"></i> Support {{ config('app.name') }}
        </h1>

        <p class="text-muted small">
            Running a private tracker requires powerful servers and bandwidth.
        </p>
    </div>


    <div class="alert donation-alert text-center mb-5">

        <h4 class="mb-3">
            <i class="bi bi-heart-fill"></i> Help keep the tracker alive
        </h4>

        <p>
            Using <b>{{ config('app.name') }}</b> is completely free,
            but the infrastructure behind it isn't.
        </p>

        <p>
            If you donate, please contact staff to activate your benefits.
        </p>

        <a href="/profile/1067" class="btn btn-outline-light">
            <i class="bi bi-envelope"></i> Contact Staff
        </a>

    </div>


    @php

    $donations = [

        ['amount'=>3,'vip'=>'4 weeks','upload'=>'50 GB','bonus'=>'500','color'=>'tier1'],
        ['amount'=>5,'vip'=>'6 weeks','upload'=>'150 GB','bonus'=>'1500','color'=>'tier2'],
        ['amount'=>7,'vip'=>'2 months','upload'=>'300 GB','bonus'=>'2500','color'=>'tier3'],
        ['amount'=>10,'vip'=>'10 weeks','upload'=>'500 GB','bonus'=>'5000','color'=>'tier4'],
        ['amount'=>25,'vip'=>'3 months','upload'=>'750 GB','bonus'=>'7500','color'=>'tier5'],
        ['amount'=>30,'vip'=>'Unlimited','upload'=>'1 TB','bonus'=>'10000','color'=>'tier6'],

    ];

    $totalDonation = array_sum(array_column($donations,'amount'));

    @endphp



    <div class="row g-4">

        @foreach($donations as $donation)

        <div class="col-lg-4 col-md-6">

            <div class="card donation-card {{ $donation['color'] }}">

                <div class="donation-price">

                    {{ $donation['amount'] }}€

                </div>


                <div class="card-body text-center">

                    <h5 class="tier-title">
                        Donation Tier
                    </h5>


                    <ul class="donation-benefits">

                        <li>
                            <i class="bi bi-star"></i>
                            VIP: {{ $donation['vip'] }}
                        </li>

                        <li>
                            <i class="bi bi-cloud-arrow-up"></i>
                            Upload: {{ $donation['upload'] }}
                        </li>

                        <li>
                            <i class="bi bi-coin"></i>
                            Bonus: {{ $donation['bonus'] }}
                        </li>

                    </ul>


                    <form action="https://www.paypal.com/cgi-bin/webscr" method="POST">

                        <input type="hidden" name="cmd" value="_xclick">
                        <input type="hidden" name="business" value="calapushai@gmail.com">
                        <input type="hidden" name="currency_code" value="EUR">
                        <input type="hidden" name="item_name" value="Donation">
                        <input type="hidden" name="amount" value="{{ $donation['amount'] }}">

                        <button class="btn donate-btn">

                            <i class="bi bi-cash"></i>
                            Donate {{ $donation['amount'] }}€

                        </button>

                    </form>

                </div>

            </div>

        </div>

        @endforeach

    </div>


    {{-- <div class="text-center mt-5 donation-footer">

        <h5>Total Donation Packages</h5>

        <span class="total-value">

            {{ $totalDonation }}€

        </span>

    </div> --}}

</div>



<style>

/* =========================================
   PAGE
========================================= */

.donation-page{
    color:var(--theme-text, #fff);
    max-width:1450px;
}

/* =========================================
   HEADER
========================================= */

.donation-title{

    font-weight:850;

    letter-spacing:-1px;

    font-size:3rem;

    background:
        linear-gradient(
            135deg,
            var(--theme-text, #ffffff),
            var(--theme-blue-text, #bfc8ff)
        );

    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.donation-title i{

    background:
        linear-gradient(
            135deg,
            var(--theme-blue-text, #6ea8ff),
            var(--theme-blue-text, #9b7cff)
        );

    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.donation-page .text-muted{
    color:var(--theme-muted, rgba(255,255,255,.55)) !important;
    font-size:var(--site-font-body, 13px);
}

/* =========================================
   ALERT BOX
========================================= */

.donation-alert{

    background:
        linear-gradient(
            180deg,
            var(--theme-surface-alt, rgba(14,14,17,.92)),
            var(--theme-surface-alt, rgba(8,8,10,.96))
        );

    border:
        1px solid var(--theme-border, rgba(255,255,255,.06));

    border-radius:
        28px;

    padding:
        3rem 2rem;

    backdrop-filter:
        blur(18px);

    box-shadow:
        0 20px 60px var(--theme-shadow, rgba(0,0,0,.4));

    position:relative;

    overflow:hidden;
}

.donation-alert::before{

    content:'';

    position:absolute;

    inset:0;

    background:
        radial-gradient(
            circle at top right,
            var(--theme-blue-soft, rgba(110,168,255,.12)),
            transparent 45%
        );

    pointer-events:none;
}

.donation-alert h4{

    font-weight:800;

    font-size:1.6rem;

    margin-bottom:1rem;
}

.donation-alert h4 i{
    color:var(--theme-red-text, #ff6b81);
}

.donation-alert p{

    color:var(--theme-muted, rgba(255,255,255,.75));

    font-size:1rem;

    line-height:1.7;
}

/* =========================================
   CONTACT BUTTON
========================================= */

.donation-alert .btn{

    border-radius:
        14px;

    padding:
        .8rem 1.2rem;

    font-weight:
        700;

    border:
        1px solid var(--theme-border, rgba(255,255,255,.12));

    transition:
        all .18s ease;
}

.donation-alert .btn:hover{

    transform:
        translateY(-2px);

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.056));
}

/* =========================================
   DONATION CARD
========================================= */

.donation-card{

    position:relative;

    background:
        linear-gradient(
            180deg,
            var(--theme-surface, rgba(16,16,18,.96)),
            var(--theme-surface-alt, rgba(9,9,10,.98))
        );

    border:
        1px solid var(--theme-border, rgba(255,255,255,.05));

    border-radius:
        24px;

    overflow:hidden;

    transition:
        all .28s ease;

    box-shadow:
        0 18px 45px var(--theme-shadow, rgba(0,0,0,.28));

    height:100%;
}

.donation-card:hover{

    transform:
        translateY(-8px);

    box-shadow:
        0 30px 60px var(--theme-shadow, rgba(0,0,0,.45));

    border-color:
        var(--theme-border, rgba(255,255,255,.1));
}

/* =========================================
   PRICE BAR
========================================= */

.donation-price{

    position:relative;

    text-align:center;

    padding:
        1.4rem;

    font-size:
        2rem;

    font-weight:
        850;

    letter-spacing:
        -.5px;

    color:var(--theme-text, #fff);
}

/* glow */
.donation-price::after{

    content:'';

    position:absolute;

    inset:0;

    background:
        linear-gradient(
            to bottom,
            var(--theme-surface-alt, rgba(255,255,255,0.084)),
            transparent
        );

    pointer-events:none;
}

/* =========================================
   BODY
========================================= */

.donation-card .card-body{
    padding:2rem;
}

/* =========================================
   TITLE
========================================= */

.tier-title{

    font-size:
        1.1rem;

    font-weight:
        750;

    margin-bottom:
        1.4rem;

    color:
        var(--theme-text, rgba(255,255,255,.92));
}

/* =========================================
   BENEFITS
========================================= */

.donation-benefits{

    list-style:none;

    padding:0;

    margin:0 0 2rem;
}

.donation-benefits li{

    display:flex;
    align-items:center;

    gap:12px;

    padding:
        .9rem 1rem;

    margin-bottom:
        .75rem;

    border-radius:
        14px;

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.021));

    border:
        1px solid var(--theme-border, rgba(255,255,255,.04));

    font-size:
        var(--site-font-body, 13px);

    color:
        var(--theme-muted, rgba(255,255,255,.82));

    transition:
        .18s ease;
}

.donation-benefits li:hover{

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.035));

    transform:
        translateX(2px);
}

.donation-benefits i{

    font-size:
        1rem;

    opacity:
        .95;
}

/* =========================================
   DONATE BUTTON
========================================= */

.donate-btn{

    width:100%;

    border:none;

    border-radius:
        16px;

    padding:
        1rem;

    font-weight:
        800;

    font-size:
        var(--site-font-body, 13px);

    letter-spacing:
        .2px;

    color:var(--theme-text, #fff);

    transition:
        all .2s ease;
}

/* =========================================
   BUTTON HOVER
========================================= */

.donate-btn:hover{

    transform:
        translateY(-2px);

    filter:
        brightness(1.05);
}

/* =========================================
   TIER COLORS
========================================= */

.tier1 .donation-price{
    background:linear-gradient(135deg,var(--theme-surface, #313740),var(--theme-surface, #242a35));
}

.tier1 .donate-btn{
    background:linear-gradient(135deg,var(--theme-surface, #313740),var(--theme-surface, #242a35));
}

.tier2 .donation-price{
    background:linear-gradient(135deg,var(--theme-blue-soft, #2563eb),var(--theme-blue-soft, #1d4ed8));
}

.tier2 .donate-btn{
    background:linear-gradient(135deg,var(--theme-blue-action, #2563eb),var(--theme-blue-action, #1d4ed8));
}

.tier3 .donation-price{
    background:linear-gradient(135deg,var(--theme-purple-soft, #7c3aed),var(--theme-purple-soft, #6d28d9));
}

.tier3 .donate-btn{
    background:linear-gradient(135deg,var(--theme-purple-action, #7c3aed),var(--theme-purple-action, #6d28d9));
}

.tier4 .donation-price{
    background:linear-gradient(135deg,var(--theme-red-soft, #dc2626),var(--theme-red-soft, #b91c1c));
}

.tier4 .donate-btn{
    background:linear-gradient(135deg,var(--theme-red-action, #dc2626),var(--theme-red-action, #b91c1c));
}

.tier5 .donation-price{
    background:linear-gradient(135deg,var(--theme-amber-soft, #d97706),var(--theme-amber-soft, #b45309));
}

.tier5 .donate-btn{
    background:linear-gradient(135deg,var(--theme-amber-action, #d97706),var(--theme-amber-action, #b45309));
}

.tier6 .donation-price{
    background:linear-gradient(135deg,var(--theme-teal-soft, #059669),var(--theme-teal-soft, #047857));
}

.tier6 .donate-btn{
    background:linear-gradient(135deg,var(--theme-teal-action, #059669),var(--theme-teal-action, #047857));
}

/* =========================================
   FOOTER
========================================= */

.donation-footer{
    opacity:.9;
}

.total-value{

    display:inline-block;

    margin-top:8px;

    font-size:2rem;

    font-weight:800;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .donation-title{
        font-size:2.2rem;
    }

    .donation-alert{
        padding:2rem 1.2rem;
    }

    .donation-card .card-body{
        padding:1.4rem;
    }

    .donation-price{
        font-size:1.7rem;
    }

}

</style>

@endsection