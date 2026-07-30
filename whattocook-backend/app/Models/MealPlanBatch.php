<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlanBatch extends Model
{
    protected $fillable = [
        'user_id',
        'family_id',
        'start_date',
        'end_date',
        'status',
        'generation_options',
        'saved_at',
        'discarded_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'generation_options' => 'array',
            'saved_at' => 'datetime',
            'discarded_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }
}
