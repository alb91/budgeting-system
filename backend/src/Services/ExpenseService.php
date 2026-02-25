<?php 

namespace App\Services; 
use App\Repositories\BudgetCycleRepository;
use App\Repositories\ExpenseRepository; 

class ExpenseService
{
    public static function getAllByCycle(int $cycleId): array
    {
        $cycle = BudgetCycleRepository::getById($cycleId);
        
        if (!$cycle) {
            throw new \RuntimeException('Cycle nof found'); 
        }

        $expenses = ExpenseRepository::getAllByCycle($cycleId);
        
        return [
            'cycle' => $cycle,
            'expenses' => $expenses
        ];
    }

    public static function create(array $data): array
    {
        self::validate($data); 

        return ExpenseRepository::create([
            'cycle_id' => $data['cycle_id'],
            'name'     => strtolower($data['name']),
            'date'     => $data['date'],
            'amount'   => $data['amount']
        ]);
    }

    public static function update (int $id, array $data): array
    {
        if(!$id) {
            throw new \InvalidArgumentException('ID is required'); 
        }

        self::validate($data); 

        return ExpenseRepository::update($id, [
            'name' => strtolower($data['name']),
            'date' => $data['date'],
            'amount' => $data['amount']
        ]);
    }

    public static function delete(int $id): void
    {
        ExpenseRepository::delete($id); 
    }

    private static function validate(array $data): void
    {
        $requiredFields = ['name', 'date', 'amount'];

        foreach($requiredFields as $field) {
            if(!array_key_exists($field, $data) || $data[$field] == '' || $data[$field] == null) {
                throw new \InvalidArgumentException("{$field} is required"); 

            }
        }
    }
}