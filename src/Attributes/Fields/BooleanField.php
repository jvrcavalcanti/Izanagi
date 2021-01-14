<?php

namespace Accolon\Izanagi\Attributes\Fields;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Types\FieldType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BooleanField extends Field
{
    public function __construct($default = null, bool $nullable = false)
    {
        parent::__construct(FieldType::Boolean, nullable: $nullable, default: !$default ? 0 : 1);
    }
}
