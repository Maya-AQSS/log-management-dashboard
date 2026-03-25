<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        DB::table('logs')->insert($mockLogs);

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM logs))"
        );
    }
}
