<?php

class PC
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM pcs
            ORDER BY id ASC
        ");

        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM pcs
            WHERE status = :status
        ");

        $stmt->execute([
            ':status' => $status
        ]);

        $row = $stmt->fetch();

        return (int) ($row->total ?? 0);
    }
}