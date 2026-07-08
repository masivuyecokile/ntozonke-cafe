<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findActiveByUsername(string $username): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch();

        return $user ?: null;
    }
}