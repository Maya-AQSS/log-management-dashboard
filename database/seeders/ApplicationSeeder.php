<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Application::updateOrCreate(
            ['id' => 1],
            [
                'name'        => 'Application 1',
                'description' => 'Description 1',
            ]
        );
    }
}
