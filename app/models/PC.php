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

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM pcs
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $pc = $stmt->fetch();

        return $pc ?: null;
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

        return (int)($row->total ?? 0);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO pcs (
                pc_name,
                ip_address,
                mac_address,
                status,
                sync_status
            ) VALUES (
                :pc_name,
                :ip_address,
                :mac_address,
                :status,
                'pending'
            )
        ");

        $stmt->execute([
            ':pc_name' => $data['pc_name'],
            ':ip_address' => $data['ip_address'] ?: null,
            ':mac_address' => $data['mac_address'] ?: null,
            ':status' => $data['status']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE pcs
            SET
                pc_name = :pc_name,
                ip_address = :ip_address,
                mac_address = :mac_address,
                sync_status = 'pending'
            WHERE id = :id
        ");

        $stmt->execute([
            ':pc_name' => $data['pc_name'],
            ':ip_address' => $data['ip_address'] ?: null,
            ':mac_address' => $data['mac_address'] ?: null,
            ':id' => $id
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare("
            UPDATE pcs
            SET
                status = :status,
                sync_status = 'pending'
            WHERE id = :id
        ");

        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }
}