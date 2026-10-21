<?php

namespace App\Repositories;

use App\Database; 
use PDO;

class CategoriesRepository
{
    private const TABLE = 'categories';

    public static function getAll(): array
    {
        $pdo = Database::connect();

        $stmt = $pdo->query("
            SELECT id, name, color
            FROM " . self::TABLE ." c
            WHERE status = 1
            ORDER BY id DESC
        ");

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result ?: []; 
    }

    public static function create(array $data): array
    {
        $pdo = Database::connect(); 

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO " . self::TABLE . " (name, color)
                VALUES (:name, :color)
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
                    color = :color
                WHERE id = :id
                ");

            $stmt->execute([
                ':name'  => $data['name'],
                ':color' => $data['color'],
                ':id'    => $id
            ]);

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Category not found'); 
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
                UPDATE " . self::TABLE ."
                SET status = 2
                WHERE id = :id
            ");

            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Category not found or no changes applied'); 
            }

            $pdo->commit(); 

        } catch (\Throwable $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}