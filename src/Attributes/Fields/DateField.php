<?php

namespace Accolon\Izanagi\Attributes\Fields;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Types\FieldType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateField extends Field
{
    public function __construct(
        $length = null,
        ?string $default = null,
        bool $nullable = false,
        bool $primary = false,
        bool $unique = false
    ) {
        parent::__construct(
            type: FieldType::String,
            length: $length,
            default: $default,
            nullable: $nullable,
            primary: $primary,
            unique: $unique
        );
    }
}
