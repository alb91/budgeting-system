<?php 

use Crunz\Schedule; 

$schedule = new Schedule(); 

$task = $schedule->run('php /var/www/bin/notifications.php');

$task->daily()->at('12:00');  

return $schedule; 

