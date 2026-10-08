<?php

declare(strict_types=1);

namespace VeloxRouter\Sse;

class SseEvent
{
    public function __construct(
        public readonly mixed $data,
        public readonly ?string $name = null,
        public readonly ?string $id = null,
        public readonly ?int $retry = null,
        public readonly ?string $comment = null
    ) {}
}
