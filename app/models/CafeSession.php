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
                ':description' => 'Internet session start - ' . $data['customer_name'],
                ':amount' => $data['amount_due'],
                ':created_by' => $data['created_by']
            ]);

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

            $this->db->commit();

            return $session;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function extendSession(int $sessionId, int $minutes, float $amount, int $extendedBy): object
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT s.*, p.pc_name
                FROM sessions s
                LEFT JOIN pcs p ON p.id = s.pc_id
                WHERE s.id = :session_id
                AND s.status = 'active'
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

            $updateSession = $this->db->prepare("
                UPDATE sessions
                SET end_time = DATE_ADD(end_time, INTERVAL :minutes MINUTE),
                    extended_minutes = extended_minutes + :minutes_2,
                    amount_due = amount_due + :amount,
                    sync_status = 'pending'
                WHERE id = :session_id
            ");

            $updateSession->execute([
                ':minutes' => $minutes,
                ':minutes_2' => $minutes,
                ':amount' => $amount,
                ':session_id' => $sessionId
            ]);

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
                ':description' => 'Internet session extension - ' . $session->customer_name . ' (' . $minutes . ' minutes)',
                ':amount' => $amount,
                ':created_by' => $extendedBy
            ]);

            $getUpdated = $this->db->prepare("
                SELECT *
                FROM sessions
                WHERE id = :session_id
                LIMIT 1
            ");

            $getUpdated->execute([
                ':session_id' => $sessionId
            ]);

            $updatedSession = $getUpdated->fetch();

            $this->db->commit();

            return $updatedSession;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function expireOverdueSessions(): array
    {
        $this->db->beginTransaction();

        try {
            $now = new DateTime('now', new DateTimeZone('Africa/Johannesburg'));
            $nowString = $now->format('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                SELECT *
                FROM sessions
                WHERE status = 'active'
                AND end_time <= :now_time
                FOR UPDATE
            ");

            $stmt->execute([
                ':now_time' => $nowString
            ]);

            $sessions = $stmt->fetchAll();

            if (!$sessions) {
                $this->db->commit();

                return [
                    'expired_count' => 0,
                    'pc_ids' => []
                ];
            }

            $expiredPcIds = [];

            foreach ($sessions as $session) {
                $expiredPcIds[] = (int)$session->pc_id;

                $updateSession = $this->db->prepare("
                    UPDATE sessions
                    SET status = 'ended',
                        actual_end_time = :actual_end_time,
                        sync_status = 'pending'
                    WHERE id = :session_id
                ");

                $updateSession->execute([
                    ':actual_end_time' => $nowString,
                    ':session_id' => $session->id
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
            }

            $this->db->commit();

            return [
                'expired_count' => count($sessions),
                'pc_ids' => $expiredPcIds
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
