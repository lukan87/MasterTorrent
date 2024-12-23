@extends('layouts.app')

@section('content')
<div class="container">
    <div class="tt_block rounded">
        <div class="tt_blockhead text-right">
            <div class="card">
                <h5 class="card-header">
                    <i class="fa-solid fa-circle-dollar-to-slot"></i> Donation Page
                </h5>
                <div class="card-body">
                    <div class="contain">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info text-center">
                                    <h3>
                                        <i class="fa-solid fa-circle-dollar-to-slot"></i> Donate to keep us alive!
                                        <i class="fa-solid fa-circle-dollar-to-slot"></i>
                                    </h3>
                                    <p>
                                        <b>Using LastFiles is free, but server costs are not! Donate to keep the site alive!</b><br>
                                        If you wish to donate, send a message
                                        <a href="/profile/1067">
                                            <button class="btn btn-success btn-sm">HERE</button>
                                        </a>
                                        with the donated amount to receive the selected benefits!<br>
                                        Currently, we only accept donations through
                                        <img src="https://s3.cointelegraph.com/storage/uploads/view/3278bdc14c74dd4e85732b776d0e5b1d.png" style="width:70px;">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header text-center">
                                <h4><i class="fa-solid fa-hand-holding-dollar"></i> Available Donation Options</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @php
                                        $donations = [
                                            ['amount' => 5, 'vip' => '4 weeks', 'upload' => '50G', 'bonus' => '500'],
                                            ['amount' => 7, 'vip' => '6 weeks', 'upload' => '150G', 'bonus' => '1500'],
                                            ['amount' => 10, 'vip' => '2 months', 'upload' => '300G', 'bonus' => '2500'],
                                            ['amount' => 15, 'vip' => '10 weeks', 'upload' => '500G', 'bonus' => '5000'],
                                            ['amount' => 20, 'vip' => '3 months', 'upload' => '750G', 'bonus' => '7500'],
                                            ['amount' => 30, 'vip' => 'Unlimited', 'upload' => '1000G', 'bonus' => '10000'],
                                        ];
                                        $totalDonation = array_sum(array_column($donations, 'amount'));
                                    @endphp

                                    @foreach ($donations as $donation)
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title">{{ $donation['amount'] }}€ Donation</h5><br>
                                                    <p><strong>VIP:</strong> {{ $donation['vip'] }}</p>
                                                    <p><strong>Upload:</strong> {{ $donation['upload'] }}</p>
                                                    <p><strong>Bonus:</strong> {{ $donation['bonus'] }} points</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="POST">
    <!-- Indică faptul că este o achiziție pentru donație -->
    <input type="hidden" name="cmd" value="_donations">
    <!-- ID-ul sau email-ul contului PayPal unde vor fi trimise donațiile -->
    <input type="hidden" name="business" value="cristipnc@hotmail.com">
    <!-- Moneda -->
    <input type="hidden" name="currency_code" value="EUR">
    <!-- Descrierea donației -->
    <input type="hidden" name="item_name" value="Donation">
    <!-- Suma donației -->
    <input type="hidden" name="amount" value="{{ $donation['amount'] }}">
    <button class="btn btn-success">Donate {{ $donation['amount'] }}€</button>
</form>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <h4>Total Donation Options: <b>{{ $totalDonation }}€</b></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
