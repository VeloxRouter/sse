<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use VeloxRouter\Router;
use VeloxRouter\Sse\SseStream;
use VeloxRouter\Sse\SseEvent;

$router = new Router();

// Define a route for Server-Sent Events
$router->get('/stream', function ($request, $response) {
    // 1. Initialize the SSE headers, buffers and time limit
    SseStream::start();

    $counter = 0;

    // 2. Real-time loop
    while (!SseStream::isAborted()) {
        $counter++;

        // Send data using the immutable SseEvent DTO
        $sent = SseStream::send(new SseEvent(
            data: [
                'message' => 'Real-time update from VeloxRouter SSE!',
                'count' => $counter,
                'time' => date('H:i:s')
            ],
            name: 'notification',
            id: (string) $counter,
            retry: 3000 // Instruct the browser to reconnect after 3s if disconnected
        ));

        // If writing/flushing failed (client aborted), break the loop
        if (!$sent) {
            break;
        }

        // Wait 3 seconds before next push
        sleep(3);
    }
});

// Run the router
$router->run();
