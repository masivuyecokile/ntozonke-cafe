<?php

class InternetPackage
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM internet_packages
            WHERE status = 'active'
            ORDER BY sort_order ASC, minutes ASC
        ");

        return $stmt->fetchAll();
    }

    public function findActiveById(int $id): ?object
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM internet_packages
            WHERE id = :id
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $package = $stmt->fetch();

        return $package ?: null;
    }
}