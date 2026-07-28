<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('pantry_items', function (Blueprint $table) { $table->decimal('last_used_quantity', 12, 3)->nullable()->after('quantity_value'); $table->string('previous_freshness_status')->nullable()->after('freshness_status'); }); }
    public function down(): void { Schema::table('pantry_items', function (Blueprint $table) { $table->dropColumn(['last_used_quantity', 'previous_freshness_status']); }); }
};
