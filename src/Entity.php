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

    // API

    protected function find(array $cols)
    {
        $props = $this->getNamesPropertiesInitializeds();

        foreach ($props as $prop) {
            $this->qb->where($prop, $this->$prop);
        }

        $result = sizeof($cols) > 0 ? $this->qb->select($cols) : $this->qb->select();

        if ($result->rowCount() > 0) {
            $this->exists = true;
        }

        return $result;
    }

    public function findAll(array $cols = [])
    {
        return array_map(fn($entity) => (new static)->persist($entity), $this->find($cols)->fetchAll());
    }

    public function findOne(array $cols = []): ?object
    {
        return $this->persist($this->find($cols)->fetchObject());
    }

    public static function findId($id, array $cols = [])
    {
        $obj = new static();
        $obj->qb->where('id', $id);
        $obj->persist($obj->findOne($cols));
        return $obj;
        
    }

    public function persist($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function exists()
    {
        return $this->exists = (bool) $this->findOne();
    }

    public function create()
    {
        $props = $this->getNamesPropertiesInitializeds();
        $params = [];

        foreach ($props as $prop) {
            $params[$prop] = $this->$prop;
        }

        return $this->qb->insert($params);
    }

    public function update(array $set)
    {
        $props = $this->getNamesPropertiesInitializeds();

        foreach ($props as $prop) {
            $this->qb->where($prop, $this->$prop);
        }

        return $this->qb->update($set);
    }

    public function delete()
    {
        $props = $this->getNamesPropertiesInitializeds();

        foreach ($props as $prop) {
            $this->qb->where($prop, $this->$prop);
        }

        return $this->qb->delete();
    }

    public function save()
    {
        if (!$this->exists) {
            return $this->create();
        }

        $primaryKey = $this->primaryKey;

        $this->qb->where($primaryKey, $this->$primaryKey);

        return $this->qb->update($this->getPropertiesInitializeds());
    }
}
