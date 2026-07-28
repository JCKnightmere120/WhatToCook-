<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            // A profile can represent a child/dependent without an account, or a registered family member.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('relation')->nullable();
            $table->string('sex', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('activity_level')->nullable();
            $table->string('goal')->nullable();
            $table->json('health_conditions')->nullable();
            $table->json('allergies')->nullable();
            $table->json('dietary_restrictions')->nullable();
            $table->json('likes')->nullable();
            $table->json('dislikes')->nullable();
            $table->json('visible_to_family')->nullable();
            $table->timestamps();

            $table->unique(['family_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_profiles');
    }
};
