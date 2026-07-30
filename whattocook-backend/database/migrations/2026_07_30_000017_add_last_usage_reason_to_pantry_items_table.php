<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->string('last_usage_reason', 500)->nullable()->after('last_used_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->dropColumn('last_usage_reason');
        });
    }
};
