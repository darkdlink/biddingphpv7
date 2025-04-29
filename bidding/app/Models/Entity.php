<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document',
        'type',
        'city',
        'state',
        'address',
        'contact_email',
        'contact_phone',
        'website',
    ];

    public function biddings()
    {
        return $this->hasMany(Bidding::class);
    }
}
