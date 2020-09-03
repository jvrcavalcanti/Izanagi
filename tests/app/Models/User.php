<?php

namespace App\Models;

use Accolon\Izanagi\Attributes\{
    Entity,
    Field
};
use Accolon\Izanagi\Types\FieldType;
use Accolon\Izanagi\BaseEntity;

@@Entity(name: "users")
class User extends BaseEntity
{
    @@Field(
        type: FieldType::Integer,
        primary: true,
        length: 11,
        autoIncrement: true
    )
    private int $id;

    @@Field(
        type: FieldType::String,
        length: 30,
        default: "oi"
    )
    private string $name;

    public function __construct(string $name)
    {
        parent::__construct();
        $this->name = $name;
    }
}
