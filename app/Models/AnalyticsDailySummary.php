<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailySummary extends Model
{
    /** @use HasFactory<\Database\Factories\AnalyticsDailySummaryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'summary_date',
        'event_type',
        'page_url',
        'device_type',
        'country',
        'events_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'summary_date' => 'date',
            'events_count' => 'integer',
        ];
    }
}
