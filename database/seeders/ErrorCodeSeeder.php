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

        // Cache application name → id lookups
        $appCache = [];

        foreach ($errorCodes as $errorCode) {
            $appId = $errorCode['application_id'] ?? null;

            // If no application_id, resolve from application name
            if ($appId === null && isset($errorCode['application'])) {
                $appName = $errorCode['application'];
                if (!isset($appCache[$appName])) {
                    $app = \App\Models\Application::firstOrCreate(
                        ['name' => $appName],
                        ['description' => $appName . ' application']
                    );
                    $appCache[$appName] = $app->id;
                }
                $appId = $appCache[$appName];
            }

            $attributes = [
                'code' => $errorCode['code'],
                'application_id' => $appId,
                'name' => $errorCode['name'],
                'description' => $errorCode['description'] ?? null,
                'file' => $errorCode['file'] ?? null,
                'line' => $errorCode['line'] ?? null,
            ];

            ErrorCode::updateOrCreate(
                [
                    'code' => $errorCode['code'],
                    'application_id' => $appId,
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
