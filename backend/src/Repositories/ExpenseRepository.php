<?php 

namespace App\Repositories; 

use App\Database; 
use PDO; 

class ExpenseRepository
{
    private const TABLE = 'expenses'; 

    public static function getAllByCycle(int $cycleId): array
    {
        $pdo = Database::connect(); 

        $stmt = $pdo->prepare("
        SELECT id, name, date, amount, category, notify
        FROM " . self::TABLE . "
        WHERE cycle_id = :cycle_id
        AND status = 1
        ORDER BY id DESC
        "); 

        $stmt->execute([
            ':cycle_id' => $cycleId
        ]); 

        return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
    }

    public static function create(array $data): array
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO " . self::TABLE . " (cycle_id, name, date, amount, category, notify)
                VALUES (:cycle_id, :name, :date, :amount, :category, :notify)
            ");

            $stmt->execute($data); 
            $id = $pdo->lastInsertId(); 
            $pdo->commit(); 

            return [
                'id' => $id,
                ...$data
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack(); 
            throw $e; 
        }
    }

    public static function update(int $id, array $data): array
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction();

            $check = $pdo->prepare("
                SELECT 1
                FROM " . self::TABLE . "
                WHERE id = :id    
            ");

            $check->execute([":id" => $id]); 

            if(!$check->fetchColumn()){
                throw new \RuntimeException('Expense not found'); 
            }

            $stmt = $pdo->prepare("
                UPDATE " . self::TABLE ."
                SET
                    name = :name,
                    date = :date,
                    amount = :amount,
                    category = :category,
                    notify = :notify
                WHERE id = :id
            ");

            $stmt->execute([
                ':name'     => $data['name'],
                ':date'     => $data['date'],
                ':amount'   => $data['amount'],
                ':category' => $data['category'],
                ':notify'   => $data['notify'],
                ':id'       => $id
            ]);

            $pdo->commit(); 

            return [
                'id' => $id,
                ...$data
            ];
        } catch ( \Throwable $e) {
            $pdo->rollBack(); 
            throw $e; 
        }
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction(); 

            $stmt = $pdo->prepare("
                UPDATE " . self::TABLE . "
                SET status = 2
                WHERE id = :id
            "); 

            $stmt->execute(['id' => $id]); 

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Expense not found or no changes applied'); 
            }

            $pdo->commit(); 
        } catch (\Throwable $e) {
            $pdo->rollBack(); 
            throw $e; 
        }
    }

    public static function getPendingNotifications(): array
    {
        $pdo = Database::connect(); 
        
        $stmt = $pdo->prepare("
            SELECT 
                e.id, 
                e.cycle_id,
                e.name,
                e.date,
                e.amount
            FROM " . self::TABLE . " e
            LEFT JOIN budget_cycles bc
                ON e.cycle_id = bc.id
            WHERE 
                bc.status = 1
                AND e.status = 1
                AND e.notify = 1
                AND e.notification_sent IS NULL
                AND e.DATE(date) = CURDATE()
        ");

        $stmt->execute(); 

        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    public static function markNotificationSent(int $expenseId): void
    {
        $pdo = Database::connect(); 

        $stmt = $pdo->prepare("
            UPDATE " . self::TABLE ."
            SET 
                notification_sent = 1,
                notification_sent_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $expenseId
        ]);
    }
}