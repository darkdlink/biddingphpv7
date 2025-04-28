<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    protected $fillable = [
        'title', 'description', 'notice_number', 'status_id',
        'entity', 'estimated_value', 'publication_date',
        'opening_date', 'closing_date', 'source_url', 'additional_info'
    ];

    protected $casts = [
        'publication_date' => 'datetime',
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
        'additional_info' => 'json',
        'estimated_value' => 'decimal:2'
    ];

    public function status()
    {
        return $this->belongsTo(BiddingStatus::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
