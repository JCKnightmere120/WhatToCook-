<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeReview extends Model
{
    protected $fillable = ['user_id', 'recipe_id', 'rating', 'review'];
    public function user() { return $this->belongsTo(User::class); }
}
