<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    protected $fillable = ['user_id', 'family_id', 'recipe_id', 'planned_date', 'meal_type', 'completed_at', 'consumed_items'];
    protected function casts(): array { return ['planned_date' => 'date', 'completed_at' => 'datetime', 'consumed_items' => 'array']; }
    public function recipe() { return $this->belongsTo(Recipe::class); }
}
