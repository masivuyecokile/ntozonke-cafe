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
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total_expenses,
                COALESCE(SUM(amount), 0) AS total_expense_amount
            FROM expenses
            WHERE expense_date = :expense_date
        ");

        $stmt->execute([
            ':expense_date' => $date
        ]);

        return $stmt->fetch();
    }

    public function getByDate(string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.*,
                u.name AS created_by_name
            FROM expenses e
            LEFT JOIN users u ON u.id = e.created_by
            WHERE e.expense_date = :expense_date
            ORDER BY e.id DESC
        ");

        $stmt->execute([
            ':expense_date' => $date
        ]);

        return $stmt->fetchAll();
    }
}