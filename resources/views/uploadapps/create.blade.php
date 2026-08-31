@extends('layouts.app')

@section('content')

<div class="container mt-5">

<h2 class="mb-4">Uploader Application</h2>

<div class="card glass">

<div class="card-header">
Apply for Uploader Status
</div>

<div class="card-body">

@if ($errors->any())

<div class="alert alert-danger">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>

@endif


<form method="POST" action="{{ route('uploadapps.store') }}">

@csrf


{{-- WHY PROMOTED --}}

<div class="mb-3">

<label class="form-label">
Why should you be promoted to uploader?
</label>

<textarea
name="why_promoted"
class="form-control"
rows="4"
required
>{{ old('why_promoted') }}</textarea>

</div>


{{-- EXPERIENCE --}}

<div class="mb-3">

<label class="form-label">
Describe your experience with torrents and trackers
</label>

<textarea
name="experience"
class="form-control"
rows="4"
required
>{{ old('experience') }}</textarea>

</div>


{{-- CONTENT PLAN --}}

<div class="mb-3">

<label class="form-label">
What content do you plan to upload?
</label>

<textarea
name="content_plan"
class="form-control"
rows="4"
required
>{{ old('content_plan') }}</textarea>

</div>


{{-- INTERNAL SPEED --}}

<div class="mb-3">

<label class="form-label">
Internal tracker speedtest (optional)
</label>

<input
type="url"
name="internal_speed"
class="form-control"
value="{{ old('internal_speed') }}"
placeholder="Speedtest URL"
/>

</div>


{{-- EXTERNAL SPEED --}}

<div class="mb-3">

<label class="form-label">
External speedtest (Speedtest.net etc.)
</label>

<input
type="url"
name="external_speed"
class="form-control"
value="{{ old('external_speed') }}"
placeholder="Speedtest URL"
/>

</div>


{{-- EXTERNAL SITES --}}

<div class="mb-3">

<label class="form-label">
Other trackers you are member of (optional)
</label>

<textarea
name="external_sites"
class="form-control"
rows="3"
>{{ old('external_sites') }}</textarea>

</div>


{{-- SCENE ACCESS --}}

<div class="mb-3">

<label class="form-label">
Do you have scene access?
</label>

<select name="scene_access" class="form-control">

<option value="1">Yes</option>
<option value="0">No</option>

</select>

</div>


{{-- TORRENT KNOWLEDGE --}}

<div class="mb-3">

<label class="form-label">
Do you know how to create torrents?
</label>

<select name="know_torrents" class="form-control">

<option value="1">Yes</option>
<option value="0">No</option>

</select>

</div>


{{-- SEEDING KNOWLEDGE --}}

<div class="mb-4">

<label class="form-label">
Do you understand seeding requirements?
</label>

<select name="understand_seeding" class="form-control">

<option value="1">Yes</option>
<option value="0">No</option>

</select>

</div>


<button type="submit" class="btn btn-primary">

Submit Application

</button>

<a href="{{ route('uploadapps.index') }}" class="btn btn-secondary">

Cancel

</a>


</form>

</div>
</div>

</div>

@endsection