@if(session('success'))
    <div class="alert alert-success my-3" role="status">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger my-3" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger my-3" role="alert">
        <strong>Please check the following:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
