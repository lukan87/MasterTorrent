@php
$isDev = auth()->user()->user_class == \App\Models\UserClass::WEB_DEVELOPER;
@endphp

<div class="glass-card p-3">

{{-- Header --}}

<div class="row fw-bold text-secondary small border-bottom pb-2 mb-2 align-items-center">

@if($isDev)

<div class="col-md-1">User</div>
@endif

<div class="col-md-2">Seedbox</div>
<div class="col-md-3">Address</div>
<div class="col-md-1">Auth</div>

@if($isDev)

<div class="col-md-1">Username</div>
<div class="col-md-2">Password</div>
@endif

<div class="col-md text-end">Actions</div>

</div>

{{-- Rows --}}
@foreach($boxes as $box)

<div class="row align-items-center py-2 border-bottom seedbox-row
@if(isset($highlightOwner) && $box->user_id == auth()->id()) bg-dark-subtle @endif">

@if($isDev)

<div class="col-md-1 text-light small">
{{ $box->user->name }}
</div>
@endif

<div class="col-md-2">

<a href="{{ route('seedboxes.torrents', $box) }}"
class="text-decoration-none fw-semibold text-info">

<i class="bi bi-hdd-network me-1"></i>
{{ $box->name }}

</a>

<span id="status-{{ $box->id }}" class="seedbox-status ms-2">
<i class="bi bi-hourglass-split text-warning" data-bs-toggle="tooltip" title="Checking"></i>
</span>

@if($box->user_id == auth()->id()) <span class="badge bg-info text-dark ms-1">Mine</span>
@endif

</div>

<div class="col-md-3 text-muted small seedbox-address">

<i class="bi bi-link-45deg"></i>
{{ $box->address }}

</div>

<div class="col-md-1">

@if($box->auth_type === 'basic') <span class="badge bg-primary">Basic</span>
@else <span class="badge bg-warning text-dark">Digest</span>
@endif

</div>

@if($isDev)

<div class="col-md-1 small text-light">
<i class="bi bi-person"></i>
{{ $box->username }}
</div>

<div class="col-md-2">

<div class="input-group input-group-sm">

<input
type="password"
id="password-{{ $box->id }}"
value="{{ $box->password }}"
readonly
class="form-control">

<button
class="btn btn-outline-secondary"
type="button"
onclick="togglePassword({{ $box->id }})">

<i id="icon-{{ $box->id }}" class="bi bi-eye"></i>

</button>

</div>

</div>

@endif

<div class="col-md text-end">

<div class="d-flex justify-content-end gap-1">

@if($isDev || $box->user_id == auth()->id())

<a
href="{{ route('seedboxes.edit', $box) }}"
class="btn btn-sm btn-warning">

<i class="bi bi-pencil-square"></i>

</a>

<form
action="{{ route('seedboxes.destroy', $box) }}"
method="POST"
class="d-inline">

@csrf
@method('DELETE')

<button
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this seedbox?')">

<i class="bi bi-trash-fill"></i>

</button>

</form>

@endif

<button
class="btn btn-sm btn-info"
onclick="testSeedbox({{ $box->id }})">

<i class="bi bi-wifi"></i>

</button>

</div>

</div>

</div>

@endforeach

</div>

<script>

/* Password toggle */

function togglePassword(id){

const input=document.getElementById('password-'+id);
const icon=document.getElementById('icon-'+id);

if(!input) return;

if(input.type==='password'){
input.type='text';
icon.classList.replace('bi-eye','bi-eye-slash');
}else{
input.type='password';
icon.classList.replace('bi-eye-slash','bi-eye');
}

}


/* Seedbox connection test */

function testSeedbox(id){

const badge=document.getElementById('status-'+id);

badge.innerHTML='<i class="bi bi-hourglass-split text-warning" data-bs-toggle="tooltip" title="Checking"></i>';

fetch(`/seedboxes/${id}/test`,{
headers:{'Accept':'application/json'}
})
.then(res=>res.json())
.then(data=>{

if(data.success){

badge.innerHTML='<i class="bi bi-wifi text-success" data-bs-toggle="tooltip" title="Online"></i>';

}else{

badge.innerHTML='<i class="bi bi-wifi-off text-danger" data-bs-toggle="tooltip" title="Offline"></i>';

}

initTooltips();

})
.catch(()=>{

badge.innerHTML='<i class="bi bi-wifi-off text-danger" data-bs-toggle="tooltip" title="Offline"></i>';

});

}


/* Auto check on load */

document.addEventListener("DOMContentLoaded",()=>{

document.querySelectorAll(".seedbox-status").forEach(badge=>{

const id=badge.id.replace("status-","");
testSeedbox(id);

});

initTooltips();

});


/* Bootstrap tooltips */

function initTooltips(){

const tooltipTriggerList=[].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

tooltipTriggerList.map(function(el){
return new bootstrap.Tooltip(el);
});

}

</script>

<style>

.seedbox-row{
transition:all .15s ease;
}

.seedbox-row:hover{
background:rgba(255,255,255,0.05);
}

.seedbox-address{
word-break:break-all;
}

.seedbox-status i{
font-size:1rem;
}

</style>
