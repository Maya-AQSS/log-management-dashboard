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

$applications = require __DIR__.'/mock-applications.php';
$errorCodes = require __DIR__.'/mock-error-codes.php';

$applicationIds = array_values(array_map(
    static fn (array $application): int => (int) $application['id'],
    $applications
));

$errorCodeByApplication = [];
foreach ($errorCodes as $errorCode) {
    $errorCodeByApplication[(int) $errorCode['application_id']] = (int) $errorCode['id'];
}

$longSegment = 'trace context user request pipeline timeout retry database cache service';
$messageBody = trim(implode(' ', array_fill(0, 36, $longSegment)));

foreach ($severityCounts as $severity => $count) {
    for ($i = 1; $i <= $count; $i++) {
        $createdAt = $baseDate
            ->modify('-'.(($id - 1) * 3).' minutes')
            ->format('Y-m-d H:i:s');

        $message = sprintf('Seed: %s log #%03d - %s', $severity, $i, $messageBody);

        $metadata = [
            'seed' => true,
            'source' => 'mock-logs.php',
            'batch' => 'dashboard-cards',
        ];

        // Dedicated fixture to validate long-content scrolling in detail view.
        if ($severity === 'critical' && $i === 2) {
            $metadata['stack_trace'] = $messageBody;
        }

        $applicationId = $applicationIds[($id - 1) % count($applicationIds)];
        $errorCodeId = $errorCodeByApplication[$applicationId] ?? null;

        $rows[] = [
            'application_id' => $applicationId,
            'error_code_id' => $errorCodeId,
            'severity' => $severity,

            'message' => $message,
            'file' => sprintf('seed/%s.log', $severity),
            'line' => 100 + $i,
            'metadata' => $metadata,
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
$duplicateMessage = 'Seed duplicate: repeated message for archived matching checks - '.$messageBody;

for ($i = 1; $i <= 6; $i++) {
    $rows[] = [
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
            ->modify('+'.(($i - 1) * 2).' minutes')
            ->format('Y-m-d H:i:s'),
    ];
}

// --- BLOQUES ADICIONALES PARA BALANCEAR LOGS ENTRE TODAS LAS APPS ---
$extraSeverities = ['critical', 'high', 'medium', 'low', 'other'];
$extraCount = 10;
$extraBaseDate = $baseDate->modify('+3 days');
$validAppIds = [1, 2, 3, 4];
$validErrorCodeIds = range(1, 22);
foreach ($validAppIds as $appIdx => $appId) {
    for ($i = 0; $i < $extraCount; $i++) {
        $rows[] = [
            'application_id' => $appId,
            'error_code_id' => $validErrorCodeIds[($i + $appIdx) % count($validErrorCodeIds)],
            'severity' => $extraSeverities[($i + $appIdx) % count($extraSeverities)],
            'message' => sprintf('Extra log for app %d [%s] #%d', $appId, $extraSeverities[($i + $appIdx) % count($extraSeverities)], $i + 1),
            'file' => sprintf('seed/extra-app%d.log', $appId),
            'line' => 3000 + $i,
            'metadata' => [
                'seed' => true,
                'source' => 'mock-logs.php',
                'batch' => 'extra-balanced',
            ],
            'resolved' => $i % 2 === 0,
            'created_at' => $extraBaseDate->modify("+{$i} minutes")->format('Y-m-d H:i:s'),
        ];
    }
}

return $rows;
