<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'quantity', 'unit', 'nutrition_food_id', 'nutrition_grams', 'is_substitute'];

    protected function casts(): array
    {
        return ['is_substitute' => 'boolean', 'nutrition_grams' => 'decimal:3'];
    }

    public function nutritionFood()
    {
        return $this->belongsTo(NutritionFood::class);
    }
}
