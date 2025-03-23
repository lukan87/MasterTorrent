@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create a New Ticket</h2>
    <form method="POST" action="{{ route('tickets.store') }}">
        @csrf

        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="Technical Issue">Technical Issue</option>
                <option value="Account Problem">Account Problem</option>
                <option value="Download/Upload Problem">Download/Upload Problem</option>
                <option value="Torrents">Torrents</option>
                <option value="Users">Users</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select class="form-control" id="priority" name="priority" required>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </div>

        <div class="form-group">
            <label for="title">Ticket Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Submit Ticket</button>
    </form>
</div>
@endsection
