<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PantryItem extends Model
{
    protected $fillable = [
        'user_id',
        'family_id',
        'name',
        'quantity',
        'unit',
        'purchase_date',
        'expiry_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];  

    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family() { return $this->belongsTo(Family::class); }
}
