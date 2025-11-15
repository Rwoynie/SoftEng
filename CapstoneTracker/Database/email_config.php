<?php
// config/email_config.php
class EmailConfig {
    // Gmail SMTP Configuration (Recommended)
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'rltiempo25@gmail.com'; // Your Gmail or USeP email
    const SMTP_PASSWORD = 'xttm zepv gfex dndo'; // Gmail app password
    
    
    
    const SMTP_FROM_EMAIL = 'rltiempo25@gmail.com';
    const SMTP_FROM_NAME = 'Compendium System';
    
    // Email Templates
    const WELCOME_SUBJECT = 'Welcome to Compendium System - Your Account Details';
    
    public static function getWelcomeBody($name, $email, $password, $role, $userIdentifier) {
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
                .password-box { background: #e74c3c; color: white; padding: 15px; border-radius: 5px; font-weight: bold; text-align: center; margin: 20px 0; font-size: 18px; }
                .info-box { background: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Compendium System</h1>
                    <p>University of Southeastern Philippines</p>
                </div>
                
                <div class='content'>
                    <h2>Welcome, {$name}!</h2>
                    <p>Your account has been successfully created in the Compendium System using Google Sign-In.</p>
                    
                    <div class='info-box'>
                        <p><strong>Account Details:</strong></p>
                        <ul>
                            <li><strong>Email:</strong> {$email}</li>
                            <li><strong>Role:</strong> " . ucfirst($role) . "</li>
                            <li><strong>Login Method:</strong> Google Sign-In</li>
                        </ul>
                    </div>
                    
                    <div class='password-box'>
                        <p><strong>Your Auto-Generated Password:</strong></p>
                        <p style='font-size: 24px; letter-spacing: 2px;'>{$password}</p>
                    </div>

                    <div class='password-box'>
                        <p><strong>Your User ID:</strong></p>
                        <p style='font-size: 24px; letter-spacing: 2px;'>{$userIdentifier}</p>
                    </div>

                    <div class='info-box'>
                        <p><strong>Important Information:</strong></p>
                        <ul>
                            <li>You can use this password to log in directly if Google Sign-In is unavailable</li>
                            <li>We recommend changing your password after first login</li>
                            <li>Keep this password secure and do not share it with anyone</li>
                            <li>This password was automatically generated for your account</li>
                        </ul>
                    </div>
                    
                    <p><strong>Access the system:</strong> <a href='http://localhost:3000'>Compendium System Portal</a></p>
                    
                    <p>If you have any questions, please contact the system administrator.</p>
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