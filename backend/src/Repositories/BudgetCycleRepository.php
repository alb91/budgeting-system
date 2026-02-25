<?php 

namespace App\Repositories; 

use App\Database; 
use PDO; 

class BudgetCycleRepository
{
    private const TABLE = 'budget_cycles'; 

    public static function getAll(): array
    {
        $pdo = Database::connect(); 

        $stmt = $pdo->query("
            SELECT id, name, start_date, end_date, budget
            FROM " . self::TABLE . "
            WHERE status != 2
            ORDER BY id DESC
        ");

        return $stmt->fetchALL(\PDO::FETCH_ASSOC); 
    }

    public static function create(array $data): array
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction(); 

            $stmt = $pdo->prepare("
                INSERT INTO " . self::TABLE . " (name, start_date, end_date, budget)
                VALUES (:name, :start_date, :end_date, :budget)
            ");
                
            $stmt->execute($data);
            $id = $pdo->lastInsertId(); 
            $pdo->commit(); 
            
            return [
                'id' => $id, 
                ...$data
            ]; 
        } catch (\Throwable $e) {
            $pdo->rollback(); 
            throw $e;  
        }
    }

    public static function update(int $id, array $data): array
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction(); 

            $stmt = $pdo->prepare("
                UPDATE " . self::TABLE . "
                SET
                    name = :name,
                    start_date = :start_date,
                    end_date = :end_date,
                    budget = :budget
                WHERE id = :id
                ");
            
            $stmt->execute([
                ':name'       => $data['name'],
                ':start_date' => $data['start_date'],
                ':end_date'   => $data['end_date'],
                ':budget'     => $data['budget'],
                ':id'         => $id
            ]);

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Budget cycle not found or no changes applied'); 
            }

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
                throw new \RuntimeException('Budget cycle not found or no changes applied'); 
            }
            
            $pdo->commit(); 

        } catch (\Throwable $e) {
            $pdo->rollBack(); 
            throw $e; 
        }
    }

    public static function getById(int $id): array
    {
        $pdo = Database::connect(); 

        $stmt = $pdo->prepare("
            SELECT id, name, start_date, end_date, budget
            FROM " . self::TABLE . "
            WHERE id = :id
            AND status = 1
        "); 

        $stmt->execute([':id' => $id]); 

        $result = $stmt->fetch(PDO::FETCH_ASSOC); 

        return $result ?: null; 
    }
}