<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['priority', 'least_cost', 'round_robin', 'failover']);
            $table->string('prefix', 10)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->integer('priority')->default(100);
            $table->integer('max_throughput')->default(1000);
            $table->foreignUuid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'enabled']);
            $table->index(['prefix', 'country_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('routes');
    }
};