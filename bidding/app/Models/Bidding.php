<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'reference_number',
        'description',
        'entity_id',
        'estimated_value',
        'notice_link',
        'status',
        'publication_date',
        'opening_date',
        'closing_date',
        'requirements',
        'metadata',
        'internal_notes',
    ];

    protected $casts = [
        'requirements' => 'array',
        'metadata' => 'array',
        'publication_date' => 'datetime',
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function alerts()
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
}
