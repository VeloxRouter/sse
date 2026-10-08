# VeloxRouter SSE

A lightweight, high-performance, **zero-dependency** Server-Sent Events (SSE) library for PHP. Designed to integrate seamlessly with modern routing engines like [VeloxRouter](https://github.com/VeloxRouter/router).

## Installation

Install via Composer:

```bash
composer require veloxrouter/sse

```

## Requirements

* **PHP:** 8.1 or higher (fully compatible with PHP 8.2+ features like `readonly` classes).

---

## Usage Example

Aqui tens um exemplo prático de como utilizar o `SseStream` e o DTO imutável `SseEvent` dentro duma rota:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use VeloxRouter\Router;
use VeloxRouter\Sse\SseStream;
use VeloxRouter\Sse\SseEvent;

$router = new Router();

$router->get('/stream', function ($request, $response) {
    // 1. Initialize headers, buffers and execution timeout
    SseStream::start();

    $counter = 0;

    // 2. Stream loop
    while (!SseStream::isAborted()) {
        $counter++;

        // Send a structured, immutable SSE event
        $sent = SseStream::send(new SseEvent(
            data: [
                'message' => 'Real-time update!',
                'count' => $counter,
                'time' => date('H:i:s')
            ],
            name: 'notification',
            id: (string) $counter,
            retry: 3000 // Reconnection time in milliseconds
        ));

        // Break loop if client disconnected
        if (!$sent) {
            break;
        }

        sleep(3);
    }
});

$router->run();

```

---

## Features

* **Zero Dependencies:** Core implementation relying solely on native PHP features.
* **Immutable DTO (`SseEvent`):** Strict data validation and memory safety using PHP modern features.
* **Protocol Safe:** Built-in line break validation to prevent SSE header injection or formatting corruption.
* **Output Buffer Cleaning:** Cleans nested output buffers automatically to guarantee real-time delivery.
* **Connection Monitoring:** Native tracking via `connection_aborted()` to instantly drop dead background loops.

---

## License

The MIT License (MIT). Please see [License File](https://www.google.com/search?q=LICENSE) for more information.
