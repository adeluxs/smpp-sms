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

class ProcessMessageSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $messageData,
        public string $routingStrategy = 'failover'
    ) {}

    public function handle(WalletService $walletService): void
    {
        $price = $this->calculatePrice();

        if (!$walletService->reserve($this->messageData['tenant_id'], $price)) {
            $this->reject('insufficient_balance');
            return;
        }

        $message = $this->createMessage($price);
        $this->publishToQueue($message);
    }

    private function calculatePrice(): float
    {
        $basePrice = 0.01;
        $segments = $this->calculateSegments($this->messageData['content']);
        return $basePrice * $segments;
    }

    private function createMessage(float $price): Message
    {
        return Message::create([
            'tenant_id' => $this->messageData['tenant_id'],
            'smpp_client_id' => $this->messageData['smpp_client_id'] ?? null,
            'api_key_id' => $this->messageData['api_key_id'] ?? null,
            'source' => $this->messageData['source'],
            'destination' => $this->messageData['destination'],
            'content' => $this->messageData['content'],
            'encoding' => $this->detectEncoding($this->messageData['content']),
            'segments' => $this->calculateSegments($this->messageData['content']),
            'status' => 'queued',
            'price' => $price,
        ]);
    }

    private function detectEncoding(string $content): string
    {
        return preg_match('/[^\x00-\x7F]/', $content) ? 'UCS2' : 'GSM7';
    }

    private function calculateSegments(string $content): int
    {
        $encoding = $this->detectEncoding($content);
        $limit = $encoding === 'UCS2' ? 70 : 160;
        return (int) ceil(mb_strlen($content, '8bit') / $limit);
    }

    private function publishToQueue(Message $message): void
    {
        Log::info('Message queued for processing', ['message_id' => $message->id]);
    }

    private function reject(string $reason): void
    {
        Log::warning('Message rejected', [
            'reason' => $reason,
            'data' => $this->messageData,
        ]);
    }
}