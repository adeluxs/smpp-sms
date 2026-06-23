<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Internal\DLRController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_message_id' => 'required|string',
            'status' => 'required|in:delivered,failed,expired,undelivered',
            'received_at' => 'sometimes|date',
        ]);

        $message = Message::where('provider_message_id', $validated['provider_message_id'])
            ->first();

        if (!$message) {
            Log::warning('Unknown provider message ID', ['provider_message_id' => $validated['provider_message_id']]);
            return response()->json(['status' => 'unknown'], 200);
        }

        $oldStatus = $message->status;
        $message->status = $validated['status'];
        $message->delivered_at = $validated['status'] === 'delivered' ? now() : $message->delivered_at;
        $message->failed_at = in_array($validated['status'], ['failed', 'expired', 'undelivered']) ? now() : $message->failed_at;
        $message->save();

        if ($validated['status'] === 'delivered') {
            \App\Jobs\DLRReceived::dispatch($message);
        }

        Log::info('Message status updated', [
            'message_id' => $message->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);

        return response()->json(['status' => 'updated']);
    }
}