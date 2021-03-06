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

    public function clear()
    {
        foreach ($this as $attr => $_) {
            if ($attr === 'table') {
                continue;
            }

            if (is_string($this->$attr)) {
                $this->$attr = "";
            }

            if (is_array($this->$attr)) {
                $this->$attr = [];
            }
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
        if (is_bool($param)) {
            $param = $param ? '1' : '0';
        }
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
            $cols[] = "`{$col}` = :set{$col}";
            $this->addParam(":set{$col}", $value);
        }

        $cols = implode(', ', $cols);

        $this->statement .= "{$cols} ";

        [$_success, $_stmt, $count] = $this->execute($this->statement . $this->where);

        return !!$count;
    }

    public function insert(array $datas)
    {
        $this->statement = "INSERT INTO `{$this->table}` ";

        $cols = [];
        $values = [];

        foreach ($datas as $col => $value) {
            $cols[] = "`$col`";
            $values[] = ":{$col}";
            $this->addParam(":{$col}", $value);
        }

        $cols = implode(',', $cols);
        $values = implode(',', $values);

        $this->statement .= "({$cols}) VALUES ({$values})";

        $result = $this->execute($this->statement);
        $result[] = $this->connection->getInstance()->lastInsertId();

        return $result;
    }

    public function delete(): bool
    {
        $this->statement = "DELETE FROM `{$this->table}` ";

        [$_success, $_stmt, $count] = $this->execute($this->statement . $this->where);

        return !!$count;
    }

    private function execute(string $sql)
    {
        // dd($sql);   
        $db = $this->connection->getInstance();
        $stmt = $db->prepare($sql);
        // dd($this->params);
        $result = $stmt->execute($this->params);
        $this->clear();
        return [$result, $stmt, $stmt->rowCount()];
    }
}
