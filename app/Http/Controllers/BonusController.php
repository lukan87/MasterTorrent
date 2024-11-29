<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller
{

    public function showShop()
{

    $user = Auth::user();
         if (!$user) {
        return redirect()->route('login')->with('error', 'You cannot access this page unless you are a member !');
    }
    return view('bonus.shop'); // Ensure this points to the correct view path
}

    public function buyUpload(Request $request)
    {
        $user = Auth::user();

        $amount = $request->input('amount');

        switch ($amount) {
            case '10':
                $cost = 500; // points for 10 GB
                $uploadAmount = 10 * 1024 * 1024 * 1024; // 10 GB in bytes
                break;
            case '25':
                $cost = 1000; // points for 25 GB
                $uploadAmount = 25 * 1024 * 1024 * 1024; // 25 GB in bytes
                break;
            case '100':
                $cost = 5000; // points for 100 GB
                $uploadAmount = 100 * 1024 * 1024 * 1024; // 100 GB in bytes
                break;
            default:
                return redirect()->back()->with('error', 'Invalid selection.');
        }

        if ($user->seedbonus < $cost) {
            return redirect()->back()->with('error', 'Not enough points.');
        }

        $user->uploaded += $uploadAmount;
        $user->seedbonus -= $cost;
        $user->save();

        return redirect()->back()->with('success', "You bought {$amount} GB of upload !");
    }
}

