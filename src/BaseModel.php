<?php

namespace Accolon\Izanagi;

use Accolon\Izanagi\Attributes\{
    Entity,
    Field
};
use Accolon\Izanagi\Types\CharsetType;

abstract class BaseEntity
{
    protected Connection $connection;
    protected \ReflectionClass $reflector;
    protected string $charset = CharsetType::UFT8;

    public function __construct(?Connection $connection = null)
    {
        if (!$connection) {
            $connection = Connection::fromConstDBConfig();
        }

        $this->connection = $connection;
        $this->reflector = new \ReflectionClass(static::class);

        $this->migrate();
    }

    public function getModelName()
    {
        return $this
                    ->reflector
                    ->getAttributes(Entity::class)[0]
                    ->getArguments()['name'];
    }

    public function getFields()
    {
        return array_map(
            function (\ReflectionProperty $reflectorProp) {
                $data = $reflectorProp
                            ->getAttributes(Field::class)[0]
                            ->getArguments();
                $data['name'] = $reflectorProp->getName();
                return $data;
            },
            $this->reflector->getProperties(
                \ReflectionProperty::IS_PRIVATE
            )
        );
    }

    protected function migrate()
    {
        $tableName = $this->getModelName();

        $connection = $this->connection->getInstance();

        // $connection->prepare("DROP IF EXISTS `{$tableName}`;")->execute();

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}`(";

        $fields = [];

        foreach ($this->getFields() as $field) {
            $fields[] = Connection::convertArrayInString($field);
        }

        $sql .= implode(", ", $fields) . ");";

        var_dump($sql);

        // $connection->prepare($sql)->execute();
    }
}
