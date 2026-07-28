<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = ['name', 'owner_id', 'join_code'];

    protected static function booted(): void
    {
        static::creating(function (Family $family) {
            if (! $family->join_code) {
                do { $family->join_code = strtoupper(Str::random(8)); } while (static::where('join_code', $family->join_code)->exists());
            }
        });
    }
    public function members() { return $this->hasMany(FamilyMember::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }

    public function householdProfiles(): HasMany
    {
        return $this->hasMany(HouseholdProfile::class);
    }
}
