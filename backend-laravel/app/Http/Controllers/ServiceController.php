<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('isActive', true)->get();
        return $this->sendResponse($services, 'Services retrieved');
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:KILOAN,SATUAN',
            'price' => 'required|numeric',
            'estimatedTime' => 'required|integer'
        ]);

        $service = Service::create($fields);
        return $this->sendResponse($service, 'Service created');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $service->update($request->all());
        return $this->sendResponse($service, 'Service updated');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['isActive' => false]);
        return $this->sendResponse([], 'Service deleted');
    }
}
