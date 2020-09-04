<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\{
    Field,
    Table
};
use Accolon\Izanagi\Types\FieldType;

class Manager
{
    private array $entities = [];

    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    public function addEntity(Entity $entity)
    {
        $this->entities[] = $entity;
    }

    public function getTableName(\ReflectionClass $reflector)
    {
        return $reflector
                    ->getAttributes(Table::class)[0]
                    ->getArguments()['name'];
    }

    public function getFields(\ReflectionClass $reflector)
    {
        return array_map(
            function (\ReflectionProperty $reflectorProp) {
                $data = $reflectorProp
                            ->getAttributes(Field::class)[0]
                            ->getArguments();
                $data['name'] = $reflectorProp->getName();
                return $data;
            },
            $reflector->getProperties(
                \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PUBLIC
            )
        );
    }

    private static function convertArrayInString(array $data)
    {
        $sql = "";

        if ($data['type'] === FieldType::Float) {
            [$length1, $length2] = explode(".", (string) $data['length']);
        }

        $sql .= match($data['type']) {
            FieldType::String => "`{$data['name']}` VARCHAR({$data['length']}) ",
            FieldType::Integer => "`{$data['name']}` INT({$data['length']}) ",
            FieldType::Boolean => "`{$data['name']}` TINYINT(1) ",
            FieldType::Float => "`{$data['name']}` FLOAT({$length1}, {$length2}) ",
            FieldType::Date => "`{$data['name']}` DATE "
        };

        if ($data['nullable'] ?? true) {
            $sql .= "NOT NULL ";
        }

        if ($data['primary'] ?? false) {
            $sql .= "PRIMARY KEY ";
        }

        if ($data['autoIncrement'] ?? false) {
            $sql .= "AUTO_INCREMENT ";
        }

        if ($data['unique'] ?? false) {
            $sql .= "UNIQUE ";
        }

        if (isset($data['default'])) {
            $sql .= "DEFAULT '{$data['default']}' ";
        }

        return $sql;
    }

    public function migrate(?Connection $connection = null)
    {
        foreach ($this->entities as $entity) {
            $reflector = new \ReflectionClass($entity);
            $tableName = $this->getTableName($reflector);

            if (is_null($connection)) {
                $connection = Connection::fromConstDBConfig();
            }

            $connection = $connection->getInstance();

            // $connection->prepare("DROP IF EXISTS `{$tableName}`;")->execute();

            $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}`(";

            $fields = [];

            foreach ($this->getFields($reflector) as $field) {
                $fields[] = static::convertArrayInString($field);
            }

            $sql .= implode(", ", $fields) . ");";

            var_dump($sql);

            // $connection->prepare($sql)->execute();
        }
    }
}
