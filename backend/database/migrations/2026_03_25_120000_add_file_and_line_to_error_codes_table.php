<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('error_codes', function (Blueprint $table) {
            $table->string('file')->nullable()->after('name');
            $table->integer('line')->nullable()->after('file');
        });
    }

    public function down(): void
    {
        Schema::table('error_codes', function (Blueprint $table) {
            $table->dropColumn(['file', 'line']);
        });
    }
};
