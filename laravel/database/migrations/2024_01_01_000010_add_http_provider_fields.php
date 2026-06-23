<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('type')->default('smpp')->after('name');
            $table->string('api_url')->nullable()->after('system_id');
            $table->text('api_key_encrypted')->nullable()->after('api_url');
        });
    }

    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['type', 'api_url', 'api_key_encrypted']);
        });
    }
};