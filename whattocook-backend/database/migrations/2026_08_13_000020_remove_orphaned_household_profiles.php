<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep dependents (null user_id), but remove profile records left by
        // accounts that no longer have an accepted membership.
        DB::table('household_profiles')->whereNotNull('user_id')->whereNotExists(function ($members) {
            $members->selectRaw('1')->from('family_members')
                ->whereColumn('family_members.user_id', 'household_profiles.user_id')
                ->whereColumn('family_members.family_id', 'household_profiles.family_id')
                ->where('family_members.status', 'accepted');
        })->delete();
    }

    public function down(): void
    {
        // Removed stale profile data cannot be reconstructed safely.
    }
};
