<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\Table;

abstract class Entity
{
    protected bool $exists = false;
    protected string $primaryKey = 'id';
    protected bool $autoIncrement = true;
    protected \ReflectionClass $reflection;

    // Magic

    public function __construct()
    {
        $this->reflection = new \ReflectionClass(static::class);
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

    public function getPrimaryKeyValue()
    {
        return $this->{$this->getPrimaryKey()};
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function setPrimaryKey($value)
    {
        $this->{$this->getPrimaryKey()} = $value;
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

    public function isPrimaryKeyInitialized()
    {
        return $this->isInitialized($this->primaryKey);
    }

    // API

    public function build($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }
}
