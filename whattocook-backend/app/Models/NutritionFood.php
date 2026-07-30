<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionFood extends Model
{
    protected $table = 'nutrition_foods';

    protected $fillable = ['fdc_id', 'description', 'normalized_name', 'source', 'nutrients', 'raw_data', 'fetched_at'];

    protected function casts(): array
    {
        return ['nutrients' => 'array', 'raw_data' => 'array', 'fetched_at' => 'datetime'];
    }
}
