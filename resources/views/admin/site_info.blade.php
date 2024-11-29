@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Site Information</h1>
    <h3>PHP Info</h3>
    <div>
        {{ phpinfo() }}
    </div>
</div>
@endsection
