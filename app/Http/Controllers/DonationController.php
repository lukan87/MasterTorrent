<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        $donationOptions = [
            [
                'amount' => 5,
                'vip' => '4 weeks',
                'donor' => '4 weeks',
                'freeleech' => '4 weeks',
                'upload' => '50G',
                'bonus_points' => 500,
                'access' => '4 weeks',
            ],
            [
                'amount' => 7,
                'vip' => '6 weeks',
                'donor' => '6 weeks',
                'freeleech' => '6 weeks',
                'upload' => '150G',
                'bonus_points' => 1500,
                'access' => '6 weeks',
            ],
            // Add other options similarly
        ];

        return view('donate', compact('donationOptions'));
    }
}
