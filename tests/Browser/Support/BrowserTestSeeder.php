<?php

namespace Tests\Browser\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Seeds minimal data for browser tests using raw DB inserts (no factories required).
 *
 * Usage:
 *   $seed = BrowserTestSeeder::seedAll();
 *   $seed->user          // User model
 *   $seed->applicationId // int
 *   $seed->errorCodeId   // int
 *   $seed->logId         // int (active log — "error" severity)
 *   $seed->warningLogId  // int (active log — "warning" severity)
 *   $seed->archivedLogId // int
 */
final class BrowserTestSeeder
{
    public User $user;

    public int $applicationId;

    public int $errorCodeId;

    public int $logId;

    public int $warningLogId;

    public int $archivedLogId;

    private function __construct() {}

    public static function seedAll(): self
    {
        $seed = new self;

        $seed->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $seed->applicationId = DB::table('applications')->insertGetId([
            'name' => 'TestApp',
            'description' => 'Browser test application',
            'created_at' => now(),
        ]);

        $seed->errorCodeId = DB::table('error_codes')->insertGetId([
            'code' => 'ERR-001',
            'application_id' => $seed->applicationId,
            'name' => 'Test Error',
            'description' => 'Test error code for browser tests',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seed->logId = DB::table('logs')->insertGetId([
            'application_id' => $seed->applicationId,
            'error_code_id' => $seed->errorCodeId,
            'severity' => 'critical',
            'message' => 'Something went wrong in TestApp',
            'resolved' => false,
            'created_at' => now(),
        ]);

        $seed->warningLogId = DB::table('logs')->insertGetId([
            'application_id' => $seed->applicationId,
            'error_code_id' => null,
            'severity' => 'low',
            'message' => 'A low-severity issue occurred in TestApp',
            'resolved' => false,
            'created_at' => now(),
        ]);

        $seed->archivedLogId = DB::table('archived_logs')->insertGetId([
            'application_id' => $seed->applicationId,
            'archived_by_id' => $seed->user->id,
            'error_code_id' => null,
            'severity' => 'critical',
            'message' => 'Archived critical error',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subDay(),
            'archived_at' => now(),
            'updated_at' => now(),
        ]);

        return $seed;
    }
}
