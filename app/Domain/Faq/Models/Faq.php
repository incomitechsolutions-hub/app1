<?php

namespace App\Domain\Faq\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'question',
        'answer',
        'sort_order',
        'is_schema_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_schema_enabled' => 'boolean',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
