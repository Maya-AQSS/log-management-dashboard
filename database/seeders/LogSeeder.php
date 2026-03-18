<?php

namespace Database\Seeders;

use App\Models\Log;
use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        Log::updateOrCreate(
            ['id' => 1],
            [
                'application_id' => 1,
                'error_code_id' => 1,
                'severity' => 'low',
                'message' => 'Seed: log de prueba',
                'file' => 'seed.log',
                'line' => 10,
                'metadata' => ['seed' => true, 'source' => 'LogSeeder'],
                'matched_archived_log_id' => null,
                'resolved' => false,
                'created_at' => now(),
            ]
        );

        Log::updateOrCreate(
            ['id' => 2],
            [
                'application_id' => 1,
                'error_code_id' => 1,
                'severity' => 'medium',
                'message' => 'Seed: log de prueba',
                'file' => 'seed.log',
                'line' => 10,
                'metadata' => ['seed' => true, 'source' => 'LogSeeder'],
                'matched_archived_log_id' => null,
                'resolved' => false,
                'created_at' => now(),
            ]
        );

        Log::updateOrCreate(
            ['id' => 3],
            [
                'application_id' => 1,
                'error_code_id' => 1,
                'severity' => 'high',
                'message' => 'Seed: log de prueba',
                'file' => 'seed.log',
                'line' => 10,
                'metadata' => ['seed' => true, 'source' => 'LogSeeder'],
                'matched_archived_log_id' => null,
                'resolved' => false,
                'created_at' => now(),
            ]
        );

        Log::updateOrCreate(
            ['id' => 4],
            [
                'application_id' => 1,
                'error_code_id' => 1,
                'severity' => 'critical',
                'message' => 'Seed: log de prueba',
                'file' => 'seed.log',
                'line' => 10,
                'metadata' => ['seed' => true, 'source' => 'LogSeeder'],
                'matched_archived_log_id' => null,
                'resolved' => false,
                'created_at' => now(),
            ]
        );

        Log::updateOrCreate(
            ['id' => 5],
            [
                'application_id' => 1,
                'error_code_id' => 1,
                'severity' => 'other',
                'message' => 'Seed: log de prueba',
                'file' => 'seed.log',
                'line' => 10,
                'metadata' => ['seed' => true, 'source' => 'LogSeeder'],
                'matched_archived_log_id' => null,
                'resolved' => false,
                'created_at' => now(),
            ]
        );
    }
}
