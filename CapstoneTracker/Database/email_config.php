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
    const MANUAL_REGISTRATION_SUBJECT = 'Welcome to Compendium System - Account Registration Complete';
    
    /**
     * Welcome email for MANUAL registration (email/password)
     */
    public static function getManualWelcomeBody($name, $email, $role, $userIdentifier) {
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
                .userid-box { background: #3498db; color: white; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; }
                .info-box { background: #ecf0f1; padding: 20px; border-left: 4px solid #3498db; margin: 20px 0; }
                .approval-notice { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px 0; }
                .login-instructions { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0; }
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
                    <p>Your account has been successfully registered in the Compendium System.</p>
                    
                    <div class='userid-box'>
                        <h3>Your User ID</h3>
                        <p style='font-size: 28px; letter-spacing: 2px; font-weight: bold; margin: 15px 0;'>{$userIdentifier}</p>
                        <p><em>Please save this User ID for your records</em></p>
                    </div>
                    
                    <div class='login-instructions'>
                        <h3>How to Log In</h3>
                        <p><strong>Use your email address and the password you created during registration.</strong></p>
                        <ul>
                            <li><strong>Email:</strong> {$email}</li>
                            <li><strong>Password:</strong> The password you chose during registration</li>
                        </ul>
                    </div>
                    
                    <div class='info-box'>
                        <p><strong>Account Details:</strong></p>
                        <ul>
                            <li><strong>Role:</strong> " . ucfirst($role) . "</li>
                            <li><strong>Login Method:</strong> Manual Register</li>
                            <li><strong>Registration Date:</strong> " . date('F j, Y') . "</li>
                        </ul>
                    </div>
                    
                    <div class='approval-notice'>
                        <p><strong>Account Approval Status:</strong> Pending</p>
                        <p>Your account is currently <span style='color: #e74c3c; font-weight: bold;'>pending administrator approval</span>. 
                        You will receive another email notification once your account has been approved and activated.</p>
                    </div>
                    
                    <p><strong>Access the system:</strong> <a href='http://localhost:3000'>Compendium System Portal</a></p>
                    
                    <p>If you have any questions or need assistance, please contact the system administrator.</p>
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

    /**
     * Welcome email for GOOGLE registration
     */
    public static function getGoogleWelcomeBody($name, $email, $password, $role, $userIdentifier) {
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
                .password-box { background: #e74c3c; color: white; padding: 20px; border-radius: 5px; font-weight: bold; text-align: center; margin: 20px 0; }
                .userid-box { background: #3498db; color: white; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; }
                .info-box { background: #ecf0f1; padding: 20px; border-left: 4px solid #3498db; margin: 20px 0; }
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
                    <p>Your account has been successfully created using Google Sign-In.</p>
                    
                    <div class='userid-box'>
                        <h3>Your User ID</h3>
                        <p style='font-size: 28px; letter-spacing: 2px; font-weight: bold; margin: 15px 0;'>{$userIdentifier}</p>
                    </div>
                    
                    <div class='password-box'>
                        <h3>Your Auto-Generated Password</h3>
                        <p style='font-size: 24px; letter-spacing: 2px; margin: 15px 0;'>{$password}</p>
                        <p><em>Use this password if Google Sign-In is unavailable</em></p>
                    </div>
                    
                    <div class='info-box'>
                        <p><strong>Account Details:</strong></p>
                        <ul>
                            <li><strong>Email:</strong> {$email}</li>
                            <li><strong>Role:</strong> " . ucfirst($role) . "</li>
                            <li><strong>Login Method:</strong> Google Sign-In</li>
                            <li><strong>Account Status:</strong> Approved</li>
                        </ul>
                        
                        <p><strong>Important Information:</strong></p>
                        <ul>
                            <li>You can use the password above to log in directly if Google Sign-In is unavailable</li>
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

    /**
     * Backward compatibility method (uses manual template by default)
     */
    public static function getWelcomeBody($name, $email, $password, $role, $userIdentifier) {
        if (empty($password)) {
            return self::getManualWelcomeBody($name, $email, $role, $userIdentifier);
        } else {
            return self::getGoogleWelcomeBody($name, $email, $password, $role, $userIdentifier);
        }
    }
}
?>