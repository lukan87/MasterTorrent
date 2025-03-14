@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2>Users with Warnings</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Warnings Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usersWithWarnings as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->warnings_count }}</td>
                <td>
                    <a href="{{ route('warnings.show', ['id' => $user->id, 'username' => $user->username]) }}" class="btn btn-primary btn-sm">View Warnings</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $usersWithWarnings->links('pagination::bootstrap-5') }}
</div>
@endsection
