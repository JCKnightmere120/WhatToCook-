<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class IngredientCatalog extends Model { protected $table = 'ingredient_catalog'; protected $fillable = ['canonical_name', 'aliases', 'category', 'is_approved', 'default_units']; protected function casts(): array { return ['aliases' => 'array', 'default_units' => 'array', 'is_approved' => 'boolean']; } }
