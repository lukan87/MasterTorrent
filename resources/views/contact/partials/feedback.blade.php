@if($errors->any())<div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>@endif
@foreach(['success', 'status'] as $key)
    @if(session($key))<div class="alert alert-success" role="status">{{ session($key) }}</div>@endif
@endforeach
