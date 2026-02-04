<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StatusHistory extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'status_history';
    public $timestamps = false;
    protected $fillable = ['orderId', 'status', 'changedBy', 'changedAt'];

    protected $casts = [
        'changedAt' => 'datetime'
    ];
}
