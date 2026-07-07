<?php 

namespace App\Services; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function sendExpenseReminder(array $expense): bool
    {
        $mail = new PHPMailer(true); 
        
        try {
            $mail->isSMTP();

            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) $_ENV['MAIL_PORT']; 
            $mail->setFROM($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($_ENV['NOTIFY_EMAIL']); 
            $mail->isHTML(true); 
            $mail->Subject = "Reminder"; 
            $mail->Body = self::renderTemplate('expense-reminder', ['expense' => $expense]);                  
            $mail->AltBody = 'Error obtaining email template data';
            $mail->send(); 
            return true; 
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;

            return false; 
        }
    }

    private static function renderTemplate(string $template, array $data): string
    {
        extract($data); 

        ob_start(); 

        require __DIR__ . "/../Views/Emails/expense-reminder.php"; 

        return ob_get_clean(); 
    }
}