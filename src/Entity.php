<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\Table;

abstract class Entity
{
    protected QueryBuilder $qb;
    protected Connection $connection;
    protected string $primaryKey = 'id';
    protected bool $autoIncrement = true;
    protected \ReflectionClass $reflection;

    // Magic

    public function __construct(?Connection $connection = null)
    {
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }
        $this->connection = $connection;
        $this->reflection = new \ReflectionClass(static::class);
        $this->qb = new QueryBuilder($this->getTableName(), $connection);
    }

    public function __get($name)
    {
        if (in_array($name, $this->getPropertiesProtected())) {
            return $this->$name;
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->getPropertiesProtected())) {
            $this->$name = $value;
        }
    }

    // Getters and Setters

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
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
        return array_map(
            fn(\ReflectionProperty $prop) =>  $prop->getName(),
            $this->reflection->getProperties(
                \ReflectionProperty::IS_PUBLIC
            )
        );
    }

    public function getNamesPropertiesInitializeds()
    {
        return array_filter(
            $this->getProperties(),
            fn(string $prop) => $this->isInitialized($prop)
        );
    }

    public function getPropertiesInitializeds()
    {
        $props = [];

        foreach ($this->getNamesPropertiesInitializeds() as $name) {
            $props[$name] = $this->$name;
        }

        return $props;
    }

    public function getPropertiesProtected()
    {
        return array_map(
            fn(\ReflectionProperty $prop) =>  $prop->getName(),
            $this->reflection->getProperties(
                \ReflectionProperty::IS_PROTECTED
            )
        );
    }

    public function isInitialized(string $prop)
    {
        return $this->reflection->getProperty($prop)->isInitialized($this);
    }

    // API

    public function build($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function create()
    {
        $props = $this->getPropertiesInitializeds();
        $params = [];

        foreach ($props as $name => $prop) {
            $params[$name] = $prop;
        }

        $result = $this->qb->insert($params);

        $primaryKey = $this->getPrimaryKey();

        $this->$primaryKey = $result[3];

        return $result[0];
    }

    public function update(array $set)
    {
        $primaryKey = $this->getPrimaryKey();

        if ($this->isInitialized($primaryKey)) {
            return $this->qb->where($primaryKey, $this->$primaryKey)->update($set);
        }

        $props = $this->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        $result = $this->qb->update($set);

        if ($result) {
            foreach ($set as $name => $value) {
                $this->$name = $value;
            }
        }

        return $result;
    }

    public function delete()
    {
        $primaryKey = $this->getPrimaryKey();

        if ($this->isInitialized($primaryKey)) {
            return $this->qb->where($primaryKey, $this->$primaryKey)->delete();
        }

        $props = $this->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        return $this->qb->delete();
    }

    public function exists(): bool
    {
        foreach ($this->getPropertiesInitializeds() as $name => $value) {
            $this->qb->where($name, $value);
        }

        return !!$this->qb->select()->rowCount();
    }

    public function save(array $set = [])
    {
        if (!$this->exists()) {
            return $this->create();
        }

        return $this->qb->update($set);
    }
}
