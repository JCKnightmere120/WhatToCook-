<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['name', 'owner_id'];

    public function members() { return $this->hasMany(FamilyMember::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
}
