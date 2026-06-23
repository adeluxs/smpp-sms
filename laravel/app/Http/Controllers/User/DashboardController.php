<?php

namespace App\Http\Controllers\User;

use App\Models\Wallet;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $wallet = Wallet::where('tenant_id', $user->tenant_id)->first();

        $stats = Message::where('tenant_id', $user->tenant_id)
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('count(*) as sent, 
                         sum(case when status = "delivered" then 1 else 0 end) as delivered')
            ->first();

        $deliveryRate = $stats->sent > 0 
            ? round(($stats->delivered / $stats->sent) * 100, 1) 
            : 0;

        return view('dashboard.user', compact('wallet', 'stats', 'deliveryRate'));
    }
}