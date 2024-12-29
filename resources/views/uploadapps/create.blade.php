@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Create New Upload Application</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

     <!-- Check if the user is eligible to upload -->
     @if(auth()->user()->user_class > 4 || auth()->user()->uploadpos == 'yes')
        <div class="alert alert-info">
            <strong>You already can upload torrents!</strong>
        </div>
    @else

    <form action="{{ route('uploadapps.store') }}" method="POST">
        @csrf

        <!-- Hidden Applicant ID -->
        <input type="hidden" name="applicant_id" value="{{ auth()->user()->id }}">

        <!-- Why Promoted -->
        <div class="mb-3">
            <label for="why_promoted" class="form-label">Why Should You Be Promoted?</label>
            <textarea name="why_promoted" id="why_promoted" class="form-control" rows="4" required>{{ old('why_promoted') }}</textarea>
        </div>

        <!-- Internal Speed -->
        <div class="mb-3">
            <label for="internal_speed" class="form-label">Internal Speed (Link)</label>
            <input type="url" name="internal_speed" id="internal_speed" class="form-control" placeholder="https://speedtest.net" value="{{ old('internal_speed') }}">
        </div>

        <!-- External Speed -->
        <div class="mb-3">
            <label for="external_speed" class="form-label">External Speed (Link)</label>
            <input type="url" name="external_speed" id="external_speed" class="form-control" placeholder="https://speedtest.net" value="{{ old('external_speed') }}">
        </div>

        <!-- External Sites -->
        <div class="mb-3">
            <label for="external_sites" class="form-label">Sursele mele de upload externe sunt:</label>
            <textarea name="external_sites" id="external_sites" class="form-control" rows="2">{{ old('external_sites') }}</textarea>
        </div>

        <!-- Scene Access -->
        <div class="mb-3">
            <label for="scene_access" class="form-label">Scene Access</label>
            <select name="scene_access" id="scene_access" class="form-select" required>
                <option value="1" {{ old('scene_access') == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('scene_access') == 0 ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Know How to Create Torrents -->
        <div class="mb-3">
            <label for="know_torrents" class="form-label">Stiu sa fac un torrent si sa il pun la seed:</label>
            <select name="know_torrents" id="know_torrents" class="form-select" required>
                <option value="1" {{ old('know_torrents') == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('know_torrents') == 0 ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Understand Seeding -->
        <div class="mb-3">
            <label for="understand_seeding" class="form-label">Inteleg ca trebuie sa tin la seed un torrent minim 3 zile:</i></label>
            <select name="understand_seeding" id="understand_seeding" class="form-select" required>
                <option value="1" {{ old('understand_seeding') == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('understand_seeding') == 0 ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="mb-3">
            <button type="submit" class="btn btn-success">Submit Application</button>
            <a href="{{ route('uploadapps.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    @endif
</div>
@endsection
