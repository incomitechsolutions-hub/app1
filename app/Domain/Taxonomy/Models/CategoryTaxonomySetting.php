<?php

namespace App\Domain\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTaxonomySetting extends Model
{
    protected $table = 'category_taxonomy_settings';

    protected $fillable = [
        'default_new_category_status',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['default_new_category_status' => 'draft']
        );
    }
}
