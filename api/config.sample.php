<?php
/**
 * Contact form configuration — TEMPLATE.
 *
 * Copy this file to config.php (same folder) and fill in real values.
 * config.php is git-ignored: never commit SMTP passwords.
 */

// --- SMTP (Gmail: use an App Password, not the account password) ---
define('BBR_SMTP_HOST', 'smtp.googlemail.com');
define('BBR_SMTP_PORT', 465);
define('BBR_SMTP_SECURE', 'ssl');            // 'ssl' for 465, 'tls' for 587
define('BBR_SMTP_USER', 'you@example.com');
define('BBR_SMTP_PASS', 'your-app-password');

// --- Who sends and who receives ---
define('OSC_FROM_EMAIL', BBR_SMTP_USER);     // Gmail requires From = the SMTP account
define('OSC_FROM_NAME', "O'Sullivan Communications (OSC)");
define('OSC_COMPANY_TO', 'inquiries@oneworldonestop.com');   // receives new inquiries
define('OSC_COMPANY_REPLY_TO', 'inquiries@oneworldonestop.com'); // where customer replies go

// --- Public URLs (used for links inside the emails) ---
define('OSC_SITE_URL', 'https://adarsh2772.github.io/OWOS/');

// --- Origins allowed to POST from another domain (CORS). Same-origin needs nothing here. ---
define('OSC_ALLOWED_ORIGINS', ['https://adarsh2772.github.io']);

// --- Abuse protection: max submissions per IP per window ---
define('OSC_RATE_LIMIT', 5);
define('OSC_RATE_WINDOW', 600); // seconds
