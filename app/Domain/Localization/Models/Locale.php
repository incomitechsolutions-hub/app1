<?php

namespace App\Domain\Localization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locale extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class, 'default_locale_id');
    }
}
