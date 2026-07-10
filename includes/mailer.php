<?php
/**
 * Portfolio OS — Mailer Helper
 * Sends emails using native PHP mail() with proper headers.
 * Can be swapped for PHPMailer in production.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function sendEmail(string $to, string $subject, string $htmlBody, string $replyTo = ''): bool
{
    $fromName  = MAIL_FROM_NAME;
    $fromEmail = MAIL_FROM ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
    
    if ($replyTo) {
        $headers .= "Reply-To: {$replyTo}\r\n";
    }
    
    $headers .= "X-Mailer: PHP/" . phpversion();

    // In a real environment, you'd use PHPMailer or Symfony Mailer here.
    // For this boilerplate, we use mail()
    return @mail($to, $subject, $htmlBody, $headers);
}
