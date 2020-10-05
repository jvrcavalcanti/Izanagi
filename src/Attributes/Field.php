<?php

namespace Accolon\Izanagi\Attributes;

#[Attribute]
class Field
{
    public function __construct(
        public string $type,
        public int|float $length,
        public $default,
        public bool $nullable = false,
        public bool $unique = false,
        public bool $primary = false,
        public bool $autoIncrement = false,
    ) {
        $this->type = $type;
    }
}
