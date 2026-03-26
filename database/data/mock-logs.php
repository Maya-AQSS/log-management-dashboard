<?php

$rows = [];
$id = 1;

$baseDate = new DateTimeImmutable('2026-03-25 12:00:00');

$severityCounts = [
    'critical' => 200,
    'high' => 60,
    'medium' => 30,
    'low' => 20,
    'other' => 20,
];

$applications = require __DIR__ . '/mock-applications.php';
$errorCodes = require __DIR__ . '/mock-error-codes.php';

$applicationIds = array_values(array_map(
    static fn (array $application): int => (int) $application['id'],
    $applications
));

$errorCodeByApplication = [];
foreach ($errorCodes as $errorCode) {
    $errorCodeByApplication[(int) $errorCode['application_id']] = (int) $errorCode['id'];
}

$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
$messageTail = str_repeat($lorem, 3);

foreach ($severityCounts as $severity => $count) {
    for ($i = 1; $i <= $count; $i++) {
        $createdAt = $baseDate
            ->modify('-' . (($id - 1) * 3) . ' minutes')
            ->format('Y-m-d H:i:s');

        $applicationId = $applicationIds[($id - 1) % count($applicationIds)];
        $errorCodeId = $errorCodeByApplication[$applicationId] ?? null;

        $rows[] = [
            'id' => $id++,
            'application_id' => $applicationId,
            'error_code_id' => $errorCodeId,
            'severity' => $severity,
            
            'message' => sprintf(
                'Seed: %s log #%03d - %s',
                $severity,
                $i,
                $messageTail
            ),
            'file' => sprintf('seed/%s.log', $severity),
            'line' => 100 + $i,
            'metadata' => [
                'seed' => true,
                'source' => 'mock-logs.php',
                'batch' => 'dashboard-cards',
            ],
            'resolved' => $i % 2 === 0,
            'created_at' => $createdAt,
        ];
    }
}

// Bloque controlado de logs con mismo mensaje/código/app para comprobar matching
// con archived_logs cuando hay potenciales "duplicados funcionales".
$duplicateBaseDate = $baseDate->modify('+1 day');
$duplicateApplicationId = 1; // api-gateway
$duplicateErrorCodeId = $errorCodeByApplication[$duplicateApplicationId] ?? null;
$duplicateMessage = 'Seed duplicate: repeated message for archived matching checks';

for ($i = 1; $i <= 6; $i++) {
    $rows[] = [
        'id' => $id++,
        'application_id' => $duplicateApplicationId,
        'error_code_id' => $duplicateErrorCodeId,
        'severity' => 'critical',
        'message' => $duplicateMessage,
        'file' => 'seed/duplicates.log',
        'line' => 500 + $i,
        'metadata' => [
            'seed' => true,
            'source' => 'mock-logs.php',
            'batch' => 'archived-matching-duplicates',
        ],
        'resolved' => $i % 2 === 0,
        'created_at' => $duplicateBaseDate
            ->modify('+' . (($i - 1) * 2) . ' minutes')
            ->format('Y-m-d H:i:s'),
    ];
}

return $rows;
