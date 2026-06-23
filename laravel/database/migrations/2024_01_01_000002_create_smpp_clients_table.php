<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('smpp_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('system_id')->unique();
            $table->text('password_hash');
            $table->string('sender_id', 11)->nullable();
            $table->string('ip_allowlist', 255)->nullable();
            $table->integer('throughput_limit')->default(100);
            $table->enum('bind_mode', ['transceiver', 'transmitter', 'receiver'])->default('transceiver');
            $table->enum('status', ['active', 'suspended', 'disabled'])->default('active');
            $table->timestamp('last_bind_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index('system_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('smpp_clients');
    }
};