<?php

namespace Accolon\Izanagi;

final class QueryBuilder
{
    private Connection $connection;

    private string $table;
    private string $statement = "";
    private string $where = "";
    private array $params = [];

    public function __construct(string $table, ?Connection $connection = null)
    {
        $this->table = $table;

        if (is_null($connection)) {
            $this->connection = Connection::fromConstDBConfig();
        } else {
            $this->connection = $connection;
        }
    }

    public function where(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= " AND ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $exp = "=";
        }

        if (sizeof($params) == 3) {
            [$col, $exp, $value] = $params;
        }

        $this->where .= "{$col} {$exp} {$value}";

        return $this;
    }

    public function whereIn(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= " AND ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $exp = "IN";
            $value = json_encode($value);
        }

        $this->where .= "{$col} {$exp} {$value}";

        return $this;
    }

    public function whereOr(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= " OR ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $exp = "=";
        }

        if (sizeof($params) == 3) {
            [$col, $exp, $value] = $params;
        }

        $this->where .= "{$col} {$exp} {$value}";

        return $this;
    }

    public function select($cols = ['*'], int $fetch = \PDO::FETCH_LAZY)
    {
        $cols = sizeof($cols) > 1 ? implode(',', $cols) : $cols[0];
        $this->statement = "SELECT {$cols} FROM {$this->table} ";

        return $this->execute($this->statement . $this->where)->fetch($fetch);
    }

    public function execute(string $sql)
    {
        dd($sql);
        $db = $this->connection->getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($this->params);
        return $stmt;
    }

    // final public static function __callStatic( $chrMethod, $arrArguments ) {
           
    //     $objInstance = self::getInstance();
       
    //     return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
       
    // }
}