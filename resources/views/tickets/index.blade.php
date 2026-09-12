@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 tickets-page">

<div class="tickets-header">

<h2 class="tickets-title">
<i class="bi bi-life-preserver"></i> Support Tickets
</h2>

<a href="{{ route('tickets.create') }}" class="ticket-create-btn">
<i class="bi bi-plus"></i> Create Ticket
</a>

</div>


@if(Auth::user()->user_class > 5)

<div class="ticket-nav">

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
.tickets-page{max-width:1600px}
.tickets-header{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem}
.tickets-title{margin:0;color:#f1f5f9;font-size:14px;font-weight:700}
.tickets-title i{color:var(--ui-accent,#22d3c5)}
.ticket-create-btn{display:inline-flex;align-items:center;gap:.35rem;min-height:36px;padding:.4rem .7rem;color:#061311;background:var(--ui-accent,#22d3c5);border:1px solid var(--ui-accent,#22d3c5);border-radius:.55rem;font-size:13px;font-weight:600;text-decoration:none;transition:all .18s ease}
.ticket-create-btn:hover{color:#061311;filter:brightness(1.06);transform:translateY(-1px)}
.ticket-nav{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.8rem}
.ticket-nav .btn{min-height:32px;padding:.35rem .6rem;border-radius:.5rem;font-size:12px;font-weight:600}
.ticket-nav .btn-outline-light{color:#cbd5e1;border-color:rgba(148,163,184,.22)}
.ticket-nav .btn-outline-light:hover{color:var(--ui-accent,#22d3c5);background:rgba(34,211,197,.07);border-color:rgba(34,211,197,.3)}
.ticket-nav .btn-outline-warning{color:#fbbf24;border-color:rgba(251,191,36,.25)}
.ticket-nav .btn-outline-info{color:var(--ui-accent,#22d3c5);border-color:rgba(34,211,197,.25)}

.torrent-search{position:relative;overflow:visible;background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84))!important;border:1px solid var(--ui-border,rgba(148,163,184,.16))!important;border-radius:.85rem;box-shadow:0 10px 28px rgba(0,0,0,.22)}
.torrent-search:before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--ui-accent,#22d3c5);border-radius:.85rem 0 0 .85rem}
.torrent-search .card-body{padding:1rem}
.torrent-search .form-label{margin-bottom:.4rem;color:#cbd5e1;font-size:13px;font-weight:600}
.premium-search-combined{display:flex;align-items:center;min-height:42px;padding:.3rem .6rem;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.17);border-radius:.6rem;transition:border-color .18s ease,box-shadow .18s ease}
.premium-search-combined:focus-within{border-color:var(--ui-accent,#22d3c5);box-shadow:0 0 0 2px rgba(34,211,197,.08)}
.search-input-wrapper{display:flex;align-items:center;flex:1;min-width:0}
.search-icon{flex:0 0 auto;color:var(--ui-accent,#22d3c5);margin-right:.45rem;font-size:13px}
.premium-input-combined{min-width:0;min-height:34px;padding:.3rem .2rem;color:#e2e8f0!important;background:transparent!important;border:none!important;outline:none!important;box-shadow:none!important;font-size:13px!important}
.premium-input-combined::placeholder{color:#64748b}
.search-divider{width:1px;height:25px;flex:0 0 auto;margin:0 .5rem;background:rgba(148,163,184,.15)}
.premium-inline-dropdown{position:relative;display:flex;align-items:center}
.inline-dropdown-trigger{display:flex;align-items:center;padding:.3rem .35rem;color:#cbd5e1;background:transparent;border:none;font-size:13px;font-weight:500;cursor:pointer;white-space:nowrap}
.inline-dropdown-trigger:hover{color:var(--ui-accent,#22d3c5)}
.inline-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;z-index:1000;display:none;min-width:190px;max-height:280px;overflow-y:auto;padding:.3rem;background:#0f172a;border:1px solid var(--ui-border,rgba(148,163,184,.16));border-radius:.6rem;box-shadow:0 18px 35px rgba(0,0,0,.5)}
.inline-option{padding:.5rem .65rem;color:#cbd5e1;border-radius:.4rem;font-size:13px;cursor:pointer;transition:all .15s ease}
.inline-option:hover{color:var(--ui-accent,#22d3c5);background:rgba(34,211,197,.08)}
.premium-inline-dropdown.active .inline-dropdown-menu{display:block}
.toggle-arrow{transition:transform .18s ease}
.premium-inline-dropdown.active .toggle-arrow{transform:rotate(180deg)}
.categories-trigger{display:flex;align-items:center;padding:.3rem .45rem;color:var(--ui-accent,#22d3c5);background:transparent;border:none;font-size:13px;font-weight:600}
.categories-trigger:hover{color:#5eead4}
.search-toggle{display:flex;align-items:center;margin:0 .35rem;cursor:pointer}
.search-toggle input{display:none}
.search-toggle span{padding:.25rem .55rem;color:#64748b;border:1px solid transparent;border-radius:.45rem;font-size:12px}
.search-toggle input:checked+span{color:var(--ui-accent,#22d3c5);background:rgba(34,211,197,.08);border-color:rgba(34,211,197,.25)}

.tickets-list{display:flex;flex-direction:column;gap:.6rem}
.ticket-row{position:relative;padding:.8rem .9rem;background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));border:1px solid var(--ui-border,rgba(148,163,184,.16));border-radius:.7rem;box-shadow:0 6px 18px rgba(0,0,0,.16);transition:background .18s ease,border-color .18s ease,transform .18s ease}
.ticket-row:hover{background:linear-gradient(135deg,rgba(25,38,59,.97),rgba(15,23,42,.9));border-color:rgba(34,211,197,.2);transform:translateY(-1px)}
.ticket-title{color:#e2e8f0;font-size:13px;font-weight:600;text-decoration:none}
.ticket-title:hover{color:var(--ui-accent,#22d3c5)}
.ticket-title .badge{font-size:11px;background:rgba(51,65,85,.65)!important;border:1px solid rgba(148,163,184,.12)}
.ticket-user{color:#94a3b8;font-size:13px;text-decoration:none}
.ticket-user:hover{color:var(--ui-accent,#22d3c5)}
.ticket-critical{border-left:3px solid #ef4444}
.ticket-unassigned{border-left:3px solid #f59e0b}
.ticket-waiting-staff{border-left:3px solid var(--ui-accent,#22d3c5)}
.ticket-waiting-user{border-left:3px solid #64748b}
.ticket-resolved{border-left:3px solid #22c55e}
@keyframes ticketBlink{0%,100%{opacity:1}50%{opacity:.72}}
.blink-ticket{animation:ticketBlink 1.5s infinite}
.ticket-row .badge{font-size:11px;font-weight:600;border-radius:.4rem}
.ticket-row .bg-danger{background:rgba(127,29,29,.72)!important;color:#fecaca!important;border:1px solid rgba(248,113,113,.2)}
.ticket-row .bg-warning{background:rgba(120,53,15,.72)!important;color:#fde68a!important;border:1px solid rgba(251,191,36,.2)}
.ticket-row .bg-info{background:rgba(14,116,144,.22)!important;color:#67e8f9!important;border:1px solid rgba(34,211,238,.2)}
.ticket-row .bg-secondary{background:rgba(71,85,105,.42)!important;color:#cbd5e1!important;border:1px solid rgba(148,163,184,.15)}
.ticket-row .small{color:#64748b!important;font-size:12px}
.tickets-list>.text-center{padding:3rem 1rem!important;color:#64748b!important;background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));border:1px solid var(--ui-border,rgba(148,163,184,.16));border-radius:.85rem}
.tickets-list>.text-center i{color:var(--ui-accent,#22d3c5);opacity:.8!important;font-size:36px!important}
.pagination .page-link{min-width:34px;margin:0 2px;padding:.4rem .6rem;color:#cbd5e1;background:rgba(22,32,51,.82);border:1px solid var(--ui-border,rgba(148,163,184,.16));border-radius:.5rem;font-size:13px}
.pagination .page-link:hover{color:var(--ui-accent,#22d3c5);background:rgba(34,211,197,.07);border-color:rgba(34,211,197,.3)}
.pagination .page-item.active .page-link{color:#061311;background:var(--ui-accent,#22d3c5);border-color:var(--ui-accent,#22d3c5)}
.pagination .page-item.disabled .page-link{color:#475569;background:rgba(15,23,42,.55);border-color:rgba(148,163,184,.1)}
@media(max-width:992px){.ticket-row .row{row-gap:.6rem}.premium-search-combined{flex-wrap:wrap;gap:.15rem;padding:.45rem .55rem}.search-divider{margin:0 .3rem}}
@media(max-width:768px){.tickets-page{padding-left:.5rem!important;padding-right:.5rem!important}.ticket-row{padding:.75rem}.search-input-wrapper{width:100%;flex-basis:100%}.premium-inline-dropdown{flex:1 1 auto}.inline-dropdown-trigger{width:100%;justify-content:center;font-size:12px}.search-divider{display:none}.categories-trigger{width:100%;justify-content:center;margin-top:.2rem;padding-top:.45rem;border-top:1px solid rgba(148,163,184,.12)}}
@media(max-width:576px){.tickets-header{align-items:flex-start}.ticket-create-btn{width:100%}.ticket-nav .btn{flex:1 1 auto}}
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