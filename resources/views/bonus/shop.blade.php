@extends('layouts.app') <!-- Adjust according to your layout -->

@section('content')
<div class="container">
    <h1>Shop</h1>

    <!-- Display User's Seed Bonus and Earning Rate -->
    <div class="mb-4">
        <h5>Your Seed Bonus Points: <strong>{{ Auth::user()->seedbonus }}</strong></h5>
        <h5>Your Earning Rate: <strong>{{ Auth::user()->seedbonus_per_hour }}</strong> Points per hour</h5> <!-- Assuming this field exists -->
    </div>

    <div class="row">
        <!-- 10 GB Upload -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    10 GB Upload
                </div>
                <div class="card-body">
                    <p>Cost: 500 Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">
                        @csrf
                        <input type="hidden" name="amount" value="10">
                        <button type="submit" class="btn btn-primary {{ Auth::user()->seedbonus < 500 ? 'disabled' : '' }}">
                            Buy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 25 GB Upload -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    25 GB Upload
                </div>
                <div class="card-body">
                    <p>Cost: 1000 Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">
                        @csrf
                        <input type="hidden" name="amount" value="25">
                        <button type="submit" class="btn btn-primary {{ Auth::user()->seedbonus < 1000 ? 'disabled' : '' }}">
                            Buy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 100 GB Upload -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    100 GB Upload
                </div>
                <div class="card-body">
                    <p>Cost: 5000 Points</p>
                    <form action="{{ route('shop.upload') }}" method="POST">
                        @csrf
                        <input type="hidden" name="amount" value="100">
                        <button type="submit" class="btn btn-primary {{ Auth::user()->seedbonus < 5000 ? 'disabled' : '' }}">
                            Buy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- VIP Promotion -->
        <div class="col-md-4 mt-4">
    <div class="card">
    <div class="card-header">
    @if (Auth::user()->user_class >= 3 && Auth::user()->vip_until)
        VIP Status: Expires on {{ \Carbon\Carbon::parse(Auth::user()->vip_until)->format('F j, Y') }}
    @else
        VIP Promotion (1 Year)
    @endif
</div>

        <div class="card-body">
            <p>Cost: 100,000 Points</p>
            <form action="{{ route('bonus.buyVip') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="btn btn-warning {{ Auth::user()->seedbonus < 100000 || Auth::user()->user_class >= 3 ? 'disabled' : '' }}">
                    Buy VIP
                </button>
            </form>
            @if (Auth::user()->user_class >= 3)
                <p class="text-danger mt-2">You are already VIP or higher!</p>
            @endif
        </div>
    </div>
</div>

    </div>
</div>
@endsection
