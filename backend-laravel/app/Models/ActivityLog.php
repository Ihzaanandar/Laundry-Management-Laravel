<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = false; // We use createdAt only manually or by default
    protected $fillable = ['userId', 'action', 'entity', 'entityId', 'details', 'createdAt'];

    protected $casts = [
        'details' => 'array',
        'createdAt' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
