<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeFavorite extends Model
{
    protected $fillable = ['user_id', 'recipe_id'];
}
