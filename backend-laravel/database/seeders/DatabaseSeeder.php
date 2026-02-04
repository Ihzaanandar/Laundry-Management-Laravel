<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin exists
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@laundry.com',
                'password' => Hash::make('password'),
                'name' => 'Super Owner',
                'role' => 'OWNER',
                'isActive' => true
            ]);
            $this->command->info('Default Admin User Created: admin / password');
        }

        if (!User::where('username', 'owner')->exists()) {
            User::create([
                'username' => 'owner',
                'email' => 'owner@laundry.com',
                'password' => Hash::make('owner123'),
                'name' => 'Owner Laundry',
                'role' => 'OWNER',
                'isActive' => true
            ]);
            $this->command->info('Owner User Created: owner / owner123');
        }

        if (!User::where('username', 'kasir')->exists()) {
            User::create([
                'username' => 'kasir',
                'email' => 'kasir@laundry.com',
                'password' => Hash::make('kasir123'),
                'name' => 'Kasir Laundry',
                'role' => 'KASIR',
                'isActive' => true
            ]);
            $this->command->info('Kasir User Created: kasir / kasir123');
        }

        // Services
        if (\App\Models\Service::count() === 0) {
            \App\Models\Service::create([
                'name' => 'Cuci Komplit (Kiloan)',
                'type' => 'KILOAN',
                'price' => 7000,
                'estimatedTime' => 24,
                'isActive' => true
            ]);
            \App\Models\Service::create([
                'name' => 'Cuci Kering (Kiloan)',
                'type' => 'KILOAN',
                'price' => 5000,
                'estimatedTime' => 24,
                'isActive' => true
            ]);
            \App\Models\Service::create([
                'name' => 'Setrika Saja (Kiloan)',
                'type' => 'KILOAN',
                'price' => 4500,
                'estimatedTime' => 24,
                'isActive' => true
            ]);
            \App\Models\Service::create([
                'name' => 'Cuci Satuan (Jaket)',
                'type' => 'SATUAN',
                'price' => 15000,
                'estimatedTime' => 48,
                'isActive' => true
            ]);
            $this->command->info('Dummy Services Created');
        }

        // Customers
        if (\App\Models\Customer::count() === 0) {
            \App\Models\Customer::create([
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 45'
            ]);
            \App\Models\Customer::create([
                'name' => 'Siti Aminah',
                'phone' => '089876543210',
                'address' => 'Jl. Mawar Melati No. 12'
            ]);
            $this->command->info('Dummy Customers Created');
        }
    }
}
