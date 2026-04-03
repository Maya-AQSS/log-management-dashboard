<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $mockLogs = require database_path('data/mock-logs.php');

        $errorCodeIdByCode = DB::table('error_codes')->pluck('id', 'code')->toArray();

        $rows = array_map(function (array $row) use ($errorCodeIdByCode) {
            if (isset($row['error_code_id']) && is_string($row['error_code_id'])) {
                $row['error_code_id'] = $errorCodeIdByCode[$row['error_code_id']] ?? null;
            }

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
