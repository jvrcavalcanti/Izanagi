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
        string $host = '127.0.0.1',
        int $port = 3306,
        string $charset = "uft8",
        ?string $sock = null
    ) {
        $string = "{$driver}";

        if ($driver == "sqlite") {
            $string .= ":{$name}";
        } else {
            $string .= ":host={$host}:{$port};dbname={$name};charset={$charset}";
        }

        if (!is_null($sock)) {
            $string .= ";unix_socket={$sock}";
        }

        $this->instance = new PDO($string, $user, $password);
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
            driver: $config['driver'] ?? 'mysql',
            host: $config['host'] ?? '127.0.0.1',
            port: $config['port'] ?? 3306,
            charset: $config['charset'] ?? 'utf8',
            sock: $config['sock'] ?? null
        );
    }

    public static function fromConstDBConfig()
    {
        if (!defined('DB_CONFIG')) {
            throw new \RuntimeException("Const [DB_CONFIG] is not defined");
        }
        return Connection::fromConfig(DB_CONFIG);
    }
}
