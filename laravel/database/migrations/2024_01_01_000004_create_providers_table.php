<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(2775);
            $table->text('password_encrypted');
            $table->string('system_id');
            $table->integer('throughput_limit')->default(1000);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->json('capabilities')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('providers');
    }
};