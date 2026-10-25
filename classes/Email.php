<?php
// classes/Email.php

// Load PHPMailer files manually
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // SMTP Configuration for Gmail
            $this->mailer->isSMTP();
            $this->mailer->Host       = 'smtp.gmail.com';
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = getenv('MAIL_USERNAME');
            $this->mailer->Password   = getenv('MAIL_PASSWORD');     
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = 587;
            
            // Default sender
            $this->mailer->setFrom('no-reply@presbyterianbrigade.org', 'Presbyterian Brigade');
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            error_log('Email configuration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send email to single recipient
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send email to multiple recipients
     */
    public function sendBulk($recipients, $subject, $body, $altBody = '') {
        $sent = 0;
        foreach ($recipients as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($this->send($email, $subject, $body, $altBody)) {
                    $sent++;
                }
            }
        }
        return $sent;
    }
    
    /**
     * Send event notification
     */
    public function sendEventNotification($event, $recipients) {
        $subject = '📅 Event Reminder: ' . $event['event_name'];
        
        $eventDate = date('l, F j, Y', strtotime($event['event_date']));
        $eventTime = $event['event_time'] 
            ? date('g:i A', strtotime($event['event_time'])) 
            : '';
        
        $body = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                }
                .header { 
                    background: #0d2a52; 
                    color: white; 
                    padding: 20px; 
                    text-align: center; 
                }
                .content { 
                    padding: 30px 20px; 
                    background: #ffffff;
                }
                .event-details { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border-left: 4px solid #0d2a52;
                }
                .event-details h3 {
                    margin-top: 0;
                    color: #0d2a52;
                }
                .detail-row {
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                .detail-row:last-child {
                    border-bottom: none;
                }
                .footer { 
                    text-align: center; 
                    padding: 20px; 
                    background: #f8f9fa;
                    color: #666; 
                    font-size: 12px; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Presbyterian Brigade</h2>
                    <p style='margin:0;'>Event Notification</p>
                </div>
                
                <div class='content'>
                    <h3>📅 You have an upcoming event!</h3>
                    
                    <div class='event-details'>
                        <h3>{$event['event_name']}</h3>
                        
                        <div class='detail-row'>
                            <strong>📅 Date:</strong> {$eventDate}
                        </div>
                        
                        " . ($eventTime ? "<div class='detail-row'><strong>🕐 Time:</strong> {$eventTime}</div>" : "") . "
                        
                        <div class='detail-row'>
                            <strong>📍 Location:</strong> {$event['location']}
                        </div>
                        
                        <div class='detail-row'>
                            <strong>📋 Type:</strong> " . ucfirst($event['event_type']) . "
                        </div>
                        
                        " . ($event['description'] 
                            ? "<div class='detail-row'><strong>Details:</strong><br>{$event['description']}</div>" 
                            : "") . "
                    </div>
                    
                    <p>We look forward to seeing you there!</p>
                    <p style='color:#666; font-size:14px;'>Please mark your calendar and arrive on time.</p>
                </div>
                
                <div class='footer'>
                    <p><strong>Presbyterian Brigade Management System</strong></p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendBulk($recipients, $subject, $body);
    }
    
    /**
     * Send promotion notification
     */
    public function sendPromotionNotification($brigaderName, $fromRank, $toRank, $email) {
        $subject = '🎖️ Congratulations on Your Promotion!';
        
        $body = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                }
                .container { max-width: 600px; margin: 0 auto; }
                .header { 
                    background: #0d2a52; 
                    color: white; 
                    padding: 20px; 
                    text-align: center; 
                }
                .content { padding: 30px 20px; background: #ffffff; }
                .promotion-box { 
                    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); 
                    padding: 30px; 
                    border-radius: 10px; 
                    text-align: center; 
                    margin: 20px 0;
                    border: 2px solid #28a745;
                }
                .promotion-box h2 {
                    color: #155724;
                    margin: 0 0 15px 0;
                }
                .rank-transition {
                    font-size: 20px;
                    color: #155724;
                    font-weight: bold;
                }
                .footer { 
                    text-align: center; 
                    padding: 20px; 
                    background: #f8f9fa;
                    color: #666; 
                    font-size: 12px; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Presbyterian Brigade</h2>
                    <p style='margin:0;'>🎖️ Promotion Notification</p>
                </div>
                
                <div class='content'>
                    <h2>Congratulations, {$brigaderName}!</h2>
                    
                    <div class='promotion-box'>
                        <h2>🎉 You've Been Promoted! 🎉</h2>
                        <p class='rank-transition'>
                            {$fromRank} → {$toRank}
                        </p>
                    </div>
                    
                    <p>Your hard work, dedication, and commitment to the Presbyterian Brigade have been recognized!</p>
                    
                    <p>This promotion is a testament to your growth and the positive impact you've made. We're proud of your achievements and excited to see you continue to excel in your new role.</p>
                    
                    <p><strong>Keep up the excellent work!</strong></p>
                </div>
                
                <div class='footer'>
                    <p><strong>Presbyterian Brigade Management System</strong></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Send custom notification
     */
    public function sendCustomNotification($title, $message, $recipients) {
        $subject = $title;
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #0d2a52; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #ffffff; }
                .message-box { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    border-radius: 8px; 
                    margin: 20px 0;
                    border-left: 4px solid #0d2a52;
                }
                .footer { text-align: center; padding: 20px; background: #f8f9fa; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Presbyterian Brigade</h2>
                </div>
                
                <div class='content'>
                    <h3>{$title}</h3>
                    <div class='message-box'>
                        <p style='margin:0; white-space: pre-line;'>{$message}</p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Presbyterian Brigade Management System</strong></p>
                    <p>This is an automated message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendBulk($recipients, $subject, $body);
    }
}