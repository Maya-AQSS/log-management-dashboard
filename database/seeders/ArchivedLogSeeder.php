<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ArchivedLog;

class ArchivedLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArchivedLog::updateOrCreate(
            ['id' => 1],
            [
                'application_id' => 1,
                'archived_by_id' => 1,
                'error_code_id' => 1,
                'severity' => 'low',
                'message' => 'Seed: archived log de prueba',
                'metadata' => ['seed' => true, 'source' => 'ArchivedLogSeeder'],
                'description' => 'Descripción de prueba del histórico de logs',
                'url_tutorial' => 'https://example.com/tutorial',
                'original_created_at' => now()->subDay(),
                'archived_at' => now(),
            ]
        );

        ArchivedLog::updateOrCreate(
            ['id' => 2],
            [
                'application_id' => 1,
                'archived_by_id' => 2,
                'error_code_id' => 1,
                'severity' => 'medium',
                'message' => 'Seed: archived log de prueba',
                'metadata' => ['seed' => true, 'source' => 'ArchivedLogSeeder'],
                'description' => 'Descripción de prueba del histórico de logs',
                'url_tutorial' => 'https://example.com/tutorial',
                'original_created_at' => now()->subDay(),
                'archived_at' => now(),
            ]
        );
    }
}
