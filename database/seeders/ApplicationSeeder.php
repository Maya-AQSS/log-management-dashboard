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

        Application::updateOrCreate(
            ['id' => 2],
            [
                'name'        => 'Application 2',
                'description' => 'Description 2',
            ]
        );

        Application::updateOrCreate(
            ['id' => 3],
            [
                'name'        => 'Application 3',
                'description' => 'Description 3',
            ]
        );
    }
}
