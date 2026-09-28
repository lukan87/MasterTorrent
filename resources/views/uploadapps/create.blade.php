@extends('layouts.app')
@section('content')
<div class="container-fluid py-4 ua-page">
    <a class="ua-link d-inline-block mb-3" href="{{ route('uploadapps.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>All applications</a>
    <header class="ua-hero mb-4"><span class="ua-eyebrow">Become an uploader</span><h1>What will you bring to the community?</h1><p class="ua-muted mb-0">Tell us about your experience and the content you plan to share. Staff will review your application and send the decision to your private inbox.</p></header>
    @include('uploadapps.partials.feedback')
    <form method="POST" action="{{ route('uploadapps.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8"><section class="ua-card">
                <h2 class="mb-4">Experience &amp; plans</h2>
                @foreach(['why_promoted' => ['Why would you like to become an uploader?', 'Describe how you would contribute to the tracker.'], 'experience' => ['Your experience with torrents and trackers', 'Tell us about creating torrents, uploading and maintaining releases.'], 'content_plan' => ['What do you plan to upload?', 'Mention content types, sources and how regularly you expect to contribute.']] as $field => [$label, $help])
                    <div class="mb-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }} <span class="ua-muted">(required)</span></label>
                        <textarea id="{{ $field }}" name="{{ $field }}" rows="5" maxlength="10000" required aria-describedby="{{ $field }}-help" class="form-control @error($field) is-invalid @enderror">{{ old($field) }}</textarea>
                        <div id="{{ $field }}-help" class="form-text">{{ $help }}</div>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <label for="external_sites" class="form-label">Other trackers <span class="ua-muted">(optional)</span></label>
                <textarea id="external_sites" name="external_sites" rows="3" maxlength="5000" class="form-control @error('external_sites') is-invalid @enderror">{{ old('external_sites') }}</textarea>
                @error('external_sites')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </section></div>
            <div class="col-lg-4"><section class="ua-card">
                <h2 class="mb-4">Connection &amp; knowledge</h2>
                @foreach(['internal_speed' => 'Internal speedtest', 'external_speed' => 'External speedtest'] as $field => $label)
                    <div class="mb-4"><label for="{{ $field }}" class="form-label">{{ $label }} <span class="ua-muted">(optional)</span></label><input id="{{ $field }}" type="url" name="{{ $field }}" maxlength="255" value="{{ old($field) }}" placeholder="https://…" class="form-control @error($field) is-invalid @enderror">@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                @endforeach
                @foreach(['scene_access' => 'Do you have scene access?', 'know_torrents' => 'Can you create torrents?', 'understand_seeding' => 'Do you understand the seeding requirements?'] as $field => $label)
                    <div class="mb-4"><label for="{{ $field }}" class="form-label">{{ $label }}</label><select id="{{ $field }}" name="{{ $field }}" required class="form-select @error($field) is-invalid @enderror"><option value="" @selected(old($field, '') === '') disabled>Select an answer</option><option value="1" @selected((string) old($field) === '1')>Yes</option><option value="0" @selected((string) old($field) === '0')>No</option></select>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                @endforeach
                <p class="ua-muted mb-0">Your answers are visible to reviewing administrators. Only one active application is allowed at a time.</p>
            </section></div>
        </div>
        <div class="d-flex gap-3 flex-wrap mt-4"><button type="submit" class="btn btn-success"><i class="bi bi-send me-2" aria-hidden="true"></i>Submit application</button><a class="btn btn-outline-secondary" href="{{ route('uploadapps.index') }}">Cancel</a></div>
    </form>
</div>
@include('uploadapps.partials.style')
@endsection
