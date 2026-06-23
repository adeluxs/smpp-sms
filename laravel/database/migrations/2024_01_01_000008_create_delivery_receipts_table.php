<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('message_id')->constrained()->cascadeOnDelete();

            $table->string('provider_message_id');
            $table->string('dlr_message_id')->nullable();

            $table->enum('status', ['delivered', 'failed', 'expired', 'undelivered']);
            $table->timestamp('received_at')->useCurrent();

            $table->text('provider_response')->nullable();
            $table->json('parsed_data')->nullable();

            $table->timestamps();

            $table->index('provider_message_id');
            $table->index('received_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_receipts');
    }
};