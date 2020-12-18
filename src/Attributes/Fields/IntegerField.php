<?php

namespace Accolon\Izanagi\Attributes\Fields;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Types\FieldType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerField extends Field
{
    public function __construct(
        int $length = 11,
        ?int $default = null,
        bool $nullable = false,
        bool $primary = false,
        bool $unique = false,
        bool $autoIncrement = false
    ) {
        parent::__construct(
            type: FieldType::Integer,
            length: $length,
            default: $default,
            nullable: $nullable,
            primary: $primary,
            unique: $unique,
            autoIncrement: $autoIncrement
        );
    }
}
