<?php

namespace App\Http\Controllers\User;

use App\Models\SmppClient;
use Illuminate\Support\Str;

class SmppCredentialController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $client = SmppClient::where('tenant_id', $user->tenant_id)->first();
        
        // Password is only shown on first view (security)
        $showPassword = session('show_password', false);
        
        return view('user.smpp-credentials', compact('client', 'showPassword'));
    }

    public function reset()
    {
        $user = auth()->user();
        $client = SmppClient::where('tenant_id', $user->tenant_id)->first();
        
        if ($client) {
            $client->password_hash = \Hash::make(Str::random(16));
            $client->save();
        }
        
        return redirect()->route('smpp.credentials')->with('status', 'Password reset');
    }
}