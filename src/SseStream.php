<?php

declare(strict_types=1);

namespace VeloxRouter\Sse;

use JsonException;
use RuntimeException;

final class SseStream
{
    /**
     * Prepares the HTTP response for Server-Sent Events.
     */
    public static function start(): void
    {
        if (headers_sent()) {
            throw new RuntimeException(
                'Cannot start SSE stream because HTTP headers have already been sent.'
            );
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        set_time_limit(0);

        self::clearOutputBuffers();

        flush();
    }

    /**
     * Sends an SSE event to the client.
     *
     * @throws JsonException
     */
    public static function send(SseEvent $event): bool
    {
        if (self::isAborted()) {
            return false;
        }

        $buffer = '';

        if ($event->comment !== null) {
            self::assertSingleLineHeaderValue(
                $event->comment,
                'comment'
            );

            foreach (self::splitLines($event->comment) as $line) {
                $buffer .= ": {$line}\n";
            }
        }

        if ($event->id !== null) {
            self::assertSingleLineHeaderValue(
                $event->id,
                'id'
            );

            $buffer .= "id: {$event->id}\n";
        }

        if ($event->name !== null) {
            self::assertSingleLineHeaderValue(
                $event->name,
                'name'
            );

            $buffer .= "event: {$event->name}\n";
        }

        if ($event->retry !== null) {
            if ($event->retry < 0) {
                throw new RuntimeException(
                    'SSE retry value cannot be negative.'
                );
            }

            $buffer .= "retry: {$event->retry}\n";
        }

        if ($event->data !== null) {
            $payload = self::encodeData($event->data);

            foreach (self::splitLines($payload) as $line) {
                $buffer .= "data: {$line}\n";
            }
        }

        $buffer .= "\n";

        return self::write($buffer);
    }

    /**
     * Sends an SSE heartbeat comment.
     */
    public static function ping(): bool
    {
        if (self::isAborted()) {
            return false;
        }

        return self::write(": ping\n\n");
    }

    /**
     * Determines whether the client has closed the connection.
     */
    public static function isAborted(): bool
    {
        return connection_aborted() === 1;
    }

    /**
     * Writes data to the SSE stream and flushes the output.
     */
    private static function write(string $payload): bool
    {
        if (self::isAborted()) {
            return false;
        }

        echo $payload;

        flush();

        return !self::isAborted();
    }

    /**
     * Encodes event data as a string.
     *
     * Strings are sent as-is.
     * Other values are encoded as JSON.
     *
     * @throws JsonException
     */
    private static function encodeData(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }

        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Splits a value into SSE-compatible lines.
     */
    private static function splitLines(string $value): array
    {
        if ($value === '') {
            return [''];
        }

        return preg_split(
            "/\r\n|\r|\n/",
            $value
        ) ?: [''];
    }

    /**
     * Removes all active output buffers.
     */
    private static function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Prevents SSE control fields from containing line breaks.
     */
    private static function assertSingleLineHeaderValue(
        string $value,
        string $field
    ): void {
        if (
            str_contains($value, "\r")
            || str_contains($value, "\n")
        ) {
            throw new RuntimeException(
                sprintf(
                    'SSE %s cannot contain line breaks.',
                    $field
                )
            );
        }
    }
}
