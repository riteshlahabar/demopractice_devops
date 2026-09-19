<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'salesman_id', 'period_start', 'period_end', 'sales_score',
        'collection_score', 'visit_score', 'overall_rating', 'kpis',
        'remarks', 'status', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'sales_score' => 'decimal:2',
            'collection_score' => 'decimal:2',
            'visit_score' => 'decimal:2',
            'overall_rating' => 'decimal:2',
            'kpis' => 'array',
        ];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
