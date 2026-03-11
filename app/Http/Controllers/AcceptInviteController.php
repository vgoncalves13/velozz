<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AcceptInviteController extends Controller
{
    /**
     * Show the accept invite form.
     */
    public function show(string $token)
    {
        $user = User::where('invite_token', $token)
            ->where('invite_expires_at', '>', now())
            ->firstOrFail();

        return view('auth.accept-invite', compact('user', 'token'));
    }

    /**
     * Process the invitation acceptance.
     */
    public function store(Request $request, string $token)
    {
        $user = User::where('invite_token', $token)
            ->where('invite_expires_at', '>', now())
            ->firstOrFail();

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'status' => 'active',
            'invite_token' => null,
            'invite_expires_at' => null,
        ]);

        Auth::login($user);

        // Redirect to appropriate panel based on role
        if ($user->isAdminMaster()) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return redirect()->route('filament.client.pages.dashboard', ['tenant' => $user->tenant]);
    }
}
