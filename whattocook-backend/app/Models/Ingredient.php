<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'quantity', 'unit', 'is_substitute'];
    protected function casts(): array { return ['is_substitute' => 'boolean']; }
}
