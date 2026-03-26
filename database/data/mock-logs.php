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
$veryLongChunk = str_repeat('STACK_TRACE_CHUNK: NullReferenceException at App\\Services\\LogPipeline->process() ', 120);

foreach ($severityCounts as $severity => $count) {
    for ($i = 1; $i <= $count; $i++) {
        $createdAt = $baseDate
            ->modify('-' . (($id - 1) * 3) . ' minutes')
            ->format('Y-m-d H:i:s');

        $message = sprintf(
            'Seed: %s log #%03d - %s',
            $severity,
            $i,
            $messageTail
        );

        $metadata = [
            'seed' => true,
            'source' => 'mock-logs.php',
            'batch' => 'dashboard-cards',
        ];

        // Dedicated fixture to validate long-content scrolling in detail view.
        if ($severity === 'critical' && $i === 2) {
            $message .= "\n\n" . $veryLongChunk;
            $metadata['stack_trace'] = $veryLongChunk;
        }

        $rows[] = [
            'id' => $id++,
            'application_id' => 1,
            'error_code_id' => 1,
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

return $rows;
