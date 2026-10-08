<?php

declare(strict_types=1);

namespace VeloxRouter\Sse;

final readonly class SseEvent
{
    public function __construct(
        public mixed $data,
        public ?string $name = null,
        public ?string $id = null,
        public ?int $retry = null,
        public ?string $comment = null,
    ) {
        if ($this->retry !== null && $this->retry < 0) {
            throw new \InvalidArgumentException(
                'SSE retry value cannot be negative.'
            );
        }
    }
}
