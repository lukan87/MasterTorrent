@extends('layouts.app')

@section('content')
    <h1>Create a Poll</h1>
    <form action="{{ route('polls.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="title">Poll Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Poll Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="options">Poll Options</label>
            <div id="options-container">
                <div class="option-group">
                    <input type="text" class="form-control" name="options[]" placeholder="Option 1" required>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" id="add-option">Add Option</button>
        </div>

        <button type="submit" class="btn btn-primary">Create Poll</button>
    </form>

    <script>
        document.getElementById('add-option').addEventListener('click', function() {
            var container = document.getElementById('options-container');
            var newOption = document.createElement('div');
            newOption.classList.add('option-group');
            newOption.innerHTML = '<input type="text" class="form-control" name="options[]" placeholder="New Option" required>';
            container.appendChild(newOption);
        });
    </script>
@endsection
