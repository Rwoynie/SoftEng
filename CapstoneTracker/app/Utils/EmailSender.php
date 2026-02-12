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
        
        // Test connection but don't block if it fails
        try {
            $this->mailerAvailable = $this->testConnection();
            if ($this->mailerAvailable) {
                error_log("SMTP connection test SUCCESS for {$this->host}:{$this->port}");
            } else {
                error_log("SMTP connection test FAILED for {$this->host}:{$this->port}");
            }
        } catch (Exception $e) {
            error_log("SMTP connection test exception: " . $e->getMessage());
            $this->mailerAvailable = false;
        }
    }

    public function testConnection() {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->Port = (int)$this->port;
            $mail->SMTPSecure = ($mail->Port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPDebug = 0;
            $mail->Timeout = 10;
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            return $mail->smtpConnect();
        } catch (Exception $e) {
            error_log("SMTP connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendWelcomeEmail($toEmail, $toName, $password, $role, $userIdentifier) {
        try {
            error_log("🚀 Sending welcome email:");
            error_log("   To: " . $toEmail);
            error_log("   Name: " . $toName);
            error_log("   Role: " . $role);
            error_log("   User ID: " . $userIdentifier);
            error_log("   Registration Type: " . (empty($password) ? "MANUAL" : "GOOGLE"));
            
            if (empty($toEmail) || empty($toName) || empty($role) || empty($userIdentifier)) {
                error_log("❌ Missing parameters for welcome email");
                return false;
            }
            
            // Use separate subjects and templates for manual vs Google registration
            if (empty($password)) {
                $subject = EmailConfig::MANUAL_REGISTRATION_SUBJECT;
                $body = EmailConfig::getManualWelcomeBody($toName, $toEmail, $role, $userIdentifier);
            } else {
                $subject = EmailConfig::WELCOME_SUBJECT;
                $body = EmailConfig::getGoogleWelcomeBody($toName, $toEmail, $password, $role, $userIdentifier);
            }
            
            error_log("📝 Email subject: " . $subject);
            error_log("📝 Email type: " . (empty($password) ? "MANUAL" : "GOOGLE"));
            
            return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("❌ Email sending error in sendWelcomeEmail: " . $e->getMessage());
            return false;
        }
    }

	public function sendHtmlEmail($toEmail, $toName, $subject, $body) {
		try {
			error_log("Sending custom HTML email to: " . $toEmail . " with subject: " . $subject);
			return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
		} catch (\Exception $e) {
			error_log("Custom email sending error: " . $e->getMessage());
			return false;
		}
	}
    
    private function sendEmailPHPMailer($toEmail, $toName, $subject, $body) {
        if (!$this->mailerAvailable) {
            error_log("PHPMailer not available in sendEmailPHPMailer().");
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        try {
            error_log("Configuring PHPMailer for: " . $this->host . ":" . $this->port);
            
            // Server settings
            $mail->isSMTP();
            $mail->Timeout    = 30;
            $mail->SMTPKeepAlive = false; // Important: Don't keep connection alive
            $mail->CharSet    = 'UTF-8';
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            
            // Choose encryption based on port
            $mail->Port = (int)$this->port;
            if ($mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            error_log("Using encryption: " . $mail->SMTPSecure . " on port: " . $mail->Port);
    
            // SSL options
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Recipients
            $mail->setFrom($this->username, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $this->generatePlainText($body);
            
            error_log("Attempting to send email to: " . $toEmail);
            
            // Send email
            $result = $mail->send();
            
            if ($result) {
                error_log("PHPMailer: Email sent successfully to: " . $toEmail);
            } else {
                error_log("PHPMailer: Send method returned false for: " . $toEmail);
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
            }
            
            // Explicitly close the connection
            $mail->smtpClose();
            
            return $result;
            
        } catch (PHPMailerException $e) {
            error_log("PHPMailer Exception: Could not send email to {$toEmail}. Error: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            
            // Ensure connection is closed even on error
            try {
                $mail->smtpClose();
            } catch (Exception $e) {
                // Ignore close errors
            }
            
            return false;
        } catch (Exception $e) {
            error_log("General Exception in sendEmailPHPMailer: " . $e->getMessage());
            
            // Ensure connection is closed even on error
            try {
                $mail->smtpClose();
            } catch (Exception $e) {
                // Ignore close errors
            }
            
            return false;
        }
    }
    
    private function generatePlainText($html) {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /*Thesis email sender function*/ 

    public function sendThesisNotification($toEmail, $toName, $thesisTitle, $department, $course, $role = 'author') {
        try {
            error_log("Sending thesis notification email to: " . $toEmail . " as " . $role);
            
            if ($role === 'author') {
                $subject = "Thesis Upload Confirmation: " . $thesisTitle;
                $body = $this->getAuthorNotificationBody($toName, $thesisTitle, $department, $course);
            } else {
                $subject = "New Thesis Assignment: " . $thesisTitle;
                $body = $this->getAdviserNotificationBody($toName, $thesisTitle, $department, $course);
            }
            
            return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
            
        } catch (\Exception $e) {
            error_log("Thesis notification email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getAuthorNotificationBody($name, $title, $department, $course) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .thesis-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Thesis Upload Confirmation</h1>
                </div>
                <div class='content'>
                    <p>Dear " . ($name ?: 'Author') . ",</p>
                    <p>Your thesis has been successfully uploaded to the Capstone Tracker System.</p>
                    
                    <div class='thesis-info'>
                        <h3>Thesis Details</h3>
                        <p><strong>Title:</strong> {$title}</p>
                        <p><strong>Department:</strong> {$department}</p>
                        <p><strong>Course:</strong> {$course}</p>
                        <p><strong>Date Uploaded:</strong> " . date('F j, Y') . "</p>
                    </div>
                    
                    <p>You can access your thesis through the system dashboard at any time.</p>
                    <p>Best regards,<br>Capstone Tracker System</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getAdviserNotificationBody($name, $title, $department, $course) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .thesis-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>New Thesis Assignment</h1>
                </div>
                <div class='content'>
                    <p>Dear " . ($name ?: 'Adviser') . ",</p>
                    <p>A new thesis has been assigned to you for review and guidance.</p>
                    
                    <div class='thesis-info'>
                        <h3>Thesis Details</h3>
                        <p><strong>Title:</strong> {$title}</p>
                        <p><strong>Department:</strong> {$department}</p>
                        <p><strong>Course:</strong> {$course}</p>
                        <p><strong>Date Submitted:</strong> " . date('F j, Y') . "</p>
                    </div>
                    
                    <p>Please log in to the system to review this thesis.</p>
                    <p>Best regards,<br>Capstone Tracker System</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>