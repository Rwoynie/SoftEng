<?php
// EmailSender.php - SIMPLIFIED AND WORKING VERSION
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../storage/php_error.log');

// Load email config
$emailConfigPath = __DIR__ . '/../../Database/email_config.php';
if (file_exists($emailConfigPath)) {
    require_once $emailConfigPath;
    error_log("Email config loaded from: " . $emailConfigPath);
} else {
    die("Email config not found at: " . $emailConfigPath);
}

// SIMPLE PHPMailer loading - direct path
$phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
if (!file_exists($phpmailerPath)) {
    die("PHPMailer not found at: " . $phpmailerPath);
}

require_once $phpmailerPath;
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailSender {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    public $mailerAvailable = true; // We know it's available
    
    public function __construct() {
        $this->host = EmailConfig::SMTP_HOST;
        $this->port = EmailConfig::SMTP_PORT;
        $this->username = EmailConfig::SMTP_USERNAME;
        $this->password = EmailConfig::SMTP_PASSWORD;
        $this->fromEmail = EmailConfig::SMTP_FROM_EMAIL;
        $this->fromName = EmailConfig::SMTP_FROM_NAME;
        
        error_log("EmailSender initialized with: " . $this->host . ":" . $this->port);
    }
    
    public function sendWelcomeEmail($toEmail, $toName, $password, $role) {
        try {
            error_log("Sending welcome email to: " . $toEmail);
            
            $subject = EmailConfig::WELCOME_SUBJECT;
            $body = EmailConfig::getWelcomeBody($toName, $toEmail, $password, $role);
            
            return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
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
            
            
            
            // SSL options for development
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Recipients
            $mail->setFrom($this->username, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $this->generatePlainText($body);
            
            error_log("Attempting to send to: " . $toEmail);
            $result = $mail->send();
            
            if ($result) {
                error_log("✅ Email sent successfully to: " . $toEmail);
            } else {
                error_log("❌ Email failed to send to: " . $toEmail);
            }
            
            return $result;
            
        } catch (PHPMailerException $e) {
            error_log("PHPMailer Exception: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    private function generatePlainText($html) {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
?>