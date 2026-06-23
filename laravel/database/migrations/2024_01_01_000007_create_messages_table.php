<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('smpp_client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('api_key_id')->nullable()->constrained()->nullOnDelete();

            $table->string('source', 11)->nullable();
            $table->string('destination', 20);
            $table->text('content');
            $table->integer('segments')->default(1);
            $table->enum('encoding', ['GSM7', 'UCS2', 'BINARY'])->default('GSM7');

            $table->string('internal_message_id')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('smpp_client_message_id')->nullable();

            $table->enum('status', [
                'queued',
                'submitted',
                'accepted',
                'delivered',
                'failed',
                'expired',
                'rejected',
                'undelivered'
            ])->default('queued');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->decimal('price', 10, 4)->nullable();
            $table->decimal('cost', 10, 4)->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['destination', 'created_at']);
            $table->index('internal_message_id');
            $table->index('provider_message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};