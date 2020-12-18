<?php

namespace Accolon\Izanagi\Attributes\Fields;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Types\FieldType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FloatField extends Field
{
    public function __construct(
        float $length = 11.2,
        ?float $default = null,
        bool $nullable = false,
        bool $primary = false,
        bool $unique = false
    ) {
        parent::__construct(
            type: FieldType::Integer,
            length: $length,
            default: $default,
            nullable: $nullable,
            primary: $primary,
            unique: $unique
        );
    }
}
