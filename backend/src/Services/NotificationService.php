<?php 

namespace App\Services; 
use App\Repositories\ExpenseRepository;  

class NotificationService
{
    public static function processNotifications(): void
    {
        $expenses = ExpenseRepository::getPendingNotifications(); 

        foreach ($expenses as $expense) {
            
            $success = MailService::sendExpenseReminder($expense); 

            if($success){
                ExpenseRepository::markNotificationSent($expense['id']);
            }

        }
    }

}
