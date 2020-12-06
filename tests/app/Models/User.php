<?php

namespace App\Models;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Attributes\Table;
use Accolon\Izanagi\Types\FieldType;
use Accolon\Izanagi\Entity;

#[Table(name: "users")]
class User extends Entity
{
    #[Field(type: FieldType::Integer, primary: true, length: 11, autoIncrement: true)]
    public int $id;

    #[Field(type: FieldType::String, length: 30)]
    public string $name;

    #[Field(type: FieldType::String, length: 30)]
    public string $password;

    #[Field(type: FieldType::Boolean)]
    public bool $admin;
}
