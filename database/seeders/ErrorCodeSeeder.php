<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ErrorCode;

class ErrorCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['code' => 'ERR-001', 'application_id' => 1, 'name' => 'Auth token expired', 'file' => 'app/Auth/TokenGuard.php', 'line' => 42, 'description' => 'Token expired before validation', 'severity' => 'high'],
            ['code' => 'ERR-002', 'application_id' => 1, 'name' => 'Invalid payload schema', 'file' => 'app/Http/Controllers/ApiController.php', 'line' => 77, 'description' => 'Payload does not match expected schema', 'severity' => 'medium'],
            ['code' => 'ERR-003', 'application_id' => 1, 'name' => 'Database timeout', 'file' => 'app/Repositories/Eloquent/LogRepository.php', 'line' => 118, 'description' => 'Query exceeded timeout threshold', 'severity' => 'critical'],
            ['code' => 'ERR-004', 'application_id' => 1, 'name' => 'Cache connection refused', 'file' => 'app/Services/CacheService.php', 'line' => 25, 'description' => 'Redis refused connection', 'severity' => 'high'],
            ['code' => 'ERR-005', 'application_id' => 1, 'name' => 'Missing configuration key', 'file' => 'config/services.php', 'line' => 12, 'description' => 'Required service key is missing', 'severity' => 'low'],
            ['code' => 'ERR-006', 'application_id' => 1, 'name' => 'Rate limit exceeded', 'file' => 'app/Http/Middleware/ThrottleRequests.php', 'line' => 66, 'description' => 'Client exceeded request quota', 'severity' => 'medium'],

            ['code' => 'ERR-001', 'application_id' => 2, 'name' => 'Webhook signature invalid', 'file' => 'app/Services/WebhookVerifier.php', 'line' => 51, 'description' => 'Invalid webhook signature', 'severity' => 'high'],
            ['code' => 'ERR-002', 'application_id' => 2, 'name' => 'External API unavailable', 'file' => 'app/Services/ExternalApiService.php', 'line' => 134, 'description' => 'Third-party API returned 503', 'severity' => 'critical'],
            ['code' => 'ERR-003', 'application_id' => 2, 'name' => 'Malformed CSV import', 'file' => 'app/Services/Import/CsvImporter.php', 'line' => 89, 'description' => 'CSV row malformed at parse time', 'severity' => 'medium'],
            ['code' => 'ERR-004', 'application_id' => 2, 'name' => 'File storage write failed', 'file' => 'app/Services/FileStorageService.php', 'line' => 103, 'description' => 'Unable to persist file to disk', 'severity' => 'high'],
            ['code' => 'ERR-005', 'application_id' => 2, 'name' => 'Queue job stalled', 'file' => 'app/Jobs/ProcessImport.php', 'line' => 57, 'description' => 'Job exceeded expected runtime', 'severity' => 'low'],
            ['code' => 'ERR-006', 'application_id' => 2, 'name' => 'Email transport error', 'file' => 'app/Notifications/SendDigest.php', 'line' => 71, 'description' => 'SMTP transport failed', 'severity' => 'other'],

            ['code' => 'ERR-001', 'application_id' => 3, 'name' => 'Session deserialization failed', 'file' => 'app/Http/Middleware/StartSession.php', 'line' => 39, 'description' => 'Session payload cannot be deserialized', 'severity' => 'medium'],
            ['code' => 'ERR-002', 'application_id' => 3, 'name' => 'Permission denied', 'file' => 'app/Policies/ProjectPolicy.php', 'line' => 27, 'description' => 'User lacks required permission', 'severity' => 'low'],
            ['code' => 'ERR-003', 'application_id' => 3, 'name' => 'Template rendering failed', 'file' => 'resources/views/errors/fallback.blade.php', 'line' => 9, 'description' => 'Blade template rendering exception', 'severity' => 'high'],
            ['code' => 'ERR-004', 'application_id' => 3, 'name' => 'Unexpected null reference', 'file' => 'app/Services/ProjectService.php', 'line' => 112, 'description' => 'Null reference in service pipeline', 'severity' => 'critical'],
            ['code' => 'ERR-005', 'application_id' => 3, 'name' => 'SSE stream interrupted', 'file' => 'app/Http/Controllers/LogController.php', 'line' => 41, 'description' => 'Client stream closed unexpectedly', 'severity' => 'other'],
            ['code' => 'ERR-006', 'application_id' => 3, 'name' => 'Validation fallback triggered', 'file' => 'app/Http/Requests/StoreProjectRequest.php', 'line' => 18, 'description' => 'Fallback validation path executed', 'severity' => 'low'],
        ];

        ErrorCode::query()->upsert(
            $rows,
            ['code', 'application_id'],
            ['name', 'file', 'line', 'description', 'severity']
        );
    }
}
