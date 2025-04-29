<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read',
        'trigger_date',
        'read_at',
    ];

    protected $casts = [
        'read' => 'boolean',
        'trigger_date' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alertable()
    {
        return $this->morphTo();
    }
}
