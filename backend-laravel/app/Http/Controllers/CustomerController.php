<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return $this->sendResponse($customers, 'Customers retrieved');
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $customer = Customer::create($fields);
        return $this->sendResponse($customer, 'Customer created');
    }

    public function show($id)
    {
        $customer = Customer::with('orders')->findOrFail($id);
        return $this->sendResponse($customer, 'Customer retrieved');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $fields = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $customer->update($fields);
        return $this->sendResponse($customer, 'Customer updated');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if customer has orders
        if ($customer->orders()->count() > 0) {
            return $this->sendError('Cannot delete customer with existing orders', [], 400);
        }

        $customer->delete();
        return $this->sendResponse(null, 'Customer deleted');
    }
}
