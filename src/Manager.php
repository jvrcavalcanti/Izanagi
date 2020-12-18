<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\Field;
use Accolon\Izanagi\Attributes\Table;
use Accolon\Izanagi\Types\FieldType;

class Manager
{
    private array $entities = [];
    private Connection $connection;

    public function __construct(array $entities = [], ?Connection $connection = null)
    {
        $this->entities = $entities;

        if (is_null($connection)) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
    }

    public function addEntity(Entity $entity)
    {
        $this->entities[] = $entity;
    }

    public function getTableName(\ReflectionClass $reflector)
    {
        return $reflector
                    ->getAttributes(Table::class)[0]
                    ->newInstance()->name;
    }

    public function getFields(\ReflectionClass $reflector)
    {
        return array_map(
            function (\ReflectionProperty $reflectorProp) {
                $data = $reflectorProp
                            ->getAttributes(Field::class, \ReflectionAttribute::IS_INSTANCEOF)[0]
                            ->newInstance();
                $data->name = $reflectorProp->getName();
                return $data;
            },
            $reflector->getProperties(
                \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PUBLIC
            )
        );
    }

    private static function convertObjectInString(object $data)
    {
        $sql = "";

        if ($data->type === FieldType::Float) {
            [$length1, $length2] = explode(".", (string) $data->length);
        }

        $sql .= match($data->type) {
            FieldType::String => "`{$data->name}` VARCHAR({$data->length}) ",
            FieldType::Integer => "`{$data->name}` INT({$data->length}) ",
            FieldType::Boolean => "`{$data->name}` BOOLEAN ",
            FieldType::Float => "`{$data->name}` FLOAT({$length1}, {$length2}) ",
            FieldType::Date => "`{$data->name}` DATE "
        };

        if (!$data->nullable) {
            $sql .= "NOT NULL ";
        }

        if ($data->primary) {
            $sql .= "PRIMARY KEY ";
        }

        if ($data->autoIncrement) {
            $sql .= "AUTO_INCREMENT ";
        }

        if ($data->unique) {
            $sql .= "UNIQUE ";
        }

        if (!is_null($data->default)) {
            $sql .= "DEFAULT '{$data->default}' ";
        }

        return $sql;
    }

    public function migrate()
    {
        foreach ($this->entities as $entity) {
            $this->dropIfExists($entity);
            $this->createIfExists($entity);
        }
    }

    public function dropIfExists(string $entity)
    {
        $reflector = new \ReflectionClass($entity);
        $tableName = $this->getTableName($reflector);

        $this->connection->getInstance()->prepare("DROP TABLE IF EXISTS `{$tableName}`;")->execute();
    }

    public function createIfExists(string $entity)
    {
        $reflector = new \ReflectionClass($entity);
        $tableName = $this->getTableName($reflector);

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}`(";

        $fields = [];

        foreach ($this->getFields($reflector) as $field) {
            $fields[] = static::convertObjectInString($field);
        }

        $sql .= implode(", ", $fields) . ");";

        $this->connection->getInstance()->prepare($sql)->execute();
    }
}
