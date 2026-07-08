<?php

class CafeSession
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getActiveByPcId(int $pcId): ?object
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM sessions
            WHERE pc_id = :pc_id
            AND status = 'active'
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([
            ':pc_id' => $pcId
        ]);

        $session = $stmt->fetch();

        return $session ?: null;
    }

    public function getActiveSessionsIndexedByPc(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM sessions
            WHERE status = 'active'
            ORDER BY id DESC
        ");

        $sessions = $stmt->fetchAll();
        $indexed = [];

        foreach ($sessions as $session) {
            $indexed[$session->pc_id] = $session;
        }

        return $indexed;
    }

    public function startSession(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $pcStmt = $this->db->prepare("
                SELECT *
                FROM pcs
                WHERE id = :pc_id
                FOR UPDATE
            ");

            $pcStmt->execute([
                ':pc_id' => $data['pc_id']
            ]);

            $pc = $pcStmt->fetch();

            if (!$pc) {
                throw new Exception('PC not found.');
            }

            if ($pc->status === 'active') {
                throw new Exception('This PC already has an active session.');
            }

            $startTime = new DateTime('now', new DateTimeZone('Africa/Johannesburg'));
            $endTime = clone $startTime;
            $endTime->modify('+' . (int)$data['minutes'] . ' minutes');

            $stmt = $this->db->prepare("
                INSERT INTO sessions (
                    pc_id,
                    customer_name,
                    start_time,
                    end_time,
                    minutes_purchased,
                    rate_per_minute,
                    amount_due,
                    status,
                    created_by
                ) VALUES (
                    :pc_id,
                    :customer_name,
                    :start_time,
                    :end_time,
                    :minutes_purchased,
                    :rate_per_minute,
                    :amount_due,
                    'active',
                    :created_by
                )
            ");

            $stmt->execute([
                ':pc_id' => $data['pc_id'],
                ':customer_name' => $data['customer_name'],
                ':start_time' => $startTime->format('Y-m-d H:i:s'),
                ':end_time' => $endTime->format('Y-m-d H:i:s'),
                ':minutes_purchased' => $data['minutes'],
                ':rate_per_minute' => $data['rate_per_minute'],
                ':amount_due' => $data['amount_due'],
                ':created_by' => $data['created_by']
            ]);

            $sessionId = (int)$this->db->lastInsertId();

            $updatePc = $this->db->prepare("
                UPDATE pcs
                SET status = 'active',
                    sync_status = 'pending'
                WHERE id = :pc_id
            ");

            $updatePc->execute([
                ':pc_id' => $data['pc_id']
            ]);

            $this->db->commit();

            return $sessionId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function endSession(int $sessionId, int $endedBy): object
{
    $this->db->beginTransaction();

    try {
        $stmt = $this->db->prepare("
            SELECT *
            FROM sessions
            WHERE id = :session_id
            AND status = 'active'
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([
            ':session_id' => $sessionId
        ]);

        $session = $stmt->fetch();

        if (!$session) {
            throw new Exception('Active session not found or already ended.');
        }

        $actualEndTime = new DateTime('now', new DateTimeZone('Africa/Johannesburg'));

        $updateSession = $this->db->prepare("
            UPDATE sessions
            SET status = 'ended',
                actual_end_time = :actual_end_time,
                sync_status = 'pending'
            WHERE id = :session_id
        ");

        $updateSession->execute([
            ':actual_end_time' => $actualEndTime->format('Y-m-d H:i:s'),
            ':session_id' => $sessionId
        ]);

        $updatePc = $this->db->prepare("
            UPDATE pcs
            SET status = 'locked',
                sync_status = 'pending'
            WHERE id = :pc_id
        ");

        $updatePc->execute([
            ':pc_id' => $session->pc_id
        ]);

        $saleCheck = $this->db->prepare("
            SELECT id
            FROM sales
            WHERE session_id = :session_id
            AND sale_type = 'internet'
            LIMIT 1
        ");

        $saleCheck->execute([
            ':session_id' => $sessionId
        ]);

        $existingSale = $saleCheck->fetch();

        if (!$existingSale) {
            $saleStmt = $this->db->prepare("
                INSERT INTO sales (
                    session_id,
                    sale_type,
                    description,
                    amount,
                    payment_method,
                    created_by,
                    sync_status
                ) VALUES (
                    :session_id,
                    'internet',
                    :description,
                    :amount,
                    'cash',
                    :created_by,
                    'pending'
                )
            ");

            $saleStmt->execute([
                ':session_id' => $sessionId,
                ':description' => 'Internet session - ' . $session->customer_name,
                ':amount' => $session->amount_due,
                ':created_by' => $endedBy
            ]);
        }

        $this->db->commit();

        return $session;

    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
}