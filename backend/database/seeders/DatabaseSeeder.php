<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * UserSeeder omitido: 'users' es una vista FDW de solo lectura sobre v_app_users (Odoo).
     */
    public function run(): void
    {
        $this->call([
            ApplicationSeeder::class,
            ErrorCodeSeeder::class,
            LogSeeder::class,
            ArchivedLogSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
