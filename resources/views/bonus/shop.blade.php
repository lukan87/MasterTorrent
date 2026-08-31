@extends('layouts.app') 

@section('content')

@section('content')

@php
    $happyHour = \App\Models\HappyHour::where('active', true)->latest('start_at')->first();
    $multiplier = $happyHour ? $happyHour->upload_multiplier : 1;
    $remaining = $happyHour ? now()->diffForHumans($happyHour->end_at, ['short' => true, 'parts' => 2]) : null;
@endphp

<div class="row mt-5 mb-4">
    <!-- First Card: Seed Bonus Info -->
    <div class="col-md-6">
        <div class="card glass">
            <div class="card-body">
                <h5 class="card-header">Seed Bonus</h5>
                <p class="card-text">
                    <strong>Your Seed Bonus Points:</strong> {{ Auth::user()->seedbonus }}
                </p>
                <p class="card-text">
                    <strong>Currently Seeding:</strong> 
                    <strong>{{ Auth::user()->seeding_torrent_count }}</strong> Torrent{{ Auth::user()->seeding_torrent_count == 1 ? '' : 's' }}
                </p>
                <p class="card-text">
                    <strong>Your Earning Rate:</strong> {{ Auth::user()->seedbonus_per_hour * $multiplier }} Points per hour
                    @if($happyHour)
                        <span class="badge bg-success ms-2">
                            🎉 Happy Hour! {{ $multiplier }}x
                        </span>
                    @endif
                </p>
                @if($happyHour)
                    <p class="card-text text-warning">
                        Duration remaining: {{ $remaining }} 
                        @if($happyHour->free_download)
                            | Free Downloads Enabled!
                        @endif
                    </p>
                @else
                    <p class="card-text">Standard: 0.15 points per hour for seeding a torrent</p>
                @endif
            </div>
        </div>
    </div>
    
        <!-- Second Card: Other Ways to Earn -->
        <div class="col-md-6">
            <div class="card glass shadow-sm">
                <div class="card-body">
                    <h4 class="card-header">Other Ways to Earn Seedbonus</h4>
                    <ul class="list-unstyled">
                        <li><strong>Uploading a Torrent:</strong> 10 points</li>
                        <li><strong>Thanking a Torrent:</strong> 0.5 points</li>
                        <li><strong>Commenting a Torrent:</strong> 1 point</li>
                    </ul>
                    <p><strong>*Note: The site administrator can change your seedbonus points without prior notice.</strong></p>
                    <p><strong>*Notă: Administratorul site-ului poate modifica punctele tale de seedbonus fără o notificare prealabilă.</strong></p>
                </div>
            </div>
        </div>
    </div>
    

    <div class="row">
        <!-- 10 GB Upload -->
        <div class="col-md-4 mt-4">
            <div class="card glass">
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
            <div class="card glass">
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
            <div class="card glass">
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
            <div class="card glass">
                <div class="card-header">
                    @if (Auth::user()->user_class >= 3 && Auth::user()->vip_until)
                        VIP Status: Expires on {{ \Carbon\Carbon::parse(Auth::user()->vip_until)->format('F j, Y') }}
                    @else
                        VIP Promotion (1 Year)
                    @endif
                </div>

                <div class="card-body">
                    <p>Cost: 50,000 Points</p>
                    <form action="{{ route('bonus.buyVip') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning {{ Auth::user()->seedbonus < 50000 || Auth::user()->user_class >= 3 ? 'disabled' : '' }}">
                            @if (Auth::user()->user_class >= 3)
                                <i class="bi bi-gem"></i> You are already VIP or higher
                            @elseif (Auth::user()->seedbonus < 50000)
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
            <div class="card glass">
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
            <div class="card glass">
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
            <div class="card glass">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-gift"></i> Buy a Surprise!
                </div>
                <div class="card-body">
                    <p>Cost: 15000 Points</p>
                    <p><strong>Possible Rewards:</strong></p>
                    <ul>
                        <li><i class="bi bi-hdd"></i> 100GB, 250GB, or 500GB Upload</li>
                        <li><i class="bi bi-gem"></i> VIP for 1, 2, or 3 months</li>
                        <li><i class="bi bi-person-plus"></i> 3, 6, or 10 Invites</li>
                        <li><i class="bi bi-grid-1x2"></i> 5, 10, or 15 Slots</li>
                    </ul>
                    <form action="{{ route('bonus.surprise') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger {{ Auth::user()->seedbonus < 15000 ? 'disabled' : '' }}">
                            <i class="bi bi-gift"></i> Buy Surprise!
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>


@endsection





