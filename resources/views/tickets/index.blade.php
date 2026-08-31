@extends('layouts.app')

@section('content')

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

<h2 class="m-0">
<i class="bi bi-life-preserver"></i> Support Tickets
</h2>

<a href="{{ route('tickets.create') }}" class="btn btn-primary">
<i class="bi bi-plus"></i> Create Ticket
</a>

</div>


@if(Auth::user()->user_class > 5)

<div class="mb-3 d-flex gap-2 flex-wrap">

<a href="{{ route('tickets.index') }}" class="btn btn-outline-light btn-sm">
All
</a>

<a href="{{ route('tickets.unassigned') }}" class="btn btn-outline-warning btn-sm">
Unassigned
</a>

<a href="{{ route('tickets.my') }}" class="btn btn-outline-info btn-sm">
My Tickets
</a>

<a href="{{ route('tickets.dashboard') }}" class="btn btn-outline-light btn-sm">
Dashboard
</a>

</div>

@endif



@if(Auth::user()->user_class > 5)

<div class="torrent-search card border-0 mb-4">

<div class="card-body">

<form method="GET" action="{{ route('tickets.index') }}">

<div class="row g-4 align-items-end">

<div class="col-xl-6">

<label class="form-label">Search Ticket</label>

<div class="premium-search-combined">

<div class="search-input-wrapper">

<i class="bi bi-search search-icon"></i>

<input type="text"
name="keyword"
class="form-control premium-input-combined"
placeholder="Title or keyword..."
value="{{ request('keyword') }}">

</div>

<div class="search-divider"></div>

<input type="text"
name="ticket_id"
class="form-control premium-input-combined"
placeholder="Ticket ID"
value="{{ request('ticket_id') }}"
style="max-width:140px">

<div class="search-divider"></div>

<input type="text"
name="user"
class="form-control premium-input-combined"
placeholder="Username"
value="{{ request('user') }}"
style="max-width:160px">

</div>

</div>


<div class="col-xl-6">

<label class="form-label">Filters</label>

<div class="premium-search-combined">

<div class="premium-inline-dropdown">

<input type="hidden" name="status" value="{{ request('status') }}">

<button type="button" class="inline-dropdown-trigger">

<i class="bi bi-activity me-2 search-icon"></i>

<span>{{ request('status') ?? 'All Status' }}</span>

<i class="bi bi-chevron-down ms-2 toggle-arrow"></i>

</button>

<div class="inline-dropdown-menu">

<div class="inline-option" data-value="">All</div>
<div class="inline-option" data-value="Open">Open</div>
<div class="inline-option" data-value="Waiting Staff">Waiting Staff</div>
<div class="inline-option" data-value="Waiting User">Waiting User</div>
<div class="inline-option" data-value="Resolved">Resolved</div>
<div class="inline-option" data-value="Closed">Closed</div>

</div>

</div>

<div class="search-divider"></div>


<div class="premium-inline-dropdown">

<input type="hidden" name="priority" value="{{ request('priority') }}">

<button type="button" class="inline-dropdown-trigger">

<i class="bi bi-exclamation-triangle me-2 search-icon"></i>

<span>{{ request('priority') ?? 'Priority' }}</span>

<i class="bi bi-chevron-down ms-2 toggle-arrow"></i>

</button>

<div class="inline-dropdown-menu">

<div class="inline-option" data-value="">All</div>
<div class="inline-option" data-value="Low">Low</div>
<div class="inline-option" data-value="Medium">Medium</div>
<div class="inline-option" data-value="High">High</div>
<div class="inline-option" data-value="Critical">Critical</div>

</div>

</div>

<div class="search-divider"></div>


<div class="premium-inline-dropdown">

<input type="hidden" name="category" value="{{ request('category') }}">

<button type="button" class="inline-dropdown-trigger">

<i class="bi bi-collection me-2 search-icon"></i>

<span>

@php
$cat = $categories->firstWhere('id', request('category'));
@endphp

{{ $cat->name ?? 'Category' }}

</span>

<i class="bi bi-chevron-down ms-2 toggle-arrow"></i>

</button>

<div class="inline-dropdown-menu">

<div class="inline-option" data-value="">All</div>

@foreach($categories as $category)

<div class="inline-option" data-value="{{ $category->id }}">
{{ $category->name }}
</div>

@endforeach

</div>

</div>

<div class="search-divider"></div>

<button type="submit" class="categories-trigger">

