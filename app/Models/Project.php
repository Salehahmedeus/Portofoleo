<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'summary',
        'featured',
        'sort_order',
        'thumbnail_path',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProjectDetail::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
