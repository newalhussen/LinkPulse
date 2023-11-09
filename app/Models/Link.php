<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'title', 'target_url', 'is_active', 'expires_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'expires_at' => 'datetime', 'clicks_count' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Whether visitors are currently redirected. */
    public function isLive(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function status(): string
    {
        return ! $this->is_active ? 'disabled' : ($this->isExpired() ? 'expired' : 'active');
    }

    public function shortUrl(): string
    {
        return rtrim(config('app.url'), '/').'/'.$this->code;
    }
}
