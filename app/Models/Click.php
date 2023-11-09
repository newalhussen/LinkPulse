<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    public $timestamps = false;

    protected $fillable = ['link_id', 'clicked_at', 'referrer_host', 'device'];

    protected function casts(): array
    {
        return ['clicked_at' => 'datetime'];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
