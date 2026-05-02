<?php

namespace App\Models;

use Database\Factories\UrlFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'original_url', 'short_code', 'is_active'])]
class Url extends Model
{
    /** @use HasFactory<UrlFactory> */
    use HasFactory;

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(UrlClick::class);
    }

    /**
     * @return Attribute<int, never>
     */
    protected function totalClicks(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->clicks()->count());
    }

    /**
     * @return Attribute<int, never>
     */
    protected function viaDirectClicks(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->clicks()->where('via_qr', false)->count());
    }

    /**
     * @return Attribute<int, never>
     */
    protected function viaQrClicks(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->clicks()->where('via_qr', true)->count());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
