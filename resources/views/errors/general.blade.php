{{-- resources/views/errors/general.blade.php --}}
@extends('layouts.app')

@section('title', 'Error')

@section('content')
<div class="container-fluid mt-5" style="background: linear-gradient(135deg, rgb(105, 100, 101) 0%, rgba(76, 54, 58, 0.8) 35%, rgb(189, 219, 225) 100%);rounded-corners: 20px; padding: 20px;">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <h3 class="mb-0">Oops! Something Went Wrong</h3>
                </div>
                <div class="card-body text-center">
                    <!-- Use Font Awesome or Bootstrap Icons -->
                    <i class="fas fa-cogs" style="font-size: 6rem; color: #dc3545;"></i>
                    <h4 class="mt-4">An error has occurred.</h4>
                    <p class="lead">We apologize for the inconvenience. Please try again later or contact support if the issue persists.</p>
                    <pre class="p-3 rounded">We are working to fix the problem !</pre>
                    <pre class="p-3 rounded">Se lucreaza pentru a se remedia problema !</pre>
                </div>
                <div class="text-center mt-4 mb-5">
                    <a href="{{ url('/') }}" class="btn btn-danger">Go Back to Home</a>
                    <a href="{{ url('/tickets') }}" class="btn btn-outline-danger">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
