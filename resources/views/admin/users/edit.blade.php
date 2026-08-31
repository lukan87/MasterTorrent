@extends('layouts.app')

@section('content')

<div class="container py-4">

<form action="{{ route('admin.users.update',$user->id) }}" method="POST">
@csrf
@method('PUT')

<h2 class="mb-4">Edit User</h2>

{{-- ACCOUNT INFORMATION --}}
<div class="card shadow-sm border-0 mb-4">
<div class="card-header bg-dark text-white">
Account Information
</div>

<div class="card-body">
<div class="row g-3">

<div class="col-md-4">
<label class="form-label">Name</label>
<input type="text"
       name="name"
       class="form-control elite-input"
       value="{{ old('name',$user->name) }}">
</div>

<div class="col-md-4">
<label class="form-label">Email</label>
<input type="email"
       name="email"
       class="form-control elite-input"
       value="{{ old('email',$user->email) }}">
</div>

<div class="col-md-4">
<label class="form-label">Profile Image URL</label>
<input type="url"
       name="profile_image"
       class="form-control elite-input"
       value="{{ old('profile_image',$user->profile_image) }}">
</div>

<div class="col-md-4">
<label class="form-label">Recovery Code</label>
 <input type="text" class="form-control elite-input" name="recovery_code">
</div>
</div>
</div>
</div>


@if (auth()->id() !== $user->id)

{{-- USER STATUS --}}
<div class="card shadow-sm border-0 mb-4">
<div class="card-header bg-dark text-white">
User Status
</div>

<div class="card-body">
<div class="row g-4">

@foreach (['enabled','downloadpos','uploadpos','donor'] as $field)

<div class="col-lg-3 col-md-4 col-sm-6">

<label class="form-label fw-semibold">{{ ucfirst($field) }}</label>

<div class="btn-group w-100">

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="yes"
       id="{{ $field }}_yes"
       {{ old($field,$user->$field)=='yes' ? 'checked':'' }}>

<label class="btn btn-outline-success"
       for="{{ $field }}_yes">Yes</label>

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="no"
       id="{{ $field }}_no"
       {{ old($field,$user->$field)=='no' ? 'checked':'' }}>

<label class="btn btn-outline-danger"
       for="{{ $field }}_no">No</label>

</div>

</div>

@endforeach


@foreach(['is_immune','is_freeleech'] as $field)

<div class="col-lg-3 col-md-4 col-sm-6">

<label class="form-label fw-semibold">
{{ ucfirst(str_replace('_',' ',$field)) }}
</label>

<div class="btn-group w-100">

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="1"
       id="{{ $field }}_yes"
       {{ old($field,$user->$field)==1 ? 'checked':'' }}>

<label class="btn btn-outline-success"
       for="{{ $field }}_yes">Yes</label>

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="0"
       id="{{ $field }}_no"
       {{ old($field,$user->$field)==0 ? 'checked':'' }}>

<label class="btn btn-outline-danger"
       for="{{ $field }}_no">No</label>

</div>

</div>

@endforeach

</div>
</div>
</div>


{{-- COMMUNICATION BLOCKS --}}
<div class="card shadow-sm border-0 mb-4">
<div class="card-header bg-dark text-white">
Communication Blocks
</div>

<div class="card-body">

<div class="row g-4">

@foreach(['chatblock'=>'Chat Block','commentblock'=>'Comment Block','forumblock'=>'Forum Block'] as $field=>$label)

<div class="col-md-4">

<label class="form-label fw-semibold">{{ $label }}</label>

<div class="btn-group w-100">

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="1"
       id="{{ $field }}_yes"
       {{ old($field,$user->$field)==1 ? 'checked':'' }}>

<label class="btn btn-outline-danger"
       for="{{ $field }}_yes">Blocked</label>

<input type="radio"
       class="btn-check"
       name="{{ $field }}"
       value="0"
       id="{{ $field }}_no"
       {{ old($field,$user->$field)==0 ? 'checked':'' }}>

<label class="btn btn-outline-success"
       for="{{ $field }}_no">Allowed</label>

</div>

</div>

@endforeach

</div>

</div>
</div>

@endif


{{-- WARNINGS --}}
@if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR 
     && auth()->id() !== $user->id 
     && auth()->user()->user_class > $user->user_class)

<div class="card shadow-sm border-0 mb-4">

<div class="card-header bg-dark text-white">
Warnings
</div>

<div class="card-body">

<div class="row g-3">

<div class="col-md-4">

<label class="form-label">Warned</label>

<div class="btn-group w-100">

<input type="radio"
class="btn-check"
name="warned"
value="1"
id="warn_yes"
{{ old('warned',$user->warned)==1?'checked':'' }}>

<label class="btn btn-outline-danger"
for="warn_yes">Yes</label>

<input type="radio"
class="btn-check"
name="warned"
value="0"
id="warn_no"
{{ old('warned',$user->warned)==0?'checked':'' }}>

