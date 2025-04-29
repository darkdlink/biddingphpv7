<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidding_id',
        'status',
        'proposed_value',
        'cost_estimate',
        'profit_margin',
        'items',
        'notes',
        'document_path',
        'submission_date',
        'submission_protocol',
    ];

    protected $casts = [
        'items' => 'array',
        'submission_date' => 'datetime',
    ];

    public function bidding()
    {
        return $this->belongsTo(Bidding::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
