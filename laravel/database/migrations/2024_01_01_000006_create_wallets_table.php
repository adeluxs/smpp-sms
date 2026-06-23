<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 4)->default(0);
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->decimal('credit_limit', 12, 4)->default(0);
            $table->decimal('low_balance_threshold', 12, 4)->default(10);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'balance']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallets');
    }
};