<?php

namespace App\Services;
use App\Repositories\CategoriesRepository; 

class CategoriesService
{
    public static function getAll(): array
    {
        return CategoriesRepository::getAll();
    }

    public static function create(array $data): array
    {   
        self::validate($data);

        return CategoriesRepository::create([
            'name'  => strtolower($data['name']),
            'color' => $data['color']
        ]);
    }

    public static function update(int $id, array $data): array
    {
        if (!$id) {
            throw new \InvalidArgumentException('ID is required');
        }

        self::validate($data); 

        return CategoriesRepository::update($id, [
            'name'  => strtolower($data['name']),
            'color' => $data['color']
        ]);
    }

    public static function delete(int $id): void
    {
        CategoriesRepository::delete($id);
    } 

    private static function validate(array $data): void
    {
        $requiredFields = ['name', 'color'];

        foreach($requiredFields as $field) {
            if(!array_key_exists($field, $data) || $data[$field] == '' || $data[$field] == null) {
                throw new \InvalidArgumentException("{$field} is required");
            }
        }
    }
}