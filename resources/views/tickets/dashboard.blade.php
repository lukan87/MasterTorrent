@extends('layouts.app')

@section('content')

<div class="container-fluid mt-5">

<h2 class="mb-4">

<i class="bi bi-speedometer2"></i>
Ticket Dashboard

</h2>


<div class="row g-3 mb-4">

<div class="col-md-3">

<div class="card bg-dark text-center p-3">

<h3>{{ $stats['open'] }}</h3>

Open

</div>

</div>

<div class="col-md-3">

<div class="card bg-dark text-center p-3">

<h3>{{ $stats['waiting_staff'] }}</h3>

Waiting Staff

</div>

</div>

<div class="col-md-3">

<div class="card bg-dark text-center p-3">

<h3>{{ $stats['waiting_user'] }}</h3>

Waiting User

</div>

</div>

<div class="col-md-3">

<div class="card bg-dark text-center p-3">

<h3>{{ $stats['resolved'] }}</h3>

Resolved

</div>

</div>

</div>


<div class="card bg-dark">

<div class="card-header">

Recent Tickets

</div>

<div class="card-body">

<table class="table table-dark table-hover">

<tr>

<th>ID</th>
<th>Title</th>
<th>Status</th>
<th>Priority</th>

</tr>

@foreach($recentTickets as $ticket)

<tr>

<td>#{{ $ticket->id }}</td>

<td>

<a href="{{ route('tickets.show', [
    'id' => $ticket->id,
    'slug' => $ticket->slug
]) }}">

{{ $ticket->title }}

</a>

</td>

<td>{{ $ticket->status }}</td>

<td>{{ $ticket->priority }}</td>

</tr>

@endforeach

</table>

</div>

</div>

</div>

@endsection