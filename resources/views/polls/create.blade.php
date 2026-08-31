@extends('layouts.app')

@section('content')
@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h2 class="fw-bold mb-4">Create Poll</h2>

            <form action="{{ route('polls.store') }}" method="POST">
                @csrf

                {{-- Title --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Poll Title</label>
                    <input type="text"
                           class="form-control"
                           name="title"
                           required>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control"
                              name="description"
                              rows="3"></textarea>
                </div>

                {{-- Options --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Poll Options</label>

                    <div id="options-container" class="vstack gap-2">
                        <div class="input-group option-row">
                            <input type="text"
                                   class="form-control"
                                   name="options[]"
                                   placeholder="Option 1"
                                   required>
                            <button type="button"
                                    class="btn btn-outline-danger remove-option"
                                    disabled>
                                ✕
                            </button>
                        </div>

                        <div class="input-group option-row">
                            <input type="text"
                                   class="form-control"
                                   name="options[]"
                                   placeholder="Option 2"
                                   required>
                            <button type="button"
                                    class="btn btn-outline-danger remove-option"
                                    disabled>
                                ✕
                            </button>
                        </div>
                    </div>

                    <button type="button"
                            id="add-option"
                            class="btn btn-sm btn-outline-secondary mt-3">
                        ➕ Add option
                    </button>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        Create Poll
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JS --}}
<script>
    const container = document.getElementById('options-container');
    const addBtn = document.getElementById('add-option');

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.option-row');
        rows.forEach(btn => {
            btn.querySelector('.remove-option').disabled = rows.length <= 2;
        });
    }

    addBtn.addEventListener('click', () => {
        const count = container.children.length + 1;

        const row = document.createElement('div');
        row.className = 'input-group option-row';

        row.innerHTML = `
            <input type="text"
                   class="form-control"
                   name="options[]"
                   placeholder="Option ${count}"
                   required>
            <button type="button"
                    class="btn btn-outline-danger remove-option">
                ✕
            </button>
        `;

        container.appendChild(row);
        updateRemoveButtons();
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.option-row').remove();
            updateRemoveButtons();
        }
    });

    updateRemoveButtons();
</script>

@else
<div class="container py-5 text-center">
    <h2>Access Denied</h2>
    <p class="text-muted">You do not have permission to create polls.</p>
</div>
@endif
@endsection
