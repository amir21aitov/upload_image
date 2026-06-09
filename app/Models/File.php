<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class File extends Model
{
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'compressed_size' => 'integer',
            'is_compressed' => 'boolean',
            'reference_count' => 'integer',
        ];
    }
}
