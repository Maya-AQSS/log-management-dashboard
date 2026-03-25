<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ErrorCode;
use Illuminate\Support\Facades\DB;

class ErrorCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $errorCodes = require database_path('data/mock-error-codes.php');

        foreach ($errorCodes as $errorCode) {
            ErrorCode::updateOrCreate(
                ['id' => $errorCode['id']],
                [
                    'code' => $errorCode['code'],
                    'application_id' => $errorCode['application_id'],
                    'name' => $errorCode['name'],
                    'file' => $errorCode['file'] ?? null,
                    'line' => $errorCode['line'] ?? null,
                    'description' => $errorCode['description'],
                    'severity' => $errorCode['severity'],
                ]
            );
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('error_codes', 'id'), (SELECT COALESCE(MAX(id), 1) FROM error_codes))"
            );
        }
    }
}
