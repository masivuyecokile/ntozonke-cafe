<?php

class Database
{
    private string $host = 'localhost';
    private string $dbName = 'ntozonke_cafe';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn = null;

    public function connect(): PDO
    {
        if ($this->conn === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ]);
        }

        return $this->conn;
    }
}