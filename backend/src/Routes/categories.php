<?php 

use App\Controllers\CategoriesController; 

return function ($app) {
    $app->get('/categories', [CategoriesController::class, 'index']);
    $app->post('/categories', [CategoriesController::class, 'store']);
    $app->put('/categories/{id}', [CategoriesController::class, 'update']);
    $app->delete('/categories/{id}', [CategoriesController::class, 'delete']);
};