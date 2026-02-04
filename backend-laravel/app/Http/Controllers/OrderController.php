<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StatusHistory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'user', 'items.service', 'payment']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('paymentStatus') && $request->paymentStatus) {
            $query->where('paymentStatus', $request->paymentStatus);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('orderNumber', 'ILIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('createdAt', 'desc')->get();
        return $this->sendResponse($orders, 'Orders retrieved');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customerId' => 'nullable|exists:customers,id',
            'customerName' => 'required_without:customerId|string',
            'customerPhone' => 'nullable|string',
            'customerAddress' => 'nullable|string',
            'items' => 'required|array',
            'items.*.serviceId' => 'required|exists:services,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.price' => 'required|numeric', // Or fetch from DB to be safe
            'payment' => 'required|array',
            'payment.amount' => 'required|numeric',
            'payment.method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $order = DB::transaction(function () use ($data, $request) {
                // Create New Customer if needed
                if (empty($data['customerId'])) {
                    $customer = \App\Models\Customer::create([
                        'name' => $data['customerName'],
                        'phone' => $data['customerPhone'] ?? null,
                        'address' => $data['customerAddress'] ?? '-',
                    ]);
                    $data['customerId'] = $customer->id;
                }

                // Calculate Total
                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }

                // Create Order
                $order = Order::create([
                    'orderNumber' => 'ORD-' . strtoupper(Str::random(8)),
                    'customerId' => $data['customerId'],
                    'userId' => $request->user()->id,
                    'status' => 'DITERIMA',
                    'paymentStatus' => isset($data['payment']['amount']) && $data['payment']['amount'] > 0 ? 'SUDAH_BAYAR' : 'BELUM_BAYAR',
                    'totalAmount' => $totalAmount,
                    'notes' => $data['notes'] ?? null
                ]);

                // Create Items
                foreach ($data['items'] as $item) {
                    OrderItem::create([
                        'orderId' => $order->id,
                        'serviceId' => $item['serviceId'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['quantity'] * $item['price']
                    ]);
                }

                // Create Payment
                Payment::create([
                    'orderId' => $order->id,
                    'amount' => $data['payment']['amount'],
                    'method' => $data['payment']['method'],
                ]);

                // Log Status
                StatusHistory::create([
                    'orderId' => $order->id,
                    'status' => 'DITERIMA',
                    'changedBy' => $request->user()->username
                ]);

                return $order->load('items', 'payment');
            });

            return $this->sendResponse($order, 'Order created successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order Creation Failed: ' . $e->getMessage());
            return $this->sendError('Failed to create order: ' . $e->getMessage(), [], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'user', 'items.service', 'payment', 'statusHistory'])->findOrFail($id);
        return $this->sendResponse($order, 'Order retrieved');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $request->validate(['status' => 'required|string']);

        $order->update(['status' => $request->status]);

        StatusHistory::create([
            'orderId' => $order->id,
            'status' => $request->status,
            'changedBy' => $request->user()->username
        ]);

        return $this->sendResponse($order, 'Order status updated');
    }

    public function updatePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['paymentStatus' => 'SUDAH_BAYAR']);

        // Update payment record
        Payment::where('orderId', $id)->update(['paidAt' => now()]);

        return $this->sendResponse($order, 'Payment updated');
    }

    public function getReceipt($id)
    {
        Order::findOrFail($id); // Check existence
        $settings = \App\Models\Settings::first();

        return $this->sendResponse([
            'settings' => $settings
        ], 'Receipt data retrieved');
    }
}
