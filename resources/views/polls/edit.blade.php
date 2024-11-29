@extends('layouts.app')

@section('content')
    <h1>Edit Poll</h1>

    <form action="{{ route('polls.update', $poll->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Poll Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $poll->title) }}" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Poll Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $poll->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <h5>Options:</h5>
        <div class="mb-3">
            @foreach($poll->options as $option)
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="options[{{ $option->id }}]" value="{{ old('options.' . $option->id, $option->option_text) }}" required>
                    <button type="button" class="btn btn-danger" onclick="removeOption(this)">Remove</button>
                </div>
            @endforeach
            <button type="button" class="btn btn-primary" id="addOption">Add Option</button>
        </div>

        <button type="submit" class="btn btn-success">Update Poll</button>
    </form>

    <a href="{{ route('polls.index') }}" class="btn btn-secondary mt-2">Back to Polls</a>
@endsection

@push('scripts')
    <script>
        // Add new option field dynamically
        document.getElementById('addOption').addEventListener('click', function() {
            const newOption = document.createElement('div');
            newOption.classList.add('input-group', 'mb-2');
            newOption.innerHTML = `
                <input type="text" class="form-control" name="options[new]" required>
                <button type="button" class="btn btn-danger" onclick="removeOption(this)">Remove</button>
            `;
            document.querySelector('.mb-3').appendChild(newOption);
        });

        // Remove option field
        function removeOption(button) {
            button.closest('.input-group').remove();
        }
    </script>
@endpush
