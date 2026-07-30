<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientPackageConversion extends Model
{
    protected $fillable = ['user_id', 'family_id', 'ingredient_name', 'package_unit', 'amount_per_package', 'amount_unit'];
    protected function casts(): array { return ['amount_per_package' => 'decimal:3']; }
}
