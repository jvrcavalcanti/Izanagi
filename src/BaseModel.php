<?php

namespace Accolon\Izanagi;

use ReflectionClass;

abstract class BaseModel
{
    protected Connection $connection;
    protected ReflectionClass $reflector;

    public function __construct(?Connection $connection = null)
    {
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
        $this->reflector = new ReflectionClass(static::class);
    }

    public function getModelName()
    {
        return $this->reflector->getAttributes(Model::class)[0]->getArguments()['name'];
    }
}
