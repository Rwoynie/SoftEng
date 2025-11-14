<?php
// EmailSender.php - FIXED VERSION
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../storage/php_error.log');

// Load email config with better error handling
$emailConfigPath = __DIR__ . '/../../Database/email_config.php';
if (file_exists($emailConfigPath)) {
    require_once $emailConfigPath;
    error_log("Email config loaded from: " . $emailConfigPath);
} else {
    // Try alternative path
    $emailConfigPath = __DIR__ . '/../Database/email_config.php';
    if (file_exists($emailConfigPath)) {
        require_once $emailConfigPath;
        error_log("Email config loaded from: " . $emailConfigPath);
    } else {
        error_log("Email config not found. Checked: " . $emailConfigPath);
        die("Email config not found");
    }
}

// Use Composer autoloader for PHPMailer
$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    error_log("Composer autoloader loaded");
} else {
    // Fallback to direct inclusion
    $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    if (!file_exists($phpmailerPath)) {
        error_log("PHPMailer not found at: " . $phpmailerPath);
        die("PHPMailer not found");
    }
    require_once $phpmailerPath;
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
}

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
    public $mailerAvailable = true;
    
    public function __construct() {
        $this->host = EmailConfig::SMTP_HOST;
        $this->port = EmailConfig::SMTP_PORT;
        $this->username = EmailConfig::SMTP_USERNAME;
        $this->password = EmailConfig::SMTP_PASSWORD;
        $this->fromEmail = EmailConfig::SMTP_FROM_EMAIL;
        $this->fromName = EmailConfig::SMTP_FROM_NAME;
        
        error_log("EmailSender initialized with: " . $this->host . ":" . $this->port);
        
        // Test SMTP connection
        $this->testSmtpConnection();
    }

    private function testSmtpConnection() {
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
            
            if (!$mail->smtpConnect()) {
                error_log("SMTP connection test FAILED for {$this->host}:{$this->port}");
                $this->mailerAvailable = false;
            } else {
                error_log("SMTP connection test SUCCESS for {$this->host}:{$this->port}");
                $mail->smtpClose();
            }
        } catch (Exception $e) {
            error_log("SMTP connection test exception: " . $e->getMessage());
            $this->mailerAvailable = false;
        }
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

    public function sendHtmlEmail($toEmail, $toName, $subject, $body) {
        try {
            error_log("Sending custom HTML email to: " . $toEmail . " with subject: " . $subject);
            return $this->sendEmailPHPMailer($toEmail, $toName, $subject, $body);
        } catch (\Exception $e) {
            error_log("Custom email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    // FIXED: This method should be public for ThesisController to call
    public function sendThesisUploadNotification($authorEmails, $adviserEmail, $thesisTitle, $thesisId) {
        try {
            error_log("Sending thesis upload notification to authors: " . implode(', ', $authorEmails) . " and adviser: " . $adviserEmail);
            
            $subject = "Thesis Uploaded Successfully - Compendium System";
            $successCount = 0;
            
            // Send to each author
            foreach ($authorEmails as $authorEmail) {
                $authorBody = $this->getThesisUploadAuthorBody($thesisTitle, $thesisId);
                if ($this->sendEmailPHPMailer($authorEmail, 'Thesis Author', $subject, $authorBody)) {
                    $successCount++;
                    error_log("Thesis notification sent to author: " . $authorEmail);
                } else {
                    error_log("Failed to send thesis notification to author: " . $authorEmail);
                }
            }
            
            // Send to adviser
            if ($adviserEmail && filter_var($adviserEmail, FILTER_VALIDATE_EMAIL)) {
                $adviserBody = $this->getThesisUploadAdviserBody($thesisTitle, $thesisId, $authorEmails);
                if ($this->sendEmailPHPMailer($adviserEmail, 'Thesis Adviser', $subject, $adviserBody)) {
                    $successCount++;
                    error_log("Thesis notification sent to adviser: " . $adviserEmail);
                } else {
                    error_log("Failed to send thesis notification to adviser: " . $adviserEmail);
                }
            }
            
            error_log("Thesis notifications sent successfully. Total: " . $successCount);
            return $successCount > 0;
            
        } catch (\Exception $e) {
            error_log("Thesis notification email error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendEmailPHPMailer($toEmail, $toName, $subject, $body) {
        if (!$this->mailerAvailable) {
            error_log("PHPMailer not available for: " . $toEmail);
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        try {
            error_log("Configuring PHPMailer for: " . $toEmail);
            
            // Server settings
            $mail->isSMTP();
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = false;
            $mail->CharSet = 'UTF-8';
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            
            // Choose encryption based on port
            $mail->Port = (int)$this->port;
            if ($mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // SSL options
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $this->generatePlainText($body);
            
            // Send email
            $result = $mail->send();
            
            if ($result) {
                error_log("Email sent successfully to: " . $toEmail);
            } else {
                error_log("Send method returned false for: " . $toEmail);
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
            }
            
            // Close connection
            $mail->smtpClose();
            
            return $result;
            
        } catch (PHPMailerException $e) {
            error_log("PHPMailer Exception for {$toEmail}: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            
            try {
                $mail->smtpClose();
            } catch (Exception $e) {
                // Ignore close errors
            }
            
            return false;
        } catch (Exception $e) {
            error_log("General Exception for {$toEmail}: " . $e->getMessage());
            
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
    
    private function getThesisUploadAuthorBody($thesisTitle, $thesisId) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                .thesis-info { background: #e8f4fd; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
                .btn { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Compendium System</h1>
                    <p>University of Southeastern Philippines</p>
                </div>
                
                <div class='content'>
                    <h2>Thesis Upload Successful!</h2>
                    <p>Your thesis has been successfully uploaded to the Compendium System.</p>
                    
                    <div class='thesis-info'>
                        <h3>Thesis Details:</h3>
                        <p><strong>Title:</strong> {$thesisTitle}</p>
                        <p><strong>Thesis ID:</strong> {$thesisId}</p>
                        <p><strong>Upload Date:</strong> " . date('F j, Y') . "</p>
                    </div>
                    
                    <p>Your thesis is now available in the system and can be accessed by authorized users.</p>
                    
                    <p><strong>Access the system:</strong> 
                        <a href='http://localhost:3000' class='btn'>View Compendium</a>
                    </p>
                    
                    <p>If you have any questions or need to make changes, please contact the system administrator.</p>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " University of Southeastern Philippines | Compendium System</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getThesisUploadAdviserBody($thesisTitle, $thesisId, $authorEmails) {
        $authorsList = implode(', ', $authorEmails);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                .thesis-info { background: #e8f4fd; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
                .btn { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Compendium System</h1>
                    <p>University of Southeastern Philippines</p>
                </div>
                
                <div class='content'>
                    <h2>New Thesis Upload - Adviser Notification</h2>
                    <p>A new thesis where you are listed as the adviser has been uploaded to the Compendium System.</p>
                    
                    <div class='thesis-info'>
                        <h3>Thesis Details:</h3>
                        <p><strong>Title:</strong> {$thesisTitle}</p>
                        <p><strong>Thesis ID:</strong> {$thesisId}</p>
                        <p><strong>Authors:</strong> {$authorsList}</p>
                        <p><strong>Upload Date:</strong> " . date('F j, Y') . "</p>
                    </div>
                    
                    <p>The thesis is now available in the system for review and access by authorized users.</p>
                    
                    <p><strong>Access the system:</strong> 
                        <a href='http://localhost:3000' class='btn'>View Compendium</a>
                    </p>
                    
                    <p>If you have any questions about this thesis, please contact the authors or system administrator.</p>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " University of Southeastern Philippines | Compendium System</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>