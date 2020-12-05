<?php

namespace Accolon\Izanagi;

abstract class Repository
{
    protected Entity $entity;
    protected QueryBuilder $qb;
    protected Connection $connection;

    public function __construct(?Connection $connection = null)
    {
        if (!isset($this->class)) {
            throw new \Exception('You must define a property [class]');
        }
        $this->entity = new $this->class;
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
        $this->qb = new QueryBuilder($this->entity->getTableName());
    }

    public function create(Entity $entity)
    {
        $props = $entity->getPropertiesInitializeds();
        $params = [];

        foreach ($props as $name => $prop) {
            $params[$name] = $prop;
        }

        return $this->qb->insert($params);
    }

    public function update(Entity $entity, array $set)
    {
        $props = $entity->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        return $this->qb->update($set);
    }

    public function delete(Entity $entity)
    {
        $props = $entity->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        return $this->qb->delete();
    }

    // Query

    public function all(string ...$cols)
    {
        return array_map(
            fn($data) => (new $this->class)->build($data),
            $this->qb->select(empty($cols) ? ['*'] : $cols)->fetchAll()
        );
    }

    public function find($id, string ...$cols)
    {
        return (new $this->class)->build(
            $this->qb->where($this->entity->primaryKey, $id)->select(empty($cols) ? ['*'] : $cols)->fetchObject()
        );
    }

    public function findOne(array $props, string ...$cols): ?Entity
    {
        foreach ($props as $name => $value) {
            $this->qb->where($name, $value);
        }

        $result = $this->qb->select(empty($cols) ? ['*'] : $cols)->fetchObject();

        return $result ? (new $this->class)->build($result) : null;
    }

    public function findAll(array $props, string ...$cols)
    {
        foreach ($props as $name => $value) {
            $this->qb->where($name, $value);
        }

        return array_map(
            fn($data) => (new $this->class)->build($data),
            $this->qb->select(empty($cols) ? ['*'] : $cols)->fetchAll()
        );
    }

    public function exists(array $props)
    {
        foreach ($props as $name => $value) {
            $this->qb->where($name, $value);
        }

        return (bool) count($this->qb->select()->fetchAll());
    }
}
