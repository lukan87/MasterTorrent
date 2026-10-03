@extends('layouts.admin')
@section('title', 'Schedule Happy Hour')
@section('admin-content')
@include('admin.happyhour.partials.styles')
<div class="happyhour-workspace hh-create">
    <a class="hh-back" href="{{ route('happyhour.index') }}">← All Happy Hours</a>
    <header class="hh-header"><div><div class="hh-eyebrow">Community rewards</div><h1>Schedule Happy Hour</h1>
    <p class="text-muted">Choose a preset or customize your rewards. All times use {{ config('app.timezone') }}.</p></div></header>
    <form method="POST" action="{{ route('happyhour.store') }}" class="card"><div class="card-body p-4">
        @csrf
        <div class="mb-3">
            <label for="theme" class="form-label">Event preset</label>
            <select name="theme" id="theme" class="form-select" required>
                @foreach($dayThemes as $preset)
                    <option value="{{ $preset['name'] }}" data-multiplier="{{ $preset['upload_multiplier'] }}" data-free="{{ (int) $preset['free_download'] }}" data-hours="{{ $preset['duration_hours'] }}" @selected(old('theme', $theme['name']) === $preset['name'])>{{ $preset['name'] }} · {{ $preset['duration_hours'] }} hours</option>
                @endforeach
                <option value="custom" @selected(old('theme') === 'custom')>Custom event</option>
            </select>
        </div>
        <div class="mb-3" id="custom-theme-container">
            <label for="custom_theme_name" class="form-label">Custom event name (required for custom events)</label>
            <input id="custom_theme_name" name="custom_theme_name" class="form-control" maxlength="100" value="{{ old('custom_theme_name') }}">
        </div>
        <div class="mb-3">
            <label for="upload_multiplier" class="form-label">Upload multiplier</label>
            <input id="upload_multiplier" name="upload_multiplier" type="number" min="1" max="10" required class="form-control" value="{{ old('upload_multiplier', $theme['upload_multiplier']) }}">
        </div>
        <input type="hidden" name="free_download" value="0">
        <div class="form-check form-switch mb-4">
            <input id="free_download" name="free_download" type="checkbox" value="1" class="form-check-input" @checked(old('free_download', $theme['free_download']))>
            <label for="free_download" class="form-check-label">Freeleech — downloads do not reduce ratio</label>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><label for="start_at" class="form-label">Starts at</label><input id="start_at" name="start_at" type="datetime-local" required class="form-control" value="{{ old('start_at', now()->format('Y-m-d\TH:i')) }}"></div>
            <div class="col-md-6"><label for="end_at" class="form-label">Ends at</label><input id="end_at" name="end_at" type="datetime-local" required class="form-control" value="{{ old('end_at', now()->addHours($theme['duration_hours'])->format('Y-m-d\TH:i')) }}"></div>
        </div>
        <p class="alert alert-info" id="happy-hour-preview" aria-live="polite">Review the rewards and schedule before saving.</p>
        <p class="small text-muted">Upload bonuses multiply existing double-upload rewards. External torrents keep their usual accounting. Freeleech does not waive seeding requirements. Events cannot overlap.</p>
        <button class="btn btn-primary" type="submit">Save event</button>
        <a href="{{ route('happyhour.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div></form>
</div>
<script>
(() => {
    const theme = document.getElementById('theme');
    const custom = document.getElementById('custom_theme_name');
    const multiplier = document.getElementById('upload_multiplier');
    const free = document.getElementById('free_download');
    const start = document.getElementById('start_at');
    const end = document.getElementById('end_at');
    function preview() {
        document.getElementById('custom-theme-container').hidden = theme.value !== 'custom';
        custom.required = theme.value === 'custom';
        document.getElementById('happy-hour-preview').textContent = `${theme.value === 'custom' ? custom.value || 'Custom event' : theme.value}: ${multiplier.value}× upload credit · ${free.checked ? 'Freeleech' : 'Standard downloads'}`;
    }
    theme.addEventListener('change', () => {
        const preset = theme.selectedOptions[0].dataset;
        if (theme.value !== 'custom') {
            multiplier.value = preset.multiplier;
            free.checked = preset.free === '1';
            // Treat datetime-local as wall-clock fields in the displayed server timezone.
            const date = new Date(start.value + 'Z');
            if (!Number.isNaN(date.getTime())) {
                date.setUTCHours(date.getUTCHours() + Number(preset.hours));
                end.value = date.toISOString().slice(0, 16);
            }
        }
        preview();
    });
    [custom, multiplier, free].forEach(input => input.addEventListener('input', preview));
    preview();
})();
</script>
@endsection
