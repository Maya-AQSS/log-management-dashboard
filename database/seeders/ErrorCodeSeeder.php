<?php

namespace Database\Seeders;

use App\Models\ErrorCode;
use Illuminate\Database\Seeder;
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
            $attributes = [
                'code' => $errorCode['code'],
                'application_id' => $errorCode['application_id'],
                'name' => $errorCode['name'],
                'description' => $errorCode['description'] ?? null,
                'file' => $errorCode['file'] ?? null,
                'line' => $errorCode['line'] ?? null,
            ];

            ErrorCode::updateOrCreate(
                [
                    'code' => $errorCode['code'],
                    'application_id' => $errorCode['application_id'],
                ],
                $attributes
            );
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('error_codes', 'id'), (SELECT COALESCE(MAX(id), 1) FROM error_codes))"
            );
        }
    }
}
