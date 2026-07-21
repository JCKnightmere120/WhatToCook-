<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealHistory extends Model
{
    protected $table = 'meal_history';
    protected $fillable = ['user_id', 'family_id', 'recipe_id', 'prepared_at', 'servings', 'notes'];
    protected function casts(): array { return ['prepared_at' => 'date']; }
    public function recipe() { return $this->belongsTo(Recipe::class); }
}
