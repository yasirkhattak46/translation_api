<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['slug', 'label'];

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(Translation::class);
    }
}
