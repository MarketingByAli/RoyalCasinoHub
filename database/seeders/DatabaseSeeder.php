<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SeoSettingSeeder::class);

        if (!User::where('email', 'admin@royalcasinohub.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@royalcasinohub.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }
    }
}
