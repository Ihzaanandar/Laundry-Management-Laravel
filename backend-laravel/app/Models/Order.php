<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasFactory, HasUuids;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = ['orderNumber', 'customerId', 'userId', 'status', 'paymentStatus', 'totalAmount', 'notes'];

    protected $casts = [
        'totalAmount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'orderId');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'orderId');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHistory::class, 'orderId')->orderBy('changedAt', 'desc');
    }
}
