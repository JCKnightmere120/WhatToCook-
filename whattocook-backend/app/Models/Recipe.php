<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'name', 'description', 'region', 'prep_time', 'cook_time', 'servings', 'meal_type', 'difficulty', 'image',
        'calories', 'protein', 'carbs', 'fat', 'created_by'
    ];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
