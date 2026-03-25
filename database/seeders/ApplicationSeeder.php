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
        $applications = require database_path('data/mock-applications.php');

        foreach ($applications as $application) {
            Application::updateOrCreate(
                ['id' => $application['id']],
                [
                    'name' => $application['name'],
                    'description' => $application['description'],
                ]
            );
        }
    }
}
