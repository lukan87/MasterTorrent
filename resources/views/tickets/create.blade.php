@extends('layouts.app')
@section('content')
@include('tickets.partials.style')
<div class="support">
    <header class="support-header"><div><div class="support-eyebrow">FileiPlay support</div><h1>{{ !empty($reportedUser) ? 'Report a user' : ($torrent ? 'Report a torrent' : 'Create a support ticket') }}</h1><p>Tell us what happened so the right person can help.</p></div><a class="support-btn" href="{{ route('tickets.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back to tickets</a></header>
    @include('tickets.partials.feedback')
    <div class="support-grid">
        <section class="support-panel"><div class="support-panel-head"><h2>Request details</h2><small>All fields required except attachments</small></div>
            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="support-panel-body">
                @csrf
                <div class="support-form-grid">
                    <div class="support-field"><label for="category">Category</label><select id="category" name="category_id" class="form-select" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $selectedCategoryId ?? null) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                    <div class="support-field"><label for="priority">Priority</label><select id="priority" name="priority" class="form-select" required aria-describedby="priority-help">@foreach(\App\Models\Ticket::PRIORITIES as $priority)<option @selected(old('priority', 'Medium') === $priority)>{{ $priority }}</option>@endforeach</select><p id="priority-help" class="support-help">Medium is suitable for most requests.</p></div>
                </div>
                <div class="support-field"><label for="title">Subject</label><input id="title" name="title" class="form-control" value="{{ old('title', $torrent ? 'Issue with torrent: '.\Illuminate\Support\Str::limit($torrent->name, 220, '') : (!empty($reportedUser) ? 'Report user: '.\Illuminate\Support\Str::limit($reportedUser->name, 220, '') : '')) }}" placeholder="Summarize your issue in one sentence" maxlength="255" required></div>
                @if($torrent)<div class="support-notice"><i class="bi bi-file-earmark-play me-2" aria-hidden="true"></i>Reporting: {{ $torrent->name }}<input type="hidden" name="linked_torrent_id" value="{{ $torrent->id }}"></div>@endif
                @if(!empty($reportedUser))<div class="support-notice"><i class="bi bi-person me-2" aria-hidden="true"></i>Reporting user: {{ $reportedUser->name }} (#{{ $reportedUser->id }})<input type="hidden" name="linked_user_id" value="{{ $reportedUser->id }}"></div>@endif
                <div class="support-field"><label for="description">Description</label><textarea id="description" name="description" rows="9" class="form-control" maxlength="20000" required aria-describedby="description-help" placeholder="{{ !empty($reportedUser) ? 'Describe the behavior you are reporting, when and where it happened, and include links or evidence that can help staff investigate.' : 'What were you trying to do? What happened instead? Include any error messages and steps you have already tried.' }}">{{ old('description') }}</textarea><p id="description-help" class="support-help">Include enough detail for us to reproduce the issue. Do not include your password or passkey.</p></div>
                @include('tickets.partials.upload')
                <div class="support-actions"><button type="submit" class="support-btn support-btn-primary"><i class="bi bi-send" aria-hidden="true"></i>Submit ticket</button><a class="support-btn" href="{{ route('tickets.index') }}">Cancel</a></div>
            </form>
        </section>
        <aside>
            <section class="support-panel"><div class="support-panel-head"><h2>A helpful request includes</h2></div><div class="support-panel-body"><ol class="support-guidance"><li><strong>What happened</strong><br>Describe the issue and the result you expected.</li><li><strong>Where it happened</strong><br>Add the page, torrent name, or relevant error message.</li><li><strong>What you tried</strong><br>List any steps you took and attach a screenshot if useful.</li></ol></div></section>
            <section class="support-panel"><div class="support-panel-body"><h3>What happens next?</h3><p class="support-help">Your request appears in your ticket list. Our team will review it and reply in the conversation. You can add more information at any time while the ticket is unlocked.</p></div></section>
        </aside>
    </div>
</div>
@endsection
