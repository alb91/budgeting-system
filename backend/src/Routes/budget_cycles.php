<?php 

use App\Controllers\BudgetCycleController;

return function ($app) {
    $app->post('/budget-cycles', [BudgetCycleController::class, 'store']);
    $app->get('/budget-cycles', [BudgetCycleController::class, 'index']);
    $app->put('/budget-cycles/{id}', [BudgetCycleController::class, 'update']); 
    $app->delete('/budget-cycles/{id}', [BudgetCycleController::class, 'delete']);  
}; 

