<?php

namespace App\Notification\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private PHPMailer $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Настройка SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'];
        
        // Настройка отправителя
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $this->mailer->CharSet = 'UTF-8';
    }
    
    public function sendNotification(string $to, string $subject, string $body): bool {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML(true);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            // Логирование ошибки
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
} 