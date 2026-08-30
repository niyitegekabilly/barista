<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;

class MailService {

    /**
     * Send an email with HTML & Text bodies.
     */
    public static function send(
        string|array $to,
        string $subject,
        string $htmlBody,
        ?string $plainTextBody = null,
        array $attachments = []
    ): array {
        $toEmail = is_array($to) ? ($to['email'] ?? $to[0] ?? '') : $to;
        $toName = is_array($to) ? ($to['name'] ?? '') : '';

        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => "Invalid recipient email: {$toEmail}"];
        }

        $mailer = config('mail.default', 'smtp');

        if ($mailer === 'log') {
            error_log("[BBA Mailer] To: {$toEmail} | Subject: {$subject}\n{$htmlBody}");
            return ['success' => true, 'message' => 'Email logged to server log.'];
        }

        return static::sendSmtp($toEmail, $toName, $subject, $htmlBody, $plainTextBody, $attachments);
    }

    /**
     * Native, robust SMTP Sender with SSL/TLS socket support.
     */
    public static function sendSmtp(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $plainTextBody = null,
        array $attachments = []
    ): array {
        $config = config('mail.mailers.smtp', []);
        $host = $config['host'] ?? env('MAIL_HOST', 'mail.beyondbarista.rw');
        $port = (int)($config['port'] ?? env('MAIL_PORT', 465));
        $encryption = strtolower((string)($config['encryption'] ?? env('MAIL_ENCRYPTION', 'ssl')));
        $username = (string)($config['username'] ?? env('MAIL_USERNAME', 'info@beyondbarista.rw'));
        $password = (string)($config['password'] ?? env('MAIL_PASSWORD', ''));
        $timeout = !empty($config['timeout']) ? (int)$config['timeout'] : 15;
        $timeout = max(5, $timeout);

        $fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'info@beyondbarista.rw'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'Beyond Barista Academy'));

        // Handle SSL vs TLS
        $isSsl = ($port === 465 || $encryption === 'ssl' || $encryption === 'smtps');
        $protocol = $isSsl ? "ssl://" : "tcp://";
        $remoteSocket = "{$protocol}{$host}:{$port}";

        $contextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $streamContext = stream_context_create($contextOptions);

        $socket = @stream_socket_client($remoteSocket, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $streamContext);
        if (!$socket) {
            $err = "SMTP Connection failed to {$remoteSocket}: {$errstr} ({$errno})";
            error_log("[MailService] " . $err);
            return ['success' => false, 'message' => $err];
        }

        stream_set_timeout($socket, $timeout);

        try {
            // 1. Read Greeting Banner
            $banner = static::readResponse($socket);
            if (!str_starts_with($banner, '220')) {
                throw new \RuntimeException("Unexpected SMTP Banner: {$banner}");
            }

            // 2. Send EHLO
            static::sendCommand($socket, "EHLO beyondbarista.rw");
            $ehloResp = static::readResponse($socket);

            // 3. Upgrade to STARTTLS if configured on port 587 / TLS
            if (!$isSsl && ($port === 587 || $encryption === 'tls')) {
                static::sendCommand($socket, "STARTTLS");
                $tlsResp = static::readResponse($socket);
                if (!str_starts_with($tlsResp, '220')) {
                    throw new \RuntimeException("STARTTLS failed: {$tlsResp}");
                }
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException("Failed to establish TLS encryption stream.");
                }
                static::sendCommand($socket, "EHLO beyondbarista.rw");
                $ehloResp = static::readResponse($socket);
            }

            // 4. Authenticate
            if (!empty($username) && !empty($password)) {
                static::sendCommand($socket, "AUTH LOGIN");
                $authResp = static::readResponse($socket);
                if (!str_starts_with($authResp, '334')) {
                    throw new \RuntimeException("AUTH LOGIN rejected: {$authResp}");
                }

                static::sendCommand($socket, base64_encode($username));
                $userResp = static::readResponse($socket);
                if (!str_starts_with($userResp, '334')) {
                    throw new \RuntimeException("Username rejected: {$userResp}");
                }

                static::sendCommand($socket, base64_encode($password));
                $passResp = static::readResponse($socket);
                if (!str_starts_with($passResp, '235')) {
                    throw new \RuntimeException("Password authentication failed: {$passResp}");
                }
            } else {
                throw new \RuntimeException("SMTP username/password is empty! Username: '{$username}', Password length: " . strlen($password));
            }

            // 5. MAIL FROM
            static::sendCommand($socket, "MAIL FROM:<{$fromAddress}>");
            $mailFromResp = static::readResponse($socket);
            if (!str_starts_with($mailFromResp, '250')) {
                throw new \RuntimeException("MAIL FROM rejected: {$mailFromResp}");
            }

            // 6. RCPT TO
            static::sendCommand($socket, "RCPT TO:<{$toEmail}>");
            $rcptResp = static::readResponse($socket);
            if (!str_starts_with($rcptResp, '250') && !str_starts_with($rcptResp, '251')) {
                throw new \RuntimeException("RCPT TO rejected: {$rcptResp}");
            }

            // 7. DATA
            static::sendCommand($socket, "DATA");
            $dataResp = static::readResponse($socket);
            if (!str_starts_with($dataResp, '354')) {
                throw new \RuntimeException("DATA command rejected: {$dataResp}");
            }

            // 8. Build RFC 2822 Message Payload
            $boundary = "bba_mime_" . md5(uniqid((string)time(), true));
            $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
            $fromHeader = !empty($fromName) ? "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddress}>" : "<{$fromAddress}>";
            $toHeader = !empty($toName) ? "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>" : "<{$toEmail}>";
            $dateHeader = date('r');

            $headers = [
                "Date: {$dateHeader}",
                "From: {$fromHeader}",
                "Reply-To: {$fromHeader}",
                "To: {$toHeader}",
                "Subject: {$encodedSubject}",
                "MIME-Version: 1.0",
                "X-Mailer: Beyond Barista LMS Mailer 2.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\""
            ];

            $altText = $plainTextBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($altText)) . "\r\n";

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";

            $body .= "--{$boundary}--\r\n";

            $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
            static::sendCommand($socket, $payload);
            $sendResp = static::readResponse($socket);

            if (!str_starts_with($sendResp, '250')) {
                throw new \RuntimeException("Message delivery rejected: {$sendResp}");
            }

            static::sendCommand($socket, "QUIT");
            fclose($socket);

            return [
                'success' => true,
                'message' => 'Email sent successfully.',
                'recipient' => $toEmail
            ];

        } catch (\Throwable $e) {
            if (is_resource($socket)) {
                @fputs($socket, "QUIT\r\n");
                @fclose($socket);
            }
            error_log("[MailService Error] " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private static function sendCommand($socket, string $cmd): void {
        fputs($socket, $cmd . "\r\n");
    }

    private static function readResponse($socket): string {
        $response = '';
        while ($line = fgets($socket, 1024)) {
            $response .= $line;
            if ((strlen($line) >= 4 && $line[3] === ' ') || strlen(trim($line)) === 3) {
                break;
            }
        }
        return trim($response);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Transactional Luxury Brand Email Templates
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Wrap HTML in Beyond Barista Academy luxury gold/chocolate template.
     */
    public static function wrapTemplate(string $title, string $contentHtml, ?string $actionText = null, ?string $actionUrl = null): string {
        $appUrl = app_url();
        $buttonHtml = '';
        if ($actionText && $actionUrl) {
            $buttonHtml = "
                <table border='0' cellpadding='0' cellspacing='0' style='margin: 28px 0;'>
                    <tr>
                        <td align='center' bgcolor='#C59B27' style='border-radius: 8px;'>
                            <a href='{$actionUrl}' target='_blank' style='display: inline-block; padding: 14px 32px; font-family: Arial, sans-serif; font-size: 15px; font-weight: bold; color: #180D06; text-decoration: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(197, 155, 39, 0.3);'>
                                {$actionText} &rarr;
                            </a>
                        </td>
                    </tr>
                </table>
            ";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #F8F9FA; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #2B1810; line-height: 1.6;'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #F8F9FA; padding: 30px 0;'>
                <tr>
                    <td align='center'>
                        <!-- Email Card Container -->
                        <table border='0' cellpadding='0' cellspacing='0' width='600' style='max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(24, 13, 6, 0.08); border: 1px solid #EFE8E1;'>
                            
                            <!-- Header Bar -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #180D06 0%, #2B1810 100%); padding: 32px 30px; text-align: center; border-bottom: 3px solid #C59B27;'>
                                    <h1 style='margin: 0; font-size: 22px; font-weight: bold; color: #F3C78E; letter-spacing: 0.5px;'>BEYOND BARISTA ACADEMY</h1>
                                    <p style='margin: 6px 0 0 0; font-size: 12px; color: #E6D2B5; text-transform: uppercase; letter-spacing: 2px;'>Center of Specialty Coffee & Hospitality Excellence</p>
                                </td>
                            </tr>

                            <!-- Body Content -->
                            <tr>
                                <td style='padding: 36px 32px; font-size: 15px; color: #333333;'>
                                    <h2 style='margin: 0 0 18px 0; font-size: 20px; color: #180D06; font-weight: bold;'>{$title}</h2>
                                    
                                    {$contentHtml}

                                    {$buttonHtml}

                                    <hr style='border: none; border-top: 1px solid #EFE8E1; margin: 30px 0 20px 0;'>

                                    <p style='margin: 0; font-size: 12px; color: #888888; line-height: 1.5;'>
                                        If you did not make this request or believe you received this in error, you can safely ignore this email or contact us at <a href='mailto:info@beyondbarista.rw' style='color: #C59B27; text-decoration: none;'>info@beyondbarista.rw</a>.
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #FDFBF7; padding: 24px 30px; text-align: center; border-top: 1px solid #F0EAE1;'>
                                    <p style='margin: 0 0 6px 0; font-size: 12px; color: #777777; font-weight: bold;'>Beyond Barista Academy — Kigali, Rwanda</p>
                                    <p style='margin: 0; font-size: 11px; color: #999999;'>&copy; " . date('Y') . " Beyond Barista Academy Ltd. All rights reserved. <br><a href='{$appUrl}' style='color: #C59B27; text-decoration: none;'>beyondbarista.rw</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Send Account Invitation Email.
     */
    public static function sendInvitation(string $toEmail, string $name, string $inviteUrl, string $roleName = 'Student'): array {
        $title = "You're Invited to Join Beyond Barista Academy";
        $content = "
            <p>Dear <strong>{$name}</strong>,</p>
            <p>An account has been provisioned for you on the <strong>Beyond Barista Academy LMS</strong> with the role of <strong>{$roleName}</strong>.</p>
            <p>To finalize your account setup, choose your secure password, and unlock your personalized learning dashboard, please click the button below:</p>
        ";
        $html = static::wrapTemplate($title, $content, "Accept Invitation & Setup Password", $inviteUrl);
        return static::send($toEmail, "Official Invitation: Beyond Barista Academy", $html);
    }

    /**
     * Send Password Reset Email.
     */
    public static function sendPasswordReset(string $toEmail, string $name, string $resetUrl): array {
        $title = "Reset Your Account Password";
        $content = "
            <p>Hello <strong>{$name}</strong>,</p>
            <p>We received a request to reset your password for your Beyond Barista Academy account.</p>
            <p>Click the button below to choose a new password. This reset link is valid for <strong>60 minutes</strong>.</p>
        ";
        $html = static::wrapTemplate($title, $content, "Reset My Password", $resetUrl);
        return static::send($toEmail, "Password Reset Request — Beyond Barista Academy", $html);
    }

    /**
     * Send Course Enrollment Welcome Email.
     */
    public static function sendEnrollmentConfirmation(string $toEmail, string $name, string $courseTitle, string $classroomUrl): array {
        $title = "Enrollment Confirmed: {$courseTitle}";
        $content = "
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Congratulations! Your enrollment in <strong>{$courseTitle}</strong> is now active.</p>
            <p>You have full access to interactive video lessons, coffee brewing formulations, study notes, curriculum resources, and the certification assessment.</p>
        ";
        $html = static::wrapTemplate($title, $content, "Enter Student Classroom", $classroomUrl);
        return static::send($toEmail, "Enrollment Confirmed — {$courseTitle}", $html);
    }

    /**
     * Send Certificate Issuance Notification Email.
     */
    public static function sendCertificateIssued(string $toEmail, string $name, string $courseTitle, string $certNumber, string $certUrl): array {
        $title = "Official Certificate Awarded: {$courseTitle}";
        $content = "
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Congratulations on successfully completing all curriculum requirements and assessment examinations for <strong>{$courseTitle}</strong>!</p>
            <div style='background-color: #FDFBF7; border: 1px solid #C59B27; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center;'>
                <p style='margin: 0; font-size: 12px; text-transform: uppercase; color: #888888; letter-spacing: 1px;'>Certificate Serial Number</p>
                <p style='margin: 4px 0 0 0; font-size: 18px; font-weight: bold; color: #180D06;'>{$certNumber}</p>
            </div>
            <p>Your verified digital credential is now ready for download, printing, and sharing directly to your LinkedIn profile.</p>
        ";
        $html = static::wrapTemplate($title, $content, "View & Download Certificate", $certUrl);
        return static::send($toEmail, "Certificate Awarded: {$courseTitle} ({$certNumber})", $html);
    }
}
