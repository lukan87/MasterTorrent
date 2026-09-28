@if(session('success'))
    <div class="support-notice" role="status"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="support-notice support-notice-error" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="support-notice support-notice-error" role="alert">
        <strong>Please check the following:</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
