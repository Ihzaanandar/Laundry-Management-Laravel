<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderItem extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = false;
    protected $fillable = ['orderId', 'serviceId', 'quantity', 'price', 'subtotal'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }
}
