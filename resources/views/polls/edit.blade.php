@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="fw-bold mb-4">Edit Poll</h2>

    <form action="{{ route('polls.update', $poll->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Title --}}
        <div class="mb-3">
            <label class="form-label">Poll Title</label>
            <input type="text"
                   class="form-control @error('title') is-invalid @enderror"
                   name="title"
                   value="{{ old('title', $poll->title) }}"
                   required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="form-label">Poll Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror"
                      name="description"
                      rows="3">{{ old('description', $poll->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Options --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Options</label>

            <div id="optionsContainer" class="vstack gap-2">

                @foreach($poll->options as $option)
                    <div class="input-group option-item" data-existing="1">
                        <input type="text"
                               class="form-control"
                               name="options[{{ $option->id }}]"
                               value="{{ old("options.$option->id", $option->option_text) }}"
                               required>

                        <button type="button"
                                class="btn btn-outline-danger remove-option">
                            ✕
                        </button>
                    </div>
                @endforeach

            </div>

            <button type="button"
                    id="addOption"
                    class="btn btn-sm btn-outline-secondary mt-3">
                ➕ Add Option
            </button>
        </div>

        {{-- Actions --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('polls.index') }}" class="btn btn-outline-secondary">
                Back
            </a>

            <button type="submit" class="btn btn-success">
                Update Poll
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const container = document.getElementById('optionsContainer');
    const addBtn = document.getElementById('addOption');

    function updateRemoveButtons() {
        const items = container.querySelectorAll('.option-item');
        items.forEach(item => {
            item.querySelector('.remove-option').disabled = items.length <= 2;
        });
    }

    // Add new option
    addBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'input-group option-item';

        div.innerHTML = `
            <input type="text"
                   class="form-control"
                   name="options[new][]"
                   required>
            <button type="button"
                    class="btn btn-outline-danger remove-option">
                ✕
            </button>
        `;

        container.appendChild(div);
        updateRemoveButtons();
    });

    // Remove option (event delegation)
    container.addEventListener('click', e => {
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.option-item').remove();
            updateRemoveButtons();
        }
    });

    updateRemoveButtons();
</script>
@endpush
