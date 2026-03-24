<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleState extends Model
{
    protected $fillable = [
        'module_key',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
