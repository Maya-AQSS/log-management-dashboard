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

$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
$messageTail = str_repeat($lorem, 3);

foreach ($severityCounts as $severity => $count) {
    for ($i = 1; $i <= $count; $i++) {
        $createdAt = $baseDate
            ->modify('-' . (($id - 1) * 3) . ' minutes')
            ->format('Y-m-d H:i:s');

        $rows[] = [
            'id' => $id++,
            'application_id' => 1,
            'error_code_id' => 1,
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

return $rows;
