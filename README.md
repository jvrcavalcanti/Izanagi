Protótipo de ORM com php 8 e suas novas features

```php
<?php

namespace App\Models;

use Accolon\Izanagi\Attributes\{
    Table,
    Field
};
use Accolon\Izanagi\Types\FieldType;
use Accolon\Izanagi\Entity;

#[Table(name: "users")]
class User extends Entity
{
    #[Field(type: FieldType::Integer, primary: true, length: 11, autoIncrement: true)]
    private int $id;

    #[Field(type: FieldType::String, length: 30)]
    private string $name;

    #[Field(type: FieldType::String, length: 30)]
    private string $password;

    #[Field(type: FieldType::Boolean)]
    public bool $admin;

    #[Field(type: FieldType::Float, length: 10.2, default: 0.0)]
    private float $price;

    public function __construct(string $name)
    {
        parent::__construct();
        $this->name = $name;
    }
}
```