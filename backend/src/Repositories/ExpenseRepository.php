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
        SELECT id, name, date, amount
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
                INSERT INTO " . self::TABLE . " (cycle_id, name, date, amount)
                VALUES (:cycle_id, :name, :date, :amount)
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

            $stmt = $pdo->prepare("
                UPDATE " . self::TABLE ."
                SET
                    name = :name,
                    date = :date,
                    amount = :amount
                WHERE id = :id
            ");

            $stmt->execute([
                ':name'   => $data['name'],
                ':date'   => $data['date'],
                ':amount' => $data['amount'],
                ':id'     => $id
            ]);

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Expense not found o no changes applied'); 
            }

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
}