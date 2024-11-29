@extends('layouts.app')

@section('content')

<div class="row">
    <!-- Movies Section -->
    <div class="col-lg-9 col-md-8 col-sm-6">
@include('partials.news')
</div>
<div class="col-lg-3 col-md-4 col-sm-6">
@include('partials.poll')
</div>


<div class="row">
    <!-- Movies Section -->
    <div class="col-md-6">
@include('partials.toptorrents')
</div>

<div class="col-md-6">
@include('partials.onlineusers')
</div>
</div>


@include('partials.stats')



<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><strong>Disclaimer:</strong></h5>
    </div>
    <div class="card-body" style="font-size: 0.85rem;">
        <p class="mb-0">
            Niciunul dintre fișierele indexate pe această platformă nu este găzduit pe serverele noastre. Toate link-urile și conținutul indexat sunt furnizate exclusiv de utilizatorii site-ului, iar administratorii platformei nu își asumă responsabilitatea pentru acțiunile și materialele distribuite de aceștia. Accesul și utilizarea acestui serviciu trebuie să respecte legile și reglementările în vigoare. Orice utilizare a platformei pentru scopuri ilegale este strict interzisă și poate atrage măsuri disciplinare, inclusiv dezactivarea permanentă a accesului. Recomandăm tuturor utilizatorilor să se informeze și să respecte legislația aplicabilă în domeniul drepturilor de autor și distribuției de conținut.
        </p>
    </div>
</div>


@endsection
