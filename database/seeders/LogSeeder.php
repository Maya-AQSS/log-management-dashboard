<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        $rows = array_map(function (array $row) {
            if (isset($row['metadata']) && is_array($row['metadata'])) {
                $row['metadata'] = json_encode($row['metadata'], JSON_UNESCAPED_UNICODE);
            }
            return $row;
        }, $mockLogs);
        
        DB::table('logs')->insert($rows);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM logs))"
            );
        }
    }
}
