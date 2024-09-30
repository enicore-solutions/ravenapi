<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

use PDO;
use PDOStatement;

class Database
{
    use Injection;
    use Singleton;
    protected PDO $pdo;

    public function __construct(array|null $settings = [])
    {
        $this->pdo = new PDO(
            "mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['database']}",
            $settings['username'],
            $settings['password'],
            $settings['options'] ?? []
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("SET CHARACTER SET utf8;SET NAMES utf8;");
    }

    public function query($query, $parameters = false): bool|PDOStatement
    {
        if (!$parameters) {
            $parameters = [];
        } else if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        if (($st = $this->pdo->prepare($query)) && $st->execute($parameters)) {
            return $st;
        }

        return false;
    }

    public function all($query, $parameters = false): array
    {
        if ($st = $this->query($query, $parameters)) {
            return $st->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    public function row($query, $parameters = false)
    {
        // get a single row from from db table, return it in an array or false if no rows or error

        if ($st = $this->query($query, $parameters)) {
            if ($array = $st->fetch(PDO::FETCH_ASSOC)) {
                return $array;
            }
        }

        return false;
    }

    public function getAll(string $table, $selectFields = "*", array $whereParams = [], $orderBy = null, $orderType = "ASC", $limit = null, $offset = null): bool|array
    {
        !is_array($selectFields) && ($selectFields = [$selectFields]);
        $params = [];
        $where = [];

        foreach ($whereParams as $key => $val) {
            $where[] = "$key=:$key";
            $params[":" . $key] = $val;
        }

        $fields = implode(",", $selectFields);
        $where = !empty($where) ? "WHERE " . implode("&&", $where) : "";
        $order = $orderBy ? "ORDER BY $orderBy $orderType" : "";
        $limit = $limit ? "LIMIT $limit" . ($offset ? ",$offset" : "") : "";

        if (($st = $this->query("SELECT $fields FROM `$table` $where $order $limit", $params)) && ($array = $st->fetchAll(PDO::FETCH_ASSOC))) {
            return $array;
        }

        return false;
    }

    public function getFirst(string $table, $selectFields = "*", array $whereParams = [], $orderBy = false, $orderType = "ASC")
    {
        !is_array($selectFields) && ($selectFields = [$selectFields]);
        $params = [];
        $where = [];

        foreach ($whereParams as $key => $val) {
            $where[] = "$key=:$key";
            $params[":" . $key] = $val;
        }

        $fields = implode(",", $selectFields);
        $where = !empty($where) ? "WHERE " . implode("&&", $where) : "";
        $order = $orderBy ? "ORDER BY $orderBy $orderType" : "";

        if (($st = $this->query("SELECT $fields FROM `$table` $where $order LIMIT 1", $params)) && ($array = $st->fetch(PDO::FETCH_ASSOC))) {
            return $array;
        }

        return false;
    }

    public function getLastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    public function getLastError()
    {
        // get query error for the last query
        $array = $this->pdo->errorInfo();
        return $array[2];
    }

    public function exists(string $table, string $whereQuery = "", array $whereParameters = []): bool
    {
        if ($whereQuery) {
            $whereQuery = "WHERE " . $whereQuery;
        }

        return ($st = $this->query("SELECT * FROM `$table` $whereQuery", $whereParameters)) && $st->fetch(PDO::FETCH_COLUMN);
    }

    public function count(string $table, string $whereQuery = "", array $whereParameters = [])
    {
         if ($whereQuery) {
            $whereQuery = "WHERE " . $whereQuery;
        }

        if ($st = $this->query("SELECT COUNT(*) FROM `$table` $whereQuery", $whereParameters)) {
            return $st->fetch(PDO::FETCH_COLUMN);
        }

        return false;
    }

    public function insert(string $table, array $data): bool|PDOStatement
    {
        $keys = [];
        $values = [];
        $params = [];

        foreach ($data as $key => $val) {
            $keys[] = $key;
            $values[] = ":" . $key;
            $params[":" . $key] = $val;
        }

        return $this->query("INSERT INTO `$table` (" . implode(",", $keys) . ") VALUES (" . implode(",", $values) . ")", $params);
    }

    public function update(string $table, array $data, array $whereParams): bool|PDOStatement
    {
        $values = [];
        $params = [];
        $where = [];

        foreach ($data as $key => $val) {
            $values[] = "$key=:$key";
            $params[":" . $key] = $val;
        }

        foreach ($whereParams as $key => $val) {
            $where[] = "$key=:$key";
            $params[":" . $key] = $val;
        }

        return $this->query("UPDATE `$table` SET " . implode(",", $values) . " WHERE " . implode("&&", $where), $params);
    }

    public function delete(string $table, array $whereParams): bool|PDOStatement
    {
        $params = [];
        $where = [];

        foreach ($whereParams as $key => $val) {
            $where[] = "$key=:$key";
            $params[":" . $key] = $val;
        }

        return $this->query("DELETE FROM `$table` WHERE " . implode("&&", $where), $params);
    }
}
