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
        'quantity_value',
        'last_used_quantity',
        'last_usage_reason',
        'unit',
        'purchase_date',
        'expiry_date',
        'purchase_source',
        'storage_type',
        'freshness_condition',
        'freshness_status',
        'previous_freshness_status',
        'freshness_review_date',
        'freshness_confidence',
        'is_expiry_estimated',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'freshness_review_date' => 'date',
        'quantity_value' => 'decimal:3',
        'last_used_quantity' => 'decimal:3',
        'is_expiry_estimated' => 'boolean',
    ];  

    // Relationship to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family() { return $this->belongsTo(Family::class); }
}
