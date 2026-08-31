@extends('layouts.app')

@section('content')

<style>

.contact-panel{
    max-width:1500px;
    margin:auto;
}

/* Glass card */

.contact-card{

    background: rgba(10,10,10,0.90);
    border:1px solid rgba(0,150,255,0.15);
    border-radius:18px;

    backdrop-filter: blur(18px);

    padding:1.5rem;
    margin-bottom:1rem;

    transition:.25s;

    box-shadow:
        0 15px 45px rgba(0,0,0,0.9),
        inset 0 0 10px rgba(0,191,255,0.05);

}

.contact-card:hover{

    transform:translateY(-3px);
    border-color:rgba(0,191,255,0.35);

    box-shadow:
        0 25px 60px rgba(0,0,0,1),
        0 0 20px rgba(0,191,255,0.25);

}

/* Header */

.contact-title{

    font-family:'Orbitron', sans-serif;
    color:#00bfff;
    letter-spacing:3px;
    margin-bottom:2rem;

}

/* Status */

.badge-open{

    background:#ff3b3b;
    padding:6px 12px;
    border-radius:8px;
    font-size:0.75rem;
    font-weight:600;

}

.badge-answered{

    background:#00c97f;
    padding:6px 12px;
    border-radius:8px;
    font-size:0.75rem;
    font-weight:600;

}

/* Button */

.open-btn{

    padding:6px 16px;
    border-radius:20px;
    border:none;
    width: 100%;

    background:linear-gradient(90deg,#003366,#0c5b75);

    color:white;
    font-size:0.8rem;
    text-decoration:none;

    transition:0.25s;

}

.open-btn:hover{

    transform:translateY(-2px);
    box-shadow:0 0 15px rgba(0,191,255,0.6);

}

/* Labels */

.contact-label{

    font-size:0.75rem;
    text-transform:uppercase;
    letter-spacing:1px;
    color:#777;

}

/* Meta */

.contact-meta{

    font-size:0.85rem;
    color:#aaa;

}

/* Unread indicator */

.unread-dot{

    width:8px;
    height:8px;
    background:#00bfff;
    border-radius:50%;
    display:inline-block;
    margin-right:6px;

}

</style>


<div class="container-fluid glass contact-panel mt-5">

<h4 class="contact-title mt-3">
📨 Contact Staff Requests
</h4>

@foreach($contacts as $c)

<div class="contact-card">

<div class="row align-items-center gy-3">

<!-- ID -->

<div class="col-6 col-md-1">

<div class="contact-label">Ticket</div>

<strong>#{{ $c->id }}</strong>

</div>


<!-- EMAIL -->

<div class="col-12 col-md-3">

<div class="contact-label">Email</div>

<div>{{ $c->email }}</div>

</div>


<!-- SUBJECT -->

<div class="col-12 col-md-2">

<div class="contact-label">Subject</div>

<strong><a href="{{ route('contactstaff.show',$c->id) }}">{{ $c->subject }}</a></strong>

</div>


<!-- CREATED -->

<div class="col-6 col-md-1">

<div class="contact-label">Created</div>

<div class="contact-meta">

{{ $c->created_at->diffForHumans() }}

</div>

</div>


<!-- IP -->

<div class="col-6 col-md-3">

<div class="contact-label">IP</div>

<div class="contact-meta">

{{ $c->ip ?? '-' }}

</div>

</div>


<!-- STATUS -->

<div class="col-6 col-md-2 text-end">

@if($c->resolved)

<span class="badge-answered">
✔ Resolved
</span>

@else

<span class="badge-open">
● Open
</span>

@endif

</div>


</div>

</div>

@endforeach

</div>

@endsection