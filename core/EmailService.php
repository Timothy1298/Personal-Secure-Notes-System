<?php
namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private static $config = [
        'host' => 'smtp.google.com',
        'port' => 587,
        'username' => 'tkuria30@gmail.com',
        'password' => 'nolzyhlourgzader',
        'encryption' => 'tls',
        'from_address' => 'tkuria30@gmail.com',
        'from_name' => 'SecureNote Pro'
    ];
    
    public static function init() {
        // Load email configuration from environment
        self::$config['username'] = $_ENV['SMTP_USER'] ?? 'tkuria30@gmail.com';
        self::$config['password'] = $_ENV['SMTP_PASS'] ?? 'nolzyhlourgzader';
        self::$config['host'] = $_ENV['SMTP_HOST'] ?? 'smtp.google.com';
        self::$config['port'] = $_ENV['SMTP_PORT'] ?? 587;
        self::$config['encryption'] = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
        self::$config['from_address'] = $_ENV['EMAIL_FROM'] ?? 'tkuria30@gmail.com';
        self::$config['from_name'] = $_ENV['EMAIL_FROM_NAME'] ?? 'SecureNote Pro';
    }
    
    // Send email using PHPMailer with SMTP
    public static function sendMail($to, $subject, $message, $isHTML = true, $attachments = []) {
        self::init();
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = self::$config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$config['username'];
            $mail->Password = self::$config['password'];
            $mail->SMTPSecure = self::$config['encryption'];
            $mail->Port = self::$config['port'];
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom(self::$config['from_address'], self::$config['from_name']);
            $mail->addAddress($to);
            
            // Attachments
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && isset($attachment['name'])) {
                    $mail->addAttachment($attachment['path'], $attachment['name']);
                }
            }
            
            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            if (!$isHTML) {
                $mail->AltBody = strip_tags($message);
            }
            
            $result = $mail->send();
            
            // Log successful email
            error_log("Email sent successfully to: $to, Subject: $subject");
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Send welcome email
    public static function sendWelcomeEmail($email, $username) {
        $subject = "Welcome to SecureNote Pro!";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Welcome to SecureNote Pro!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$username}!</h2>
                    <p>Thank you for joining SecureNote Pro - your personal secure notes and task management system.</p>
                    <p>Your account has been successfully created and is ready to use. Here's what you can do:</p>
                    <ul>
                        <li>📝 Create and manage encrypted notes</li>
                        <li>✅ Organize tasks with advanced features</li>
                        <li>🏷️ Use tags and categories for better organization</li>
                        <li>📊 Track your productivity with analytics</li>
                        <li>🔒 Enjoy enterprise-grade security</li>
                    </ul>
                    <p>We recommend setting up two-factor authentication for enhanced security.</p>
                    <a href='" . ($_ENV['APP_URL'] ?? 'http://localhost:3000') . "/settings' class='button'>Go to Settings</a>
                    <p>If you have any questions, feel free to contact our support team.</p>
                    <p>Best regards,<br>The SecureNote Pro Team</p>
                </div>
                <div class='footer'>
                    <p>This email was sent to {$email}. If you didn't create this account, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendMail($email, $subject, $message, true);
    }
    
    // Send password reset email
    public static function sendPasswordResetEmail($email, $username, $resetToken) {
        $resetUrl = ($_ENV['APP_URL'] ?? 'http://localhost:3000') . "/password-reset-form?token={$resetToken}";
        
        $subject = "Password Reset Request - SecureNote Pro";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #ff6b6b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔑 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$username}!</h2>
                    <p>We received a request to reset your password for your SecureNote Pro account.</p>
                    <p>Click the button below to reset your password:</p>
                    <a href='{$resetUrl}' class='button'>Reset Password</a>
                    <div class='warning'>
                        <strong>⚠️ Important:</strong>
                        <ul>
                            <li>This link will expire in 1 hour</li>
                            <li>If you didn't request this reset, please ignore this email</li>
                            <li>Your password will remain unchanged until you click the link above</li>
                        </ul>
                    </div>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;'>{$resetUrl}</p>
                    <p>Best regards,<br>The SecureNote Pro Team</p>
                </div>
                <div class='footer'>
                    <p>This email was sent to {$email}. If you didn't request this reset, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendMail($email, $subject, $message, true);
    }
    
    // Send 2FA code email
    public static function send2FACodeEmail($email, $username, $code) {
        $subject = "Your 2FA Code - SecureNote Pro";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code { background: #4ecdc4; color: white; font-size: 32px; font-weight: bold; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Two-Factor Authentication</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$username}!</h2>
                    <p>You're logging into your SecureNote Pro account. Use the code below to complete your login:</p>
                    <div class='code'>{$code}</div>
                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong>
                        <ul>
                            <li>This code will expire in 10 minutes</li>
                            <li>Never share this code with anyone</li>
                            <li>If you didn't request this code, please secure your account immediately</li>
                        </ul>
                    </div>
                    <p>Best regards,<br>The SecureNote Pro Team</p>
                </div>
                <div class='footer'>
                    <p>This email was sent to {$email} for security purposes.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendMail($email, $subject, $message, true);
    }
    
    // Send email verification
    public static function sendEmailVerification($email, $username, $verificationToken) {
        $verifyUrl = ($_ENV['APP_URL'] ?? 'http://localhost:3000') . "/verify-email?token={$verificationToken}";
        
        $subject = "Verify Your Email - SecureNote Pro";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 Verify Your Email</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$username}!</h2>
                    <p>Thank you for signing up for SecureNote Pro. Please verify your email address to complete your registration.</p>
                    <a href='{$verifyUrl}' class='button'>Verify Email Address</a>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;'>{$verifyUrl}</p>
                    <p>Best regards,<br>The SecureNote Pro Team</p>
                </div>
                <div class='footer'>
                    <p>This email was sent to {$email}. If you didn't create this account, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendMail($email, $subject, $message, true);
    }
    
    // Send task reminder
    public static function sendTaskReminder($email, $username, $taskTitle, $dueDate) {
        $subject = "Task Reminder - {$taskTitle}";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .task-info { background: #fff; border-left: 4px solid #f093fb; padding: 20px; margin: 20px 0; border-radius: 5px; }
                .button { display: inline-block; background: #f093fb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⏰ Task Reminder</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$username}!</h2>
                    <p>This is a reminder about your upcoming task:</p>
                    <div class='task-info'>
                        <h3>{$taskTitle}</h3>
                        <p><strong>Due Date:</strong> {$dueDate}</p>
                    </div>
                    <a href='" . ($_ENV['APP_URL'] ?? 'http://localhost:3000') . "/tasks' class='button'>View Tasks</a>
                    <p>Best regards,<br>The SecureNote Pro Team</p>
                </div>
                <div class='footer'>
                    <p>This reminder was sent to {$email}.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendMail($email, $subject, $message, true);
    }
}
