<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ErrorCode;

class ErrorCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ErrorCode::updateOrCreate(
            ['id' => 1],
            [
                'code'        => 'ERR-001',
                'application_id' => 1,
                'name'        => 'Error Code 1',
                'description' => 'Description 1',
                'severity'    => 'low',
            ]
        );
    }
}
