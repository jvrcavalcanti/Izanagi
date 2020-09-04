<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\{
    Table,
    Field
};
use Accolon\Izanagi\Types\CharsetType;

abstract class Entity
{
    protected Connection $connection;
    protected string $charset = CharsetType::UFT8;

    public function __construct(?Connection $connection = null)
    {
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
    }
}
