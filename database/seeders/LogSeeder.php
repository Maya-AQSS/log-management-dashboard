<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        // Convert metadata arrays to JSON strings
        $mockLogs = array_map(function ($log) {
            if (is_array($log['metadata'])) {
                $log['metadata'] = json_encode($log['metadata']);
            }
            return $log;
        }, $mockLogs);

        DB::table('logs')->insert($mockLogs);

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM logs))"
        );
    }
}