<i class="bi bi-search me-1"></i>

Search

</button>

</div>

</div>

</div>

</form>

</div>

</div>

@endif



<div class="tickets-list">

@forelse($tickets as $ticket)

@php
$rowClass = '';

if($ticket->priority == 'Critical'){
$rowClass = 'ticket-critical';
}elseif(!$ticket->claimed_by){
$rowClass = 'ticket-unassigned';
}elseif($ticket->status == 'Waiting Staff'){
$rowClass = 'ticket-waiting-staff blink-ticket';
}elseif($ticket->status == 'Waiting User'){
$rowClass = 'ticket-waiting-user';
}elseif($ticket->status == 'Resolved'){
$rowClass = 'ticket-resolved';
}
@endphp


<div class="ticket-row {{ $rowClass }}">

<div class="row align-items-center">

<div class="col-xl-4 col-lg-5">

<a href="{{ route('tickets.show',['id'=>$ticket->id,'slug'=>$ticket->slug]) }}"
class="ticket-title">

#{{ $ticket->id }} — {{ $ticket->title }}

@if($ticket->responses->count())

<span class="badge bg-dark ms-2">

{{ $ticket->responses->count() }}

</span>

@endif

</a>

</div>


<div class="col-xl-2 col-md-4 mt-2 mt-lg-0">

<span class="badge"
style="background: {{ $ticket->category->color }}">

<i class="{{ $ticket->category->icon }}"></i>

{{ $ticket->category->name }}

</span>

</div>


<div class="col-xl-1 col-md-2 mt-2 mt-lg-0">

@if($ticket->priority == 'Critical')

<span class="badge bg-danger">Critical</span>

@elseif($ticket->priority == 'High')

<span class="badge bg-warning text-dark">High</span>

@elseif($ticket->priority == 'Medium')

<span class="badge bg-info">Medium</span>

@else

<span class="badge bg-secondary">Low</span>

@endif

</div>


<div class="col-xl-2 col-md-3 mt-2 mt-lg-0">

<span class="badge bg-info">

{{ $ticket->status }}

</span>

</div>


<div class="col-xl-2 col-md-3 mt-2 mt-lg-0">

<a href="{{ route('profile.show',$ticket->user->id) }}" class="ticket-user">

{{ $ticket->user->name }}

</a>

</div>


<div class="col-xl-1 text-end mt-2 mt-lg-0">

@if($ticket->last_replied_at)

<span class="text-muted small">

{{ $ticket->last_replied_at->diffForHumans() }}

</span>

@endif

</div>

</div>

</div>


@empty

<div class="text-center text-muted py-5">

<i class="bi bi-life-preserver fs-1 d-block mb-3 opacity-50"></i>

No tickets found

</div>

@endforelse

</div>


<div class="mt-4">

{{ $tickets->links() }}

</div>

</div>



<style>

/* ticket list */

.tickets-list{
display:flex;
flex-direction:column;
gap:12px;
}

/* ticket card */

.ticket-row{
background:rgba(30,30,30,.85);
border-radius:12px;
padding:16px 18px;
border:1px solid rgba(255,255,255,.05);
transition:.2s ease;
}

.ticket-row:hover{
background:rgba(40,40,40,.9);
}

/* title */

.ticket-title{
color:#fff;
font-weight:600;
text-decoration:none;
}

.ticket-title:hover{
color:#7c8cff;
}

/* user */

.ticket-user{
color:#9ecbff;
text-decoration:none;
}

.ticket-user:hover{
color:#fff;
}

/* ticket states */

