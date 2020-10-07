<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\{
    Table,
    Field
};
use Accolon\Izanagi\Types\CharsetType;

abstract class Entity
{
    protected QueryBuilder $qb;
    protected \ReflectionClass $reflection;
    protected Connection $connection;
    protected string $charset = CharsetType::UFT8;
    protected bool $exists = false;
    protected bool $autoIncrement = true;
    protected string $primaryKey = 'id';

    // Magic

    public function __construct(?Connection $connection = null)
    {
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
        $this->reflection = new \ReflectionClass(static::class);
        $this->qb = new QueryBuilder($this->getTableName());
    }

    // Reflection

    public function getTableName(): string
    {
        return $this->reflection
                    ->getAttributes(Table::class)[0]
                    ->getArguments()['name'];
    }

    public function getProperties()
    {
        return array_map(fn(\ReflectionProperty $prop) =>  $prop->getName(),$this->reflection->getProperties(
            \ReflectionProperty::IS_PUBLIC
        ));
    }

    public function getPropertiesProtected()
    {
        return array_map(fn(\ReflectionProperty $prop) =>  $prop->getName(),$this->reflection->getProperties(
            \ReflectionProperty::IS_PROTECTED
        ));
    }

    public function isInitialized(string $prop)
    {
        return $this->reflection->getProperty($prop)->isInitialized($this);
    }

    // Getters and Setters

    public function __get($name)
    {
        if (!in_array($name, $this->getPropertiesProtected())) {
            return $this->$name;
        }
    }

    public function __set($name, $value)
    {
        if (!in_array($name, $this->getPropertiesProtected())) {
            return $this->$name = $value;
        }
    }

    // API

    protected function find(?array $cols)
    {
        $props = $this->getProperties();

        foreach ($props as $prop) {
            if (!$this->isInitialized($prop)) {
                continue;
            }

            $this->qb->where($prop, $this->$prop);
        }

        return is_array($cols) ? $this->qb->select($cols) : $this->qb->select();
    }

    public function findAll(?array $cols = null)
    {
        return $this->find($cols)->fetchAll();
    }

    public function findOne(?array $cols = null)
    {
        return $this->find($cols)->fetchObject();
    }
}
