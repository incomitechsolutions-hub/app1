<?php

namespace App\Domain\CourseCatalog\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCoupon extends Model
{
    protected $table = 'course_coupons';

    protected $fillable = [
        'code',
        'discount_percent',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
