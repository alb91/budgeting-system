<?php 

return function ($app) {
    (require __DIR__ . '/budget_cycles.php')($app); 
    (require __DIR__ . '/expenses.php')($app); 
    (require __DIR__ . '/categories.php')($app);
}; 

