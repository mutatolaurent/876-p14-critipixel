<?php

namespace App\Model\ValueObject;

final readonly class Page
{
    public function __construct(
        public int $page,
        public bool $active,
        public string $label,
        public string $url,
    ) {
    }
}
