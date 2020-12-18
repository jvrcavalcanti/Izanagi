<?php

namespace Accolon\Izanagi\Attributes\Fields;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Types\FieldType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringField extends Field
{
    public function __construct(
        int $length = 255,
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
