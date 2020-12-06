Protótipo de ORM com php 8 e suas novas features

### User.php

```php
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
```

### UserRepository.php

```php
<?php

namespace App\Repositories;

use Accolon\Izanagi\Repository;
use App\Models\User;

class UserRepository extends Repository
{
    protected string $class = User::class;
}
```
### index.php

```php
// Basic Config

define("DB_CONFIG", [
    'name' => "izanagi",
    'user' => "root",
    'password' => 'pass'
]);
```

```php
// Migrate

$manager = new Manager([
    User::class
]);

$manager->migrate();
```

```php
$user = new User();
$user->name = 'Sla';
$user->password = 'd2k';
$user->admin = false;

$user->create();

$user->update(['admin' => true]);

$user->delete();
```

```php
$repository = new UserRepository();

$repository->all(); // User[]
$repository->findAll([
    'name' => 'Sla'
]); // User[]

$repository->findOne(['name' => 'Sla']); // ?User
$repository->find(1); // ?User

$repository->exists(['name' => 'Sla']); // bool
```