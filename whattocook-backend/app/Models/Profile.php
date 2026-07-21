<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['health_conditions', 'allergies', 'dietary_restrictions', 'likes', 'dislikes', 'visible_to_family'];

    protected function casts(): array
    {
        return [
            'health_conditions' => 'array', 'allergies' => 'array', 'dietary_restrictions' => 'array',
            'likes' => 'array', 'dislikes' => 'array', 'visible_to_family' => 'array',
        ];
    }
}
