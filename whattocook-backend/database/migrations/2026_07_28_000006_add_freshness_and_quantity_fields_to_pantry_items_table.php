<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->decimal('quantity_value', 12, 3)->nullable()->after('quantity');
            $table->string('purchase_source')->default('unknown')->after('purchase_date');
            $table->string('storage_type')->default('unknown')->after('purchase_source');
            $table->string('freshness_condition')->default('unknown')->after('storage_type');
            $table->string('freshness_status')->default('review')->after('freshness_condition');
            $table->date('freshness_review_date')->nullable()->after('freshness_status');
            $table->string('freshness_confidence')->default('low')->after('freshness_review_date');
            $table->boolean('is_expiry_estimated')->default(true)->after('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_value', 'purchase_source', 'storage_type', 'freshness_condition',
                'freshness_status', 'freshness_review_date', 'freshness_confidence',
                'is_expiry_estimated',
            ]);
        });
    }
};
