<?php

namespace App\Models;

use Database\Factories\UrlClickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['url_id', 'clicked_at', 'ip_address', 'user_agent', 'referrer', 'via_qr'])]
class UrlClick extends Model
{
    /** @use HasFactory<UrlClickFactory> */
    use HasFactory;

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'via_qr' => 'boolean',
            'clicked_at' => 'datetime',
        ];
    }
}
