<?php

namespace Accolon\Izanagi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public string $type,
        public int|float|null $length = null,
        public $default = null,
        public bool $nullable = false,
        public bool $unique = false,
        public bool $primary = false,
        public bool $autoIncrement = false,
    ) {
        //
    }
}
