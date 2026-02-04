<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Service extends Model
{
    use HasFactory, HasUuids;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['name', 'type', 'price', 'estimatedTime', 'isActive'];

    protected $casts = [
        'price' => 'decimal:2',
        'isActive' => 'boolean'
    ];
}
