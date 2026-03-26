<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

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
