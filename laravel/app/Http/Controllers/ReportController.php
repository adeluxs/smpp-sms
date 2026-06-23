<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function messages(Request $request)
    {
        if ($request->wantsJson()) {
            $tenant = $request->user();
            $query = Message::where('tenant_id', $tenant->id);
            $messages = $query->paginate(50);
            return response()->json($messages);
        }

        $tenant = auth()->user()->tenant;
        $messages = Message::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('user.messages', compact('messages'));
    }

    public function summary(Request $request)
    {
        $tenant = $request->user();

        $stats = Message::where('tenant_id', $tenant->id)
            ->selectRaw('status, count(*) as count, sum(price) as total')
            ->groupBy('status')
            ->get();

        return response()->json(['summary' => $stats]);
    }
}