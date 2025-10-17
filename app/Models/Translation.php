<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    protected $fillable = ['key', 'locale_id', 'content', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