<label class="btn btn-outline-success"
for="warn_no">No</label>

</div>

</div>

<div class="col-md-4">

<label class="form-label">Warned Until</label>

<input type="date"
name="warned_until"
class="form-control"
value="{{ old('warned_until',optional($user->warned_until)->format('Y-m-d')) }}">

</div>

<div class="col-md-12">

<label class="form-label">Warning Reason</label>

<textarea name="warned_reason"
class="form-control"
rows="3">{{ old('warned_reason',$user->warned_reason) }}</textarea>

</div>

</div>

</div>
</div>

@endif


{{-- USER ROLE --}}
@if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR 
     && auth()->id() !== $user->id 
     && auth()->user()->user_class > $user->user_class)

@php
$allClasses = \App\Models\UserClass::getClasses();

$promotionCeiling = [
\App\Models\UserClass::MODERATOR => \App\Models\UserClass::SUPERUSER,
\App\Models\UserClass::ADMIN => \App\Models\UserClass::MODERATOR,
\App\Models\UserClass::OWNER => \App\Models\UserClass::ADMIN,
\App\Models\UserClass::WEB_DEVELOPER => \App\Models\UserClass::OWNER,
];

$authClass = auth()->user()->user_class;
$maxClass = $promotionCeiling[$authClass] ?? null;

$availableClasses = collect($allClasses)->filter(fn($label,$class)=>$class <= $maxClass);
@endphp

@if($availableClasses->isNotEmpty())

<div class="card shadow-sm border-0 mb-4">

<div class="card-header bg-dark text-white">
User Role
</div>

<div class="card-body">

<select name="user_class" class="form-control">

@foreach($availableClasses as $classValue=>$className)

<option value="{{ $classValue }}"
{{ $user->user_class == $classValue ? 'selected':'' }}>
{{ $className }}
</option>

@endforeach

</select>

</div>
</div>

@endif
@endif


{{-- VIP --}}
@if (auth()->user()->user_class >= \App\Models\UserClass::OWNER && auth()->id() !== $user->id)

<div class="card shadow-sm border-0 mb-4">

<div class="card-header bg-dark text-white">
VIP Settings
</div>

<div class="card-body">

@if($user->vip_until)
<p><strong>Current VIP Until:</strong> {{ $user->vip_until }}</p>
@endif

<select id="vip_until" name="vip_until" class="form-control">

<option value="">-- Select Duration --</option>
<option value="4 weeks">4 Weeks</option>
<option value="6 weeks">6 Weeks</option>
<option value="8 weeks">8 Weeks</option>
<option value="10 weeks">10 Weeks</option>
<option value="12 weeks">12 Weeks</option>
<option value="remove">Remove VIP</option>

</select>

</div>
</div>

@endif


@if (auth()->id() !== $user->id)

{{-- USER STATISTICS --}}
<div class="card shadow-sm border-0 mb-4">

<div class="card-header bg-dark text-white">
User Statistics
</div>

<div class="card-body">

<div class="row g-4">

<div class="col-md-4">

<label class="form-label">Uploaded (GB)</label>

<input type="number"
name="uploaded"
class="form-control"
value="{{ old('uploaded',floor($user->uploaded/(1024**3))) }}">

</div>

<div class="col-md-4">

<label class="form-label">Downloaded (GB)</label>

<input type="number"
name="downloaded"
class="form-control"
value="{{ old('downloaded',floor($user->downloaded/(1024**3))) }}">

</div>

<div class="col-md-4">

<label class="form-label">Seedbonus</label>

<input type="number"
step="0.01"
name="seedbonus"
class="form-control"
value="{{ old('seedbonus',$user->seedbonus) }}">

</div>

<div class="col-md-6">

<label class="form-label">Invites</label>

<input type="number"
name="invites"
class="form-control"
value="{{ old('invites',$user->invites) }}">

</div>

<div class="col-md-6">

<label class="form-label">Slots</label>

<input type="number"
name="slots"
class="form-control"
value="{{ old('slots',$user->slots) }}">

</div>

</div>

</div>
</div>

@endif


{{-- USER BIO --}}
<div class="card shadow-sm border-0 mb-4">

<div class="card-header bg-dark text-white">
User Info
</div>

<div class="card-body">

<textarea name="info"
class="form-control"
rows="6">{{ old('info',$user->info) }}</textarea>

</div>

</div>


<div class="d-flex justify-content-between mt-4">

<a href="{{ route('admin.users.index') }}"
class="btn btn-outline-secondary">
← Back
</a>

<button type="submit"
class="btn btn-success px-4">
Save Changes
</button>

</div>

</form>

</div>


<style>

.elite-input{
background:#1f1f2e;
border:1px solid #333;
color:#fff;
}

.elite-input:focus{
background:#1f1f2e;
border-color:#6ea8fe;
box-shadow:none;
color:#fff;
}

.card{
border-radius:10px;
}

.card-header{
font-weight:600;
letter-spacing:.4px;
}

</style>

@endsection