<?php

declare(strict_types=1);

namespace VeloxRouter\Sse;

class SseStream
{
    /**
     * Prepara os headers HTTP e limpa os buffers de output para iniciar o stream.
     */
    public static function start(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Desativa o buffering do Nginx

        set_time_limit(0);

        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        
        flush();
    }

    /**
     * Envia um evento formatado com base num objeto SseEvent.
     */
    public static function send(SseEvent $event): bool
    {
        if (self::isAborted()) {
            return false;
        }

        $buffer = '';

        if ($event->comment !== null) {
            foreach (self::splitLines($event->comment) as $line) {
                $buffer .= ": {$line}\n";
            }
        }

        if ($event->id !== null) {
            $buffer .= "id: {$event->id}\n";
        }

        if ($event->name !== null) {
            $buffer .= "event: {$event->name}\n";
        }

        if ($event->retry !== null) {
            $buffer .= "retry: {$event->retry}\n";
        }

        if ($event->data !== null) {
            $payload = is_string($event->data) ? $event->data : json_encode($event->data);
            foreach (self::splitLines($payload) as $line) {
                $buffer .= "data: {$line}\n";
            }
        }

        $buffer .= "\n";

        echo $buffer;
        
        $flushed = @flush();
        if ($flushed === false || self::isAborted()) {
            return false;
        }

        return true;
    }

    /**
     * Envia um sinal de heartbeat (ping) para manter a ligação viva.
     */
    public static function ping(): bool
    {
        echo ": ping\n\n";
        $flushed = @flush();
        
        return !($flushed === false || self::isAborted());
    }

    /**
     * Valida se o cliente cortou a ligação.
     */
    public static function isAborted(): bool
    {
        return connection_aborted() === 1;
    }

    private static function splitLines(string $value): array
    {
        if ($value === '') {
            return [''];
        }
        return explode("\n", $value);
    }
}
