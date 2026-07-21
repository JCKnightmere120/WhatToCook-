<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    protected $fillable = ['user_id', 'family_id', 'recipe_id', 'planned_date', 'meal_type'];
    protected function casts(): array { return ['planned_date' => 'date']; }
    public function recipe() { return $this->belongsTo(Recipe::class); }
}
