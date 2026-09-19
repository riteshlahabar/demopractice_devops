<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductHomepageSection extends Model
{
    protected $fillable = [
        'section_key', 'title', 'subtitle', 'section_type', 'layout_type', 'source_type', 'category_id',
        'product_limit', 'item_limit', 'image_size_note', 'sort_order', 'start_at', 'end_at', 'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'product_limit' => 'integer',
            'item_limit' => 'integer',
            'sort_order' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductHomepageSectionItem::class, 'section_id')->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    protected static function booted(): void
    {
        static::creating(function (self $section): void {
            if (blank($section->section_key)) {
                $base = Str::slug((string) ($section->title ?: $section->section_type ?: 'section'));
                $base = $base ?: 'section';
                $section->section_key = $base.'-'.time().'-'.random_int(100, 999);
            }
        });

        static::updating(function (self $section): void {
            if (blank($section->section_key)) {
                $base = Str::slug((string) ($section->title ?: $section->section_type ?: 'section'));
                $base = $base ?: 'section';
                $section->section_key = $base.'-'.time().'-'.random_int(100, 999);
            }
        });
    }

    public function getSectionTypeNameAttribute(): string
    {
        return trim(ucwords(str_replace('_', ' ', (string) $this->section_type)));
    }

    public function getLayoutTypeNameAttribute(): string
    {
        return trim(ucwords(str_replace('_', ' ', (string) $this->layout_type)));
    }
}
