<?php 

use App\Controllers\ExpenseController;

return function ($app) {
    $app->get('/cycles/{id}/expenses', [ExpenseController::class, 'getAllByCycle']);
    $app->post('/expenses', [ExpenseController::class, 'store']); 
    $app->put('/expenses/{id}', [ExpenseController::class, 'update']); 
    $app->delete('/expenses/{id}', [ExpenseController::class, 'delete']); 
};