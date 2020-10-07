<?php

namespace Accolon\Izanagi;

final class QueryBuilder
{
    private Connection $connection;

    private string $table;
    private string $statement = "";
    private string $where = "";
    private array $params = [];
    private string $limit = "";
    private string $offset = "";

    public function __construct(string $table, ?Connection $connection = null)
    {
        $this->table = $table;

        if (is_null($connection)) {
            $this->connection = Connection::fromConstDBConfig();
        } else {
            $this->connection = $connection;
        }
    }

    public function limit(int $limit)
    {
        $this->limit = "LIMIT {$limit} ";
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = "OFFSET {$offset} ";
        return $this;
    }

    public function where(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= "AND ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $this->addParam(":{$col}", $value);
            $value = ":{$col}";
            $exp = "=";
        }

        if (sizeof($params) == 3) {
            [$col, $exp, $value] = $params;
            $this->addParam(":{$col}", $value);
            $value = ":{$col}";
        }

        $this->where .= "`{$col}` {$exp} {$value} ";

        return $this;
    }

    public function whereIn(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= "AND ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $exp = "IN";
            $value = json_encode($value);
            $this->addParam(":{$col}", $value);
            $value = ":{$col}";
        }

        $this->where .= "`{$col}` {$exp} {$value} ";

        return $this;
    }

    public function whereOr(...$params)
    {
        if ($this->where === "") {
            $this->where = "WHERE ";
        } else {
            $this->where .= "OR ";
        }
        
        if (sizeof($params) == 2) {
            [$col, $value] = $params;
            $this->addParam(":{$col}", $value);
            $value = ":{$col}";
            $exp = "=";
        }

        if (sizeof($params) == 3) {
            [$col, $exp, $value] = $params;
            $this->addParam(":{$col}", $value);
            $value = ":{$col}";
        }

        $this->where .= "`{$col}` {$exp} {$value} ";

        return $this;
    }

    public function addParam($key, $param)
    {
        $this->params[$key] = $param;
        return $this;
    }

    public function select($cols = ['*'])
    {
        $cols = sizeof($cols) > 1 ? implode(',', $cols) : $cols[0];
        $this->statement = "SELECT {$cols} FROM `{$this->table}` ";

        [$_success, $stmt] = $this->execute($this->statement . $this->where . $this->limit . $this->offset);
        return $stmt;
    }

    public function update(array $datas): bool
    {
        $this->statement = "UPDATE `{$this->table}` SET ";

        $cols = [];

        foreach ($datas as $col => $value) {
            $cols[] = "{$col} = :{$col}";
            $this->addParam(":{$col}", $value);
        }

        $cols = implode(', ', $cols);

        $this->statement .= "{$cols} ";

        [$success, $_stmt] = $this->execute($this->statement . $this->where);

        return $success;
    }

    public function insert(array $datas): bool
    {
        $this->statement = "INSERT INTO `{$this->table}` ";

        $cols = [];
        $values = [];

        foreach ($datas as $col => $value) {
            $cols[] = $col;
            $values[] = ":{$col}";
            $this->addParam(":{$col}", $value);
        }

        $cols = implode(',', $cols);
        $values = implode(',', $values);

        $this->statement .= "({$cols}) VALUES ({$values})";

        [$success, $_stmt] = $this->execute($this->statement);

        return $success;
    }

    public function delete(): bool
    {
        $this->statement = "DELETE FROM `{$this->table}`";

        [$success, $_stmt] = $this->execute($this->statement . $this->where);

        return $success;
    }

    private function execute(string $sql)
    {
        var_dump($this);
        dd($sql);
        $db = $this->connection->getInstance();
        $stmt = $db->prepare($sql);
        return [$stmt->execute($this->params), $stmt];
    }

    // final public static function __callStatic( $chrMethod, $arrArguments ) {
           
    //     $objInstance = self::getInstance();
       
    //     return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
       
    // }
}