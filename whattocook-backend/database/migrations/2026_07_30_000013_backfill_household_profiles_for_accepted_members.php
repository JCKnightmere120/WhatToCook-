<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Give existing accepted accounts the diner profile new accounts receive. */
    public function up(): void
    {
        $now = now();
        DB::table('family_members')
            ->join('families', 'families.id', '=', 'family_members.family_id')
            ->join('users', 'users.id', '=', 'family_members.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
            ->where('family_members.status', 'accepted')
            ->select('family_members.family_id', 'family_members.user_id', 'families.owner_id', 'users.name', 'profiles.health_conditions', 'profiles.allergies', 'profiles.dietary_restrictions', 'profiles.likes', 'profiles.dislikes', 'profiles.visible_to_family')
            ->orderBy('family_members.id')
            ->each(function ($member) use ($now) {
                $exists = DB::table('household_profiles')->where('family_id', $member->family_id)->where('user_id', $member->user_id)->exists();
                if (! $exists) {
                    DB::table('household_profiles')->insert([
                        'family_id' => $member->family_id, 'user_id' => $member->user_id, 'name' => $member->name,
                        'relation' => $member->owner_id === $member->user_id ? 'Household owner' : 'Family account member',
                        'health_conditions' => $member->health_conditions, 'allergies' => $member->allergies,
                        'dietary_restrictions' => $member->dietary_restrictions, 'likes' => $member->likes,
                        'dislikes' => $member->dislikes, 'visible_to_family' => $member->visible_to_family,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void {}
};
