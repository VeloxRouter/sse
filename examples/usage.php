<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use VeloxRouter\Sse\SseStream;

// 1. Initialize the SSE stream
SseStream::start();

$counter = 0;

// 2. Real-time loop
while (true) {
    // Break loop if client closes the browser tab
    if (SseStream::isAborted()) {
        break;
    }

    $counter++;

    // Send structured event data
    SseStream::send([
        'id' => $counter,
        'message' => 'Real-time update from VeloxRouter SSE!',
        'timestamp' => date('Y-m-d H:i:s')
    ], event: 'notification');

    // Wait 3 seconds before next push
    sleep(3);
}
