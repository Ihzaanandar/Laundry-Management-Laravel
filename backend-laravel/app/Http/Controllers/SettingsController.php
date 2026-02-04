<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Settings;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Settings::firstOrCreate([], ['businessName' => 'My Laundry']);
        return $this->sendResponse($settings, 'Settings retrieved');
    }

    public function update(Request $request)
    {
        $settings = Settings::firstOrCreate([], ['businessName' => 'My Laundry']);
        $settings->update($request->all());
        return $this->sendResponse($settings, 'Settings updated');
    }
}
