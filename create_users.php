<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::whereIn('email', ['admin@example.com', 'user1@example.com'])->delete();

// Akun Admin
$admin = User::create([
    'name' => 'Administrator',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
    'role' => 'admin',
    'phone' => '081234567890',
    'company' => 'PT Teladan Prima Agro'
]);
echo "Admin created: admin@example.com / password123\n";

// Akun User 1
$user = User::create([
    'name' => 'User Satu',
    'email' => 'user1@example.com',
    'password' => Hash::make('password123'),
    'role' => 'user',
    'phone' => '081234567891',
    'company' => 'PT Teladan Prima Agro'
]);
echo "User 1 created: user1@example.com / password123\n";
