<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return $this->sendResponse($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:OWNER,KASIR',
            'name' => 'nullable|string'
        ]);

        $fields['password'] = Hash::make($fields['password']);
        $fields['isActive'] = true;

        $user = User::create($fields);

        return $this->sendResponse($user, 'User created successfully');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $fields = $request->validate([
            'username' => ['required', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:OWNER,KASIR',
            'name' => 'nullable|string'
        ]);

        if ($request->has('password') && $request->password) {
            $fields['password'] = Hash::make($request->password);
        }

        $user->update($fields);
        return $this->sendResponse($user, 'User updated successfully');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return $this->sendResponse([], 'User deleted successfully');
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->isActive = !$user->isActive;
        $user->save();
        return $this->sendResponse($user, 'User status updated successfully');
    }
}
