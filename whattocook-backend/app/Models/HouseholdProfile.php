<?php

namespace App\Models;

use App\Services\ChildMealPlanner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdProfile extends Model
{
    protected $appends = ['age_band'];
    protected $fillable = [
        'family_id',
        'user_id',
        'name',
        'relation',
        'sex',
        'birth_date',
        'height_cm',
        'weight_kg',
        'activity_level',
        'goal',
        'health_conditions',
        'allergies',
        'dietary_restrictions',
        'likes',
        'dislikes',
        'visible_to_family',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'health_conditions' => 'array',
            'allergies' => 'array',
            'dietary_restrictions' => 'array',
            'likes' => 'array',
            'dislikes' => 'array',
            'visible_to_family' => 'array',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The API derives this from the stored date; clients never submit an age. */
    public function getAgeBandAttribute(): ?string
    {
        return app(ChildMealPlanner::class)->ageBand($this->birth_date, now());
    }
}
