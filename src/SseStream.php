<?php

declare(strict_types=1);

namespace VeloxRouter\Sse;

class SseStream
{
    /**
     * Set up required HTTP headers and clean output buffers to start the SSE stream.
     */
    public static function start(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable Nginx buffering for real-time delivery

        set_time_limit(0);

        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        flush();
    }

    /**
     * Send a formatted message/event to the client.
     *
     * @param mixed       $data  Data to send (arrays/objects are automatically JSON encoded)
     * @param string|null $event Optional custom event name
     * @param string|null $id    Optional message ID
     */
    public static function send(mixed $data, ?string $event = null, ?string $id = null): void
    {
        if ($id !== null) {
            echo "id: {$id}\n";
        }

        if ($event !== null) {
            echo "event: {$event}\n";
        }

        $payload = is_string($data) ? $data : json_encode($data);
        
        // Each line of the payload must be prefixed with "data: "
        foreach (explode("\n", $payload) as $line) {
            echo "data: {$line}\n";
        }

        echo "\n"; // Mandatory empty line to finalize the SSE block
        
        flush();
    }

    /**
     * Send a heartbeat ping signal to keep the connection alive against timeouts.
     */
    public static function ping(): void
    {
        echo ": ping\n\n";
        flush();
    }

    /**
     * Check if the client has aborted/closed the connection.
     */
    public static function isAborted(): bool
    {
        return connection_aborted();
    }
}
