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
        $this->qb = new QueryBuilder($this->entity->getTableName(), $connection);
    }

    // Query

    public function all(string ...$cols)
    {
        return array_map(
            fn($data) => (new $this->class)->build($data),
            $this->qb->select(empty($cols) ? ['*'] : $cols)->fetchAll()
        );
    }

    public function find($id, string ...$cols): ?Entity
    {
        $result = $this->qb->where($this->entity->primaryKey, $id)->select(empty($cols) ? ['*'] : $cols)->fetchObject();
        return $result ? (new $this->class)->build($result) : null;
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

        return (bool) $this->qb->select()->rowCount();
    }
}
