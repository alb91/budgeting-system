<?php 

namespace App\Services;
use App\Repositories\BudgetCycleRepository; 

class BudgetCycleService
{
    public static function getAll(): array
    {
        return BudgetCycleRepository::getAll(); 
    }

    public static function create(array $data): array
    {   
        self::validate($data); 

        return BudgetCycleRepository::create([
            'name'       => strtolower($data['name']),
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'budget'     => $data['budget']
        ]);
    }

    public static function update(int $id, array $data): array
    {
        if(!$id) {
            throw new \InvalidArgumentException('ID is required'); 
        } 

        self::validate($data); 

        return BudgetCycleRepository::update($id, [
            'name'      => strtolower($data['name']),
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'budget'    => $data['budget'],
        ]);
    }

    public static function delete(int $id): void
    {
        BudgetCycleRepository::delete($id); 
    }

    private static function validate(array $data): void
    {
        $requiredFields = ['name', 'start_date', 'end_date', 'budget'];

        foreach($requiredFields as $field) {
            if(!array_key_exists($field, $data) || $data[$field] == '' || $data[$field] == null) {
                throw new \InvalidArgumentException("{$field} is required"); 

            }
        }
    }
}