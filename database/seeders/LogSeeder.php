<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        foreach ($mockLogs as &$row) {
            if (isset($row['metadata']) && is_array($row['metadata'])) {
                $row['metadata'] = json_encode(
                    $row['metadata'],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }
        unset($row);
        
        DB::table('logs')->insert($mockLogs);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM logs))"
            );
        }
    }
}
