<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMessageSubmission;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Api\V1\SmsController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string|max:20',
            'from' => 'nullable|string|max:11',
            'message' => 'required|string|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = $request->user();

        $messageId = Str::uuid();
        $price = $this->calculatePrice($request->input('to'));

        ProcessMessageSubmission::dispatch([
            'tenant_id' => $tenant->id,
            'api_key_id' => $request->user('api')->id ?? null,
            'source' => $request->input('from'),
            'destination' => $request->input('to'),
            'content' => $request->input('message'),
            'price' => $price,
        ]);

        return response()->json([
            'message_id' => $messageId,
            'status' => 'queued',
        ], 202);
    }

    public function status(string $id, Request $request): JsonResponse
    {
        $message = \App\Models\Message::where('id', $id)
            ->where('tenant_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'message_id' => $message->id,
            'status' => $message->status,
            'price' => $message->price,
            'created_at' => $message->created_at,
            'delivered_at' => $message->delivered_at,
        ]);
    }

    private function calculatePrice(string $destination): float
    {
        return 0.01;
    }
}