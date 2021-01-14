<?php

namespace App\Models;

use Accolon\Izanagi\Attributes\Table;
use Accolon\Izanagi\Attributes\Fields\BooleanField;
use Accolon\Izanagi\Attributes\Fields\StringField;
use Accolon\Izanagi\Attributes\Fields\IntegerField;
use Accolon\Izanagi\Entity;

#[Table(name: "users")]
class User extends Entity
{
    #[IntegerField(primary: true, autoIncrement: true)]
    public int $id;

    #[StringField(length: 35, unique: true)]
    public string $name;

    #[StringField(40)]
    public string $password;

    #[BooleanField(false)]
    public bool $admin;
}
