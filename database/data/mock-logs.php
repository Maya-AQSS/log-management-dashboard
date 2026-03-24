<?php

$severities = ['critical', 'high', 'low', 'other'];
$rows = [];
$id = 1;
$baseDate = new DateTimeImmutable('now');

foreach ($severities as $severity) {
    for ($i = 1; $i <= 10; $i++) {
        $rows[] = [
            'id' => $id++,
            'application_id' => 1,
            'error_code_id' => 1,
            'severity' => $severity,
            'message' => sprintf('Seed: %s log #%02d', $severity, $i),
            'file' => sprintf('seed/%s.log', $severity),
            'line' => 100 + $i,
            'metadata' => [
                'seed' => true,
                'source' => 'mock-logs.php',
                'batch' => 'dashboard-cards',
            ],
            'matched_archived_log_id' => $i % 4 === 0 ? 1 : null,
            'resolved' => $i % 2 === 0,
            'created_at' => $baseDate->modify('-' . (($id - 1) * 3) . ' minutes')->format('Y-m-d H:i:s'),
        ];
    }
}

for($i = 1; $i <= 5; $i++) {
    $rows[] = [
        'id' => $id++,
        'application_id' => 1,
        'error_code_id' => 1,
        'severity' => 'medium',
        'message' => sprintf('Seed: medium log #%02d', $i),
        'file' => sprintf('seed/medium.log', $i),
        'line' => 100 + $i,
        'metadata' => [
            'seed' => true,
            'source' => 'mock-logs.php',
            'batch' => 'dashboard-cards',
        ],
        'matched_archived_log_id' => null,
        'resolved' => false,
        'created_at' => $baseDate->modify('-' . (($id - 1) * 3) . ' minutes')->format('Y-m-d H:i:s'),
    ];
}

return $rows;
