<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DLRReceived implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Message $message
    ) {}

    public function handle(WalletService $walletService): void
    {
        Log::info('DLR received for message', ['message_id' => $this->message->id, 'status' => $this->message->status]);

        if (in_array($this->message->status, ['failed', 'expired', 'undelivered'])) {
            $walletService->release($this->message->tenant_id, $this->message->price);
        }

        // TODO: Send callback to original requester
    }
}