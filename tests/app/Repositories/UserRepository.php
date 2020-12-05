<?php

namespace App\Repositories;

use Accolon\Izanagi\Repository;
use App\Models\User;

class UserRepository extends Repository
{
    protected string $class = User::class;
}
