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

    //

    public function create(Entity $entity): bool
    {
        $props = $entity->getPropertiesInitializeds();
        $params = [];

        foreach ($props as $name => $prop) {
            $params[$name] = $prop;
        }

        $result = $this->qb->insert($params);

        $entity->setPrimaryKey($result[3]);

        return $result[0];
    }

    public function update(Entity $entity, array $set)
    {
        $primaryKey = $entity->getPrimaryKey();

        if ($entity->isInitialized($primaryKey)) {
            return $this->qb->where($primaryKey, $entity->getPrimaryKeyValue())->update($set);
        }

        $props = $entity->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        $result = $this->qb->update($set);

        if ($result) {
            foreach ($set as $name => $value) {
                $entity->$name = $value;
            }
        }

        return $result;
    }

    public function delete(Entity $entity)
    {
        $primaryKey = $entity->getPrimaryKey();

        if ($entity->isInitialized($primaryKey)) {
            return $this->qb->where($primaryKey, $entity->getPrimaryKeyValue())->delete();
        }

        $props = $entity->getPropertiesInitializeds();

        foreach ($props as $name => $prop) {
            $this->qb->where($name, $prop);
        }

        return $this->qb->delete();
    }

    public function save(Entity $entity, array $set = [])
    {
        if (!$this->existsEntity($entity)) {
            return $this->create($entity);
        }

        $props = $entity->getPropertiesInitializeds();

        if ($entity->isPrimaryKeyInitialized()) {
            return $this->qb->where($entity->getPrimaryKey(), $entity->getPrimaryKeyValue())->update($props);
        }

        return $this->update($entity, $set);
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

    public function existsArray(array $props)
    {
        foreach ($props as $name => $value) {
            $this->qb->where($name, $value);
        }

        return (bool) $this->qb->select()->rowCount();
    }

    public function existsEntity(Entity $entity): bool
    {
        $primaryKey = $entity->getPrimaryKey();

        if ($entity->isInitialized($primaryKey)) {
            return $this->qb->where($primaryKey, $entity->getPrimaryKeyValue())->select()->rowCount();
        }

        foreach ($entity->getPropertiesInitializeds() as $name => $value) {
            $this->qb->where($name, $value);
        }

        return $this->qb->select()->rowCount();
    }
}