.ticket-critical{border-left:4px solid #ff4c4c;}
.ticket-unassigned{border-left:4px solid #ffc107;}
.ticket-waiting-staff{border-left:4px solid #0dcaf0;}
.ticket-waiting-user{border-left:4px solid #6c757d;}
.ticket-resolved{border-left:4px solid #198754;}

/* blink */

@keyframes ticketBlink{
0%{opacity:1}
50%{opacity:.45}
100%{opacity:1}
}

.blink-ticket{
animation:ticketBlink 1.2s infinite;
}

/* premium search */

.search-icon{color:#888;margin-right:8px;}

.premium-input-combined{
background:transparent;
border:none;
color:#fff;
}

.search-divider{
width:1px;
height:26px;
background:rgba(255,255,255,.12);
margin:0 12px;
}

/* mobile */

@media(max-width:992px){

.ticket-row .row{
row-gap:10px;
}

}

/*Searchbar*/

/* ===============================
   PREMIUM SEARCH CONTAINER
=============================== */

.torrent-search {
    background: linear-gradient(145deg,#1b1b1b8b,#242424);
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}

/* ===============================
   COMBINED SEARCH BAR
=============================== */

.premium-search-combined {
    display:flex;
    align-items:center;
    background:linear-gradient(145deg,#111,#1c1c1c);
    border:1px solid rgba(255,255,255,.12);
    border-radius:14px;
    padding:6px 12px;
    transition:.25s ease;
}

.premium-search-combined:focus-within {
    border-color:#7c8cff;
    box-shadow:0 0 0 3px rgba(124,140,255,.2);
}

/* ===============================
   SEARCH INPUT
=============================== */

.search-input-wrapper {
    display:flex;
    align-items:center;
    flex:1;
}

.search-icon {
    color:#888;
    margin-right:8px;
}

.premium-input-combined {
    background:transparent;
    border:none;
    color:#fff;
}

.premium-input-combined::placeholder{
    color:#aaa;
}

.premium-input-combined:focus{
    outline:none;
}

/* ===============================
   DIVIDER
=============================== */

.search-divider {
    width:1px;
    height:26px;
    background:rgba(255,255,255,.12);
    margin:0 12px;
}

/* ===============================
   DROPDOWNS
=============================== */

.premium-inline-dropdown {
    position:relative;
    display:flex;
    align-items:center;
}

.inline-dropdown-trigger {
    background:transparent;
    border:none;
    color:#ccc;
    display:flex;
    align-items:center;
    font-weight:500;
    cursor:pointer;
    padding:4px 6px;
}

.inline-dropdown-trigger:hover {
    color:#fff;
}

/* dropdown menu */

.inline-dropdown-menu {
    position:absolute;
    top:calc(100% + 6px);
    left:0;
    min-width:200px;
    background:#1c1c1c;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 20px 40px rgba(0,0,0,.6);
    display:none;
    max-height:280px;
    overflow-y:auto;
    z-index:1000;
}

.inline-option {
    padding:10px 14px;
    cursor:pointer;
    color:#ccc;
    transition:.2s ease;
}

.inline-option:hover {
    background:rgba(124,140,255,.15);
    color:#7c8cff;
}

.premium-inline-dropdown.active .inline-dropdown-menu {
    display:block;
}

/* ===============================
   SEARCH BUTTON
=============================== */

.categories-trigger {
    background:transparent;
    border:none;
    color:#ccc;
    display:flex;
    align-items:center;
    font-weight:600;
    transition:.2s ease;
}

.categories-trigger:hover {
    color:#7c8cff;
}

/* arrow animation */

.toggle-arrow {
    transition:transform .25s ease;
}

.premium-inline-dropdown.active .toggle-arrow {
    transform:rotate(180deg);
}


/* Toggle filters */

.search-toggle{
display:flex;
align-items:center;
cursor:pointer;
margin:0 6px;
}

.search-toggle input{
display:none;
}

.search-toggle span{
padding:4px 10px;
border-radius:8px;
font-size:13px;
color:#aaa;
transition:.2s ease;
border:1px solid transparent;
}

.search-toggle input:checked + span{
background:rgba(124,140,255,.15);
color:#7c8cff;
border-color:rgba(124,140,255,.4);
}

.search-toggle span:hover{
color:#fff;
}
</style>

<script>

    document.querySelectorAll('.premium-inline-dropdown').forEach(drop => {

    const trigger = drop.querySelector('.inline-dropdown-trigger');
    const hiddenInput = drop.querySelector('input');
    const label = trigger.querySelector('span');

    trigger.addEventListener('click', () => {

        document.querySelectorAll('.premium-inline-dropdown')
            .forEach(d => d !== drop && d.classList.remove('active'));

        drop.classList.toggle('active');

    });

    drop.querySelectorAll('.inline-option').forEach(option => {

        option.addEventListener('click', () => {

            hiddenInput.value = option.dataset.value;
            label.textContent = option.textContent;

            drop.classList.remove('active');

        });

    });

});

document.addEventListener('click', e => {

    if (!e.target.closest('.premium-inline-dropdown')) {

        document.querySelectorAll('.premium-inline-dropdown')
            .forEach(d => d.classList.remove('active'));

    }

});
</script>

@endsection