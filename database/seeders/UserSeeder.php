<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Administrator
        User::updateOrCreate(
            ['email' => 'admin@admin.ad'],
            [
                'name' => 'admin',
                'password' => Hash::make('adminadmin'),
                'is_admin' => true,
            ]
        );

        // Zwykły użytkownik
        User::updateOrCreate(
            ['email' => 'user@user.us'],
            [
                'name' => 'user',
                'password' => Hash::make('useruser'),
                'is_admin' => false,
            ]
        );
    }
}
