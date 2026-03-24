<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AlternateLocale extends Model
{
    protected $table = 'alternate_locales';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'locale_code',
        'target_url',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
