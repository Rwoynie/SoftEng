<?php
// app/Utils/EmailSender.php
require_once __DIR__ . '/../../Database/email_config.php';

// Prefer Composer autoload; fallback to direct includes if unavailable
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
} else {
	require_once __DIR__ . '/../../vendor/PHPMailer/src/PHPMailer.php';
	require_once __DIR__ . '/../../vendor/PHPMailer/src/SMTP.php';
	require_once __DIR__ . '/../../vendor/PHPMailer/src/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->host = EmailConfig::SMTP_HOST;
        $this->port = EmailConfig::SMTP_PORT;
        $this->username = EmailConfig::SMTP_USERNAME;
        $this->password = EmailConfig::SMTP_PASSWORD;
        $this->fromEmail = EmailConfig::SMTP_FROM_EMAIL;
        $this->fromName = EmailConfig::SMTP_FROM_NAME;
    }
    
    /**
     * Send welcome email with auto-generated password using PHPMailer
     */
    public function sendWelcomeEmail($toEmail, $toName, $password, $role) {
        try {
            $subject = EmailConfig::WELCOME_SUBJECT;
            $body = EmailConfig::getWelcomeBody($toName, $toEmail, $password, $role);
            
            return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHPMailer with SMTP
     */
    private function sendEmailPHPMailer($toEmail, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;
            
            // Debug (enable only for testing)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $this->generatePlainText($body);
            
            // Send email
            $mail->send();
            error_log("PHPMailer: Email sent successfully to: " . $toEmail);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: Could not send email to {$toEmail}. Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Generate plain text version of HTML email
     */
    private function generatePlainText($html) {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        return $text;
    }
}
?>