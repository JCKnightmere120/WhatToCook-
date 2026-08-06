<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['name', 'description', 'instructions', 'cooking_tips', 'region', 'prep_time', 'cook_time', 'servings', 'meal_type', 'difficulty', 'image', 'image_source_url', 'image_attribution', 'calories', 'protein', 'carbs', 'fat', 'created_by'];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function favorites()
    {
        return $this->hasMany(RecipeFavorite::class);
    }

    public function reviews()
    {
        return $this->hasMany(RecipeReview::class);
    }
}
