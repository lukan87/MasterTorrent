{{-- resources/views/admin/routes.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>All Routes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>URI</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($routes as $route)
                    <tr>
                        <td>{{ $route->getName() }}</td>
                        <td>{{ $route->uri() }}</td>
                        <td>{{ implode(', ', $route->methods()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
