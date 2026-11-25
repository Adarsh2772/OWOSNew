<?php
/**
 * OSC contact form endpoint.
 * POST (multipart/form-data or x-www-form-urlencoded) → JSON {ok, message, ref?}
 *
 * Sends two emails via SMTP (PHPMailer):
 *   1. Company notification → OSC_COMPANY_TO (Reply-To: the submitter)
 *   2. Sender confirmation  → the submitter  (Reply-To: OSC_COMPANY_REPLY_TO)
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/templates/email.php';

if (!is_file(__DIR__ . '/config.php')) {
    respond(500, false, 'Mail service is not configured.');
}
require __DIR__ . '/config.php';

// ---- CORS (only needed when the form is hosted on a different domain) ----
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, OSC_ALLOWED_ORIGINS, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'Method not allowed.');
}

// ---- Honeypot: real users never fill the hidden "website" field ----
if (trim((string)($_POST['website'] ?? '')) !== '') {
    respond(200, true, 'Thank you. Your inquiry has been received.'); // silently drop bots
}

// ---- Rate limit per IP ----
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!rate_limit_ok($ip)) {
    respond(429, false, 'Too many submissions. Please try again in a few minutes or call 973-227-5112.');
}

// ---- Validate input ----
$allowedCaps = [
    'Inflight Menu & Media Programs (Cabin Menus, ChefChats, IFE Feeds)',
    'Enterprise Multilingual Translation & Technical Manuals',
    'Commercial Publishing, Prepress & Print Fulfillment',
    'EMMIS Platform Demonstration & Architecture Briefing',
];
$data = [
    'name'         => clean_line($_POST['full_name'] ?? '', 100),
    'email'        => clean_line($_POST['work_email'] ?? '', 254),
    'company'      => clean_line($_POST['company_name'] ?? '', 150),
    'phone'        => clean_line($_POST['phone_number'] ?? '', 40),
    'details'      => clean_text($_POST['project_details'] ?? '', 5000),
    'capabilities' => array_values(array_intersect($allowedCaps, array_map('strval', (array)($_POST['capabilities'] ?? [])))),
];
$errors = [];
if ($data['name'] === '')    $errors['full_name'] = 'Please enter your full name.';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['work_email'] = 'Please enter a valid email address.';
if ($data['company'] === '') $errors['company_name'] = 'Please enter your company or organization.';
if ($data['details'] === '') $errors['project_details'] = 'Please describe your program scope or requirements.';
if ($data['phone'] !== '' && !preg_match('/^[0-9+().\-\s]{5,40}$/', $data['phone'])) $errors['phone_number'] = 'Please enter a valid phone number.';
if ($errors) {
    respond(422, false, reset($errors), ['errors' => $errors]);
}

$meta = [
    'ref'  => 'OSC-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
    'time' => gmdate('D, d M Y H:i') . ' UTC',
    'page' => clean_line($_SERVER['HTTP_REFERER'] ?? 'contact.html', 300),
    'ip'   => $ip,
];

// ---- Send ----
try {
    [$subject, $html, $text] = osc_company_email($data, $meta);
    send_mail(OSC_COMPANY_TO, '', $subject, $html, $text, $data['email'], $data['name']);
} catch (MailException $ex) {
    error_log('[osc-contact] company mail failed: ' . $ex->getMessage());
    respond(502, false, 'We could not send your inquiry right now. Please email inquiries@oneworldonestop.com or call 973-227-5112.');
}

$confirmationSent = true;
try {
    [$subject, $html, $text] = osc_sender_email($data, $meta);
    send_mail($data['email'], $data['name'], $subject, $html, $text, OSC_COMPANY_REPLY_TO, OSC_FROM_NAME);
} catch (MailException $ex) {
    $confirmationSent = false; // the team still has the inquiry; don't fail the request
    error_log('[osc-contact] confirmation mail failed: ' . $ex->getMessage());
}

respond(200, true, 'Thank you. Your inquiry has been received.', ['ref' => $meta['ref'], 'confirmation' => $confirmationSent]);


// ======================================================================

function send_mail(string $to, string $toName, string $subject, string $html, string $text, string $replyTo, string $replyName): void
{
    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->Host       = BBR_SMTP_HOST;
    $m->Port       = BBR_SMTP_PORT;
    $m->SMTPSecure = BBR_SMTP_SECURE === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $m->SMTPAuth   = true;
    $m->Username   = BBR_SMTP_USER;
    $m->Password   = BBR_SMTP_PASS;
    $m->Timeout    = 20;
    $m->CharSet    = PHPMailer::CHARSET_UTF8;

    $m->setFrom(OSC_FROM_EMAIL, OSC_FROM_NAME);
    $m->addAddress($to, $toName);
    $m->addReplyTo($replyTo, $replyName);
    $m->Subject = $subject;
    $m->isHTML(true);
    $m->Body    = $html;
    $m->AltBody = $text;

    $logo = dirname(__DIR__) . '/assets/email-logo.png';
    if (is_file($logo)) {
        $m->addEmbeddedImage($logo, 'osclogo', 'osc-logo.png', PHPMailer::ENCODING_BASE64, 'image/png');
    }
    $m->send();
}

function clean_line($v, int $max): string
{
    $v = preg_replace('/[\r\n\t\x00-\x1F\x7F]+/u', ' ', (string)$v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

function clean_text($v, int $max): string
{
    $v = str_replace(["\r\n", "\r"], "\n", (string)$v);
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

function rate_limit_ok(string $ip): bool
{
    $file = sys_get_temp_dir() . '/osc_contact_' . md5($ip) . '.json';
    $now = time();
    $hits = is_file($file) ? (json_decode((string)file_get_contents($file), true) ?: []) : [];
    $hits = array_values(array_filter($hits, fn($t) => $t > $now - OSC_RATE_WINDOW));
    if (count($hits) >= OSC_RATE_LIMIT) return false;
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}

function respond(int $code, bool $ok, string $message, array $extra = []): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok' => $ok, 'message' => $message] + $extra);
    exit;
}
