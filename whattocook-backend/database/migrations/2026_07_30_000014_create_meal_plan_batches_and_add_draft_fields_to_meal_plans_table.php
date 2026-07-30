<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plan_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('draft');
            $table->json('generation_options')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('discarded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['family_id', 'status']);
        });

        Schema::table('meal_plans', function (Blueprint $table) {
            $table->foreignId('meal_plan_batch_id')->nullable()->after('family_id')->constrained('meal_plan_batches')->nullOnDelete();
            $table->string('status', 20)->default('scheduled')->after('meal_plan_batch_id');
            $table->index(['status', 'planned_date']);
        });

        // Existing rows predate draft batches and are already saved schedules.
        DB::table('meal_plans')->whereNull('status')->update(['status' => 'scheduled']);
    }

    public function down(): void
    {
        Schema::table('meal_plans', function (Blueprint $table) {
            $table->dropIndex(['status', 'planned_date']);
            $table->dropColumn('status');
            $table->dropConstrainedForeignId('meal_plan_batch_id');
        });

        Schema::dropIfExists('meal_plan_batches');
    }
};
