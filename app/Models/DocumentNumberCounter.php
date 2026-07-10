<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberCounter extends Model
{
    const CREATED_AT = null;

    protected $fillable = [
        'document_type',
        'period',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }
}
