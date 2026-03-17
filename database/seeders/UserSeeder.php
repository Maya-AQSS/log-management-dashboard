<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'name'        => 'Admin',
                'email'       => 'admin@example.com',
                'external_id' => 'admin-mock',
            ]
        );

        User::updateOrCreate(
            ['id' => 2],
            [
                'name'        => 'User 2',
                'email'       => 'user2@example.com',
                'external_id' => 'user-mock',
            ]
        );
    }
}
