@extends('layouts.app') 

@section('content')

    <h1>Shop</h1>

   
    <div class="mb-4">
        <h5>Your Seed Bonus Points: <strong>{{ Auth::user()->seedbonus }}</strong></h5>
        <h5>Your Earning Rate: <strong>{{ Auth::user()->seedbonus_per_hour }}</strong> Points per hour</h5>
    </div>


    <div class="mt-5">
        <h4>Other Ways to Earn Seedbonus</h4>
        <ul>
            <li><strong>Uploading a Torrent:</strong> 5 points</li>
            <li><strong>Thanking a Torrent:</strong> 0.5 points</li>
            <li><strong>Commenting a Torrent:</strong> 1 point</li>
        </ul>
    </div>

    <div class="row">
        <!-- 10 GB Upload -->
        <div class="col-md-4 mt-4">
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
                            <i class="bi bi-upload"></i> Buy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 25 GB Upload -->
        <div class="col-md-4 mt-4">
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
                            <i class="bi bi-upload"></i> Buy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 100 GB Upload -->
        <div class="col-md-4 mt-4">
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
                            <i class="bi bi-upload"></i> Buy
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
                        <button type="submit" class="btn btn-warning {{ Auth::user()->seedbonus < 100000 || Auth::user()->user_class >= 3 ? 'disabled' : '' }}">
                            @if (Auth::user()->user_class >= 3)
                                <i class="bi bi-gem"></i> You are already VIP or higher
                            @elseif (Auth::user()->seedbonus < 100000)
                                <i class="bi bi-x-circle"></i> Not enough points
                            @else
                                <i class="bi bi-gem"></i> Buy VIP
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Buy Invites -->
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-header">
                    Buy Invites
                </div>
                <div class="card-body">
                    <p>Cost: 1500 Points per Invite</p>
                    <form action="{{ route('buy.invites') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success {{ Auth::user()->seedbonus < 1500 ? 'disabled' : '' }}">
                            <i class="bi bi-person-plus"></i> Buy Invite
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Buy Slots -->
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-header">
                    Buy Slots - Mark a torrents as double upload or freeleech
                </div>
                <div class="card-body">
                    <p>Cost: 1000 Points per Slot</p>
                    <form action="{{ route('buy.slots') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info {{ Auth::user()->seedbonus < 1000 ? 'disabled' : '' }}">
                            <i class="bi bi-grid-1x2"></i> Buy Slot
                        </button>
                    </form>
                </div>
            </div>
        </div>

          <!-- Buy Surprise -->
          <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-gift"></i> Buy a Surprise!
                </div>
                <div class="card-body">
                    <p>Cost: 5000 Points</p>
                    <p><strong>Possible Rewards:</strong></p>
                    <ul>
                        <li><i class="bi bi-hdd"></i> 100GB, 250GB, or 500GB Upload</li>
                        <li><i class="bi bi-gem"></i> VIP for 1, 2, or 3 months</li>
                        <li><i class="bi bi-person-plus"></i> 3, 6, or 10 Invites</li>
                        <li><i class="bi bi-grid-1x2"></i> 5, 10, or 15 Slots</li>
                    </ul>
                    <form action="{{ route('bonus.surprise') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger {{ Auth::user()->seedbonus < 5000 ? 'disabled' : '' }}">
                            <i class="bi bi-gift"></i> Buy Surprise!
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>


@endsection


