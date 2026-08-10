<?php

class Sale
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getTodayStats(): object
    {
        return $this->getStatsByDate(date('Y-m-d'));
    }

    public function getStatsByDate(string $date): object
    {
        return $this->getStatsByRange($date, $date);
    }

    public function getStatsByRange(string $startDate, string $endDate): object
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total_sales,
                COALESCE(SUM(amount), 0) AS total_revenue,

                COALESCE(SUM(CASE WHEN sale_type = 'internet' THEN amount ELSE 0 END), 0) AS internet_revenue,
                COALESCE(SUM(CASE WHEN sale_type = 'printing' THEN amount ELSE 0 END), 0) AS printing_revenue,
                COALESCE(SUM(CASE WHEN sale_type = 'service' THEN amount ELSE 0 END), 0) AS service_revenue,
                COALESCE(SUM(CASE WHEN sale_type = 'other' THEN amount ELSE 0 END), 0) AS other_revenue,

                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method = 'eft' THEN amount ELSE 0 END), 0) AS eft_total,
                COALESCE(SUM(CASE WHEN payment_method = 'free' THEN amount ELSE 0 END), 0) AS free_total
            FROM sales
            WHERE DATE(created_at) BETWEEN :start_date AND :end_date
        ");

        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        return $stmt->fetch();
    }

    public function getRecentToday(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,
                p.pc_name,
                pj.document_name,
                u.name AS created_by_name
            FROM sales s
            LEFT JOIN sessions cs ON cs.id = s.session_id
            LEFT JOIN pcs p ON p.id = cs.pc_id
            LEFT JOIN print_jobs pj ON pj.id = s.print_job_id
            LEFT JOIN users u ON u.id = s.created_by
            WHERE DATE(s.created_at) = CURDATE()
            ORDER BY s.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByDate(string $date): array
    {
        return $this->getByRange($date, $date);
    }

    public function getByRange(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,
                p.pc_name,
                pj.document_name,
                u.name AS created_by_name
            FROM sales s
            LEFT JOIN sessions cs ON cs.id = s.session_id
            LEFT JOIN pcs p ON p.id = cs.pc_id
            LEFT JOIN print_jobs pj ON pj.id = s.print_job_id
            LEFT JOIN users u ON u.id = s.created_by
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            ORDER BY s.created_at DESC, s.id DESC
        ");

        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        return $stmt->fetchAll();
    }

    public function createServiceSale(array $data, int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO sales (
                session_id,
                print_job_id,
                sale_type,
                description,
                amount,
                payment_method,
                created_by,
                sync_status
            ) VALUES (
                NULL,
                NULL,
                'service',
                :description,
                :amount,
                :payment_method,
                :created_by,
                'pending'
            )
        ");

        $stmt->execute([
            ':description' => $data['description'],
            ':amount' => $data['amount'],
            ':payment_method' => $data['payment_method'],
            ':created_by' => $createdBy
        ]);

        return (int)$this->db->lastInsertId();
    }
}