<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Services\InviteService;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function createInvite(Request $request, InviteService $service)
    {
        $service->create($request->user());

        return redirect()->route('invites.index')->with('success', 'Invitation created. It is valid for 14 days.');
    }

    public function showInvites(Request $request)
    {
        $invites = Invite::with('usedBy')->where('inviter_id', $request->user()->id)->latest()->get();
        $inviteCount = $request->user()->invites;

        return view('invites.index', compact('invites', 'inviteCount'));
    }

    public function deleteInvite(Request $request, int $invite, InviteService $service)
    {
        $service->revoke($request->user(), $invite);

        return redirect()->route('invites.index')->with('success', 'Invitation revoked. One invite has been returned to your balance.');
    }
}
