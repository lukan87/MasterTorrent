@extends('layouts.app')

@section('content')
<div class="container glass mt-5">
    <h2>Create Announcement</h2>

    <form method="POST" action="{{ route('announcements.store') }}">
        @csrf

        {{-- Title --}}
        <div class="mb-3">
            <input type="text" name="title" class="form-control" placeholder="Title" required>
            <small class="text-muted">
                The headline users will see in the announcements list.
            </small>
        </div>

        {{-- Body --}}
        <div class="mb-3">
            <textarea name="body" class="form-control" rows="5" placeholder="Body" required></textarea>
            <small class="text-muted">
                Full content of the announcement. You can write multiple lines.
            </small>
        </div>

        {{-- Type --}}
        <div class="mb-3">
            <select name="type" class="form-control">
                <option value="info" selected>Info</option>
                <option value="success">Success</option>
                <option value="warning">Warning</option>
            </select>
            <small class="text-muted">
                Controls the color/style of the announcement (info = blue, success = green, warning = important).
            </small>
        </div>

        {{-- Priority --}}
        <div class="mb-3">
            <input type="number" name="priority" class="form-control" placeholder="Priority (default 1)" value="1">
            <small class="text-muted">
                Higher numbers appear first. Use this to pin important announcements.
            </small>
        </div>

        {{-- Published At --}}
        <div class="mb-3">
            <input type="datetime-local" name="published_at" class="form-control">
            <small class="text-muted">
                Leave empty to publish immediately. Set a future date to schedule it.
            </small>
        </div>

        {{-- Expires At --}}
        <div class="mb-3">
            <input type="datetime-local" name="expires_at" class="form-control">
            <small class="text-muted">
                Optional. After this date, the announcement will no longer be visible.
            </small>
        </div>

        <button class="btn btn-primary mb-3">Create</button>
    </form>
</div>
@endsection