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

    public function findByName(string $pcName): ?object
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM pcs
            WHERE pc_name = :pc_name
            LIMIT 1
        ");

        $stmt->execute([
            ':pc_name' => $pcName
        ]);

        $pc = $stmt->fetch();

        return $pc ?: null;
    }

    public function findByClientToken(string $clientToken): ?object
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM pcs
            WHERE client_token = :client_token
            LIMIT 1
        ");

        $stmt->execute([
            ':client_token' => $clientToken
        ]);

        $pc = $stmt->fetch();

        return $pc ?: null;
    }

    public function findByFingerprint(?string $macAddress, ?string $computerName): ?object
    {
        $macAddress = trim((string)$macAddress);
        $computerName = trim((string)$computerName);

        if ($macAddress === '' && $computerName === '') {
            return null;
        }

        $conditions = [];
        $params = [];

        if ($macAddress !== '') {
            $conditions[] = "mac_address = :mac_address";
            $params[':mac_address'] = $macAddress;
        }

        if ($computerName !== '') {
            $conditions[] = "computer_name = :computer_name";
            $params[':computer_name'] = $computerName;
        }

        $sql = "
            SELECT *
            FROM pcs
            WHERE (" . implode(' OR ', $conditions) . ")
            AND approval_status != 'rejected'
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $pc = $stmt->fetch();

        return $pc ?: null;
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM pcs
            WHERE status = :status
            AND approval_status = 'approved'
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
                computer_name,
                ip_address,
                mac_address,
                status,
                approval_status,
                sync_status
            ) VALUES (
                :pc_name,
                :computer_name,
                :ip_address,
                :mac_address,
                :status,
                'approved',
                'pending'
            )
        ");

        $stmt->execute([
            ':pc_name' => $data['pc_name'],
            ':computer_name' => $data['computer_name'] ?? null,
            ':ip_address' => $data['ip_address'] ?: null,
            ':mac_address' => $data['mac_address'] ?: null,
            ':status' => $data['status']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function createPendingClient(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO pcs (
                pc_name,
                computer_name,
                ip_address,
                mac_address,
                status,
                approval_status,
                client_token,
                registered_at,
                sync_status
            ) VALUES (
                :pc_name,
                :computer_name,
                :ip_address,
                :mac_address,
                'locked',
                'pending',
                :client_token,
                NOW(),
                'pending'
            )
        ");

        $stmt->execute([
            ':pc_name' => $data['pc_name'],
            ':computer_name' => $data['computer_name'] ?: null,
            ':ip_address' => $data['ip_address'] ?: null,
            ':mac_address' => $data['mac_address'] ?: null,
            ':client_token' => $data['client_token']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE pcs
            SET
                pc_name = :pc_name,
                computer_name = COALESCE(:computer_name, computer_name),
                ip_address = :ip_address,
                mac_address = :mac_address,
                sync_status = 'pending'
            WHERE id = :id
        ");

        $stmt->execute([
            ':pc_name' => $data['pc_name'],
            ':computer_name' => $data['computer_name'] ?? null,
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

    public function updateClientToken(int $id, string $clientToken): void
    {
        $stmt = $this->db->prepare("
            UPDATE pcs
            SET
                client_token = :client_token,
                sync_status = 'pending'
            WHERE id = :id
        ");

        $stmt->execute([
            ':client_token' => $clientToken,
            ':id' => $id
        ]);
    }

    public function updateHeartbeat(
        int $id,
        ?string $ipAddress = null,
        ?string $macAddress = null,
        ?string $computerName = null
    ): void {
        $stmt = $this->db->prepare("
            UPDATE pcs
            SET
                last_heartbeat = NOW(),
                ip_address = COALESCE(:ip_address, ip_address),
                mac_address = COALESCE(:mac_address, mac_address),
                computer_name = COALESCE(:computer_name, computer_name),
                status = CASE
                    WHEN status = 'offline' THEN 'locked'
                    ELSE status
                END,
                sync_status = 'pending'
            WHERE id = :id
        ");

        $stmt->execute([
            ':ip_address' => $ipAddress ?: null,
            ':mac_address' => $macAddress ?: null,
            ':computer_name' => $computerName ?: null,
            ':id' => $id
        ]);
    }


    public function getApproved(): array
{
    $stmt = $this->db->query("
        SELECT *
        FROM pcs
        WHERE approval_status = 'approved'
        ORDER BY id ASC
    ");

    return $stmt->fetchAll();
}

public function getPending(): array
{
    $stmt = $this->db->query("
        SELECT *
        FROM pcs
        WHERE approval_status = 'pending'
        ORDER BY registered_at DESC, id DESC
    ");

    return $stmt->fetchAll();
}

public function approveClient(int $id, string $pcName): void
{
    $stmt = $this->db->prepare("
        UPDATE pcs
        SET
            pc_name = :pc_name,
            approval_status = 'approved',
            status = 'locked',
            approved_at = NOW(),
            rejected_at = NULL,
            sync_status = 'pending'
        WHERE id = :id
    ");

    $stmt->execute([
        ':pc_name' => $pcName,
        ':id' => $id
    ]);
}

public function rejectClient(int $id): void
{
    $stmt = $this->db->prepare("
        UPDATE pcs
        SET
            approval_status = 'rejected',
            status = 'offline',
            rejected_at = NOW(),
            sync_status = 'pending'
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);
}
}