<?php

namespace Accolon\Izanagi;

use PDO;

class Connection
{
    private PDO $instance;

    public function __construct(
        string $name,
        string $user,
        string $password,
        string $driver = "mysql",
        string $host = "localhost",
        int $port = 3306,
        string $charset = "uft8"
    ) {
        if ($driver == "sqlite") {
            $this->instance = new PDO("{$driver}:{$name}");
            return;
        }
        
        $this->instance = new PDO(
            "{$driver}:host={$host};dbname={$name};port={$port};charset={$charset}",
            $user,
            $password
        );
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public static function fromConfig(array $config): Connection
    {
        return new Connection(
            name: $config['name'],
            user: $config['user'],
            password: $config['password'],
            driver: $config['driver'] ?? "mysql",
            host: $config['host'] ?? "localhost",
            port: $config['port'] ?? 3306,
            charset: $config['charset'] ?? "utf8"
        );
    }

    public static function fromConstDBConfig()
    {
        return Connection::fromConfig(DB_CONFIG);
    }

    public static function convertArrayInString(array $data)
    {
        $sql = "";

        $sql .= "`{$data['name']}` {$data['type']}({$data['length']}) ";

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
}
