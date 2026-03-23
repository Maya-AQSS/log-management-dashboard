<?php

namespace Database\Seeders;

use App\Models\Log;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        foreach ($mockLogs as $log) {
            Log::create($log);
        }

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM logs))"
        );
    }
}
