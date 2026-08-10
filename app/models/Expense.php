<?php

class Expense
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function create(array $data, int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO expenses (
                expense_date,
                category,
                description,
                amount,
                payment_method,
                created_by,
                sync_status
            ) VALUES (
                :expense_date,
                :category,
                :description,
                :amount,
                :payment_method,
                :created_by,
                'pending'
            )
        ");

        $stmt->execute([
            ':expense_date' => $data['expense_date'],
            ':category' => $data['category'],
            ':description' => $data['description'],
            ':amount' => $data['amount'],
            ':payment_method' => $data['payment_method'],
            ':created_by' => $createdBy
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getStatsByDate(string $date): object
    {
        return $this->getStatsByRange($date, $date);
    }

    public function getStatsByRange(string $startDate, string $endDate): object
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total_expenses,
                COALESCE(SUM(amount), 0) AS total_expense_amount,

                COALESCE(SUM(CASE WHEN category = 'paper' THEN amount ELSE 0 END), 0) AS paper_total,
                COALESCE(SUM(CASE WHEN category = 'ink_toner' THEN amount ELSE 0 END), 0) AS ink_toner_total,
                COALESCE(SUM(CASE WHEN category = 'electricity' THEN amount ELSE 0 END), 0) AS electricity_total,
                COALESCE(SUM(CASE WHEN category = 'rent' THEN amount ELSE 0 END), 0) AS rent_total,
                COALESCE(SUM(CASE WHEN category = 'internet' THEN amount ELSE 0 END), 0) AS internet_total,
                COALESCE(SUM(CASE WHEN category = 'repairs' THEN amount ELSE 0 END), 0) AS repairs_total,
                COALESCE(SUM(CASE WHEN category = 'stock' THEN amount ELSE 0 END), 0) AS stock_total,
                COALESCE(SUM(CASE WHEN category = 'other' THEN amount ELSE 0 END), 0) AS other_total,

                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method = 'eft' THEN amount ELSE 0 END), 0) AS eft_total
            FROM expenses
            WHERE expense_date BETWEEN :start_date AND :end_date
        ");

        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        return $stmt->fetch();
    }

    public function getByDate(string $date): array
    {
        return $this->getByRange($date, $date);
    }

    public function getByRange(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.*,
                u.name AS created_by_name
            FROM expenses e
            LEFT JOIN users u ON u.id = e.created_by
            WHERE e.expense_date BETWEEN :start_date AND :end_date
            ORDER BY e.expense_date DESC, e.id DESC
        ");

        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        return $stmt->fetchAll();
    }
}