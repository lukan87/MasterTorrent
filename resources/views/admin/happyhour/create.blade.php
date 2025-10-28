@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header text-white text-center rounded-top-4 p-4" style="background: linear-gradient(90deg, #4e54c8, #8f94fb);">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-hourglass-split me-2"></i>Start a New Happy Hour</h3>
                    <small class="text-white-50">Create and customize your Happy Hour promotion</small>
                </div>
                <div class="card-body p-4">
                    <!-- Live Preview -->
                    <div class="alert alert-info rounded-3 mb-4" id="happy-hour-preview">
                        🎉 Theme: <strong>{{ $theme['name'] }}</strong>, 
                        Upload: <strong>{{ $theme['upload_multiplier'] }}x</strong>, 
                        Free Download: <strong>{{ $theme['free_download'] ? 'Yes' : 'No' }}</strong>
                    </div>

                    <form action="{{ route('happyhour.store') }}" method="POST">
                        @csrf

                        <!-- Theme Select -->
                        <div class="mb-3">
                            <label for="theme" class="form-label fw-semibold">Theme</label>
                            <select name="theme" id="theme" class="form-select form-select-lg">
                                @foreach($dayThemes as $dayIndex => $t)
                                    <option value="{{ $t['name'] }}" 
                                        data-multiplier="{{ $t['upload_multiplier'] }}" 
                                        data-free="{{ $t['free_download'] ? 'Yes' : 'No' }}"
                                        {{ $t['name'] === $theme['name'] ? 'selected' : '' }}>
                                        {{ $t['name'] }} ({{ $t['upload_multiplier'] }}x, {{ $t['duration_hours'] }}h, 
                                        {{ $t['free_download'] ? 'Free' : 'No Free' }})
                                    </option>
                                @endforeach
                                <option value="custom">Custom Theme</option>
                            </select>
                        </div>

                        <!-- Custom Theme Name -->
                        <div class="mb-3" id="custom-theme-container" style="display: none;">
                            <label for="custom_theme_name" class="form-label fw-semibold">Custom Theme Name</label>
                            <input type="text" name="custom_theme_name" id="custom_theme_name" class="form-control form-control-lg" placeholder="Enter your custom theme name">
                        </div>

                        <!-- Upload Multiplier -->
                        <div class="mb-3">
                            <label for="upload_multiplier" class="form-label fw-semibold">Upload Multiplier</label>
                            <input type="number" name="upload_multiplier" id="upload_multiplier" class="form-control form-control-lg" min="1" max="10" value="{{ $theme['upload_multiplier'] ?? 3 }}" required>
                        </div>

                        <!-- Free Download -->
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="free_download" id="free_download" class="form-check-input" {{ !empty($theme['free_download']) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="free_download">Free Download</label>
                        </div>

                        <!-- Start & End Date/Time -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="start_at" class="form-label fw-semibold">Start At</label>
                                <input type="datetime-local" name="start_at" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_at" class="form-label fw-semibold">End At</label>
                                <input type="datetime-local" name="end_at" class="form-control form-control-lg" required>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <button type="submit" class="btn btn-gradient btn-success flex-fill py-2 fw-bold">
                                <i class="bi bi-hourglass-split me-1"></i> Start Happy Hour
                            </button>
                            <a href="{{ route('happyhour.index') }}" class="btn btn-outline-secondary flex-fill py-2 fw-bold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-gradient {
        background: linear-gradient(90deg, #4e54c8, #8f94fb);
        border: none;
        color: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
</style>

<script>
const themeSelect = document.getElementById('theme');
const customContainer = document.getElementById('custom-theme-container');
const multiplierInput = document.getElementById('upload_multiplier');
const freeCheckbox = document.getElementById('free_download');
const customInput = document.getElementById('custom_theme_name');
const preview = document.getElementById('happy-hour-preview');

function updatePreview() {
    let themeName = themeSelect.value;
    if (themeName === 'custom' && customInput.value.trim() !== '') {
        themeName = customInput.value;
    }
    const multiplier = multiplierInput.value;
    const free = freeCheckbox.checked ? 'Yes' : 'No';
    preview.innerHTML = `🎉 Theme: <strong>${themeName}</strong>, Upload: <strong>${multiplier}x</strong>, Free Download: <strong>${free}</strong>`;
}

// Event listeners
themeSelect.addEventListener('change', () => {
    customContainer.style.display = themeSelect.value === 'custom' ? 'block' : 'none';
    updatePreview();
});

multiplierInput.addEventListener('input', updatePreview);
freeCheckbox.addEventListener('change', updatePreview);
customInput.addEventListener('input', updatePreview);
</script>
@endsection
