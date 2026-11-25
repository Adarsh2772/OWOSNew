<?php
/**
 * Contact form configuration — REAL VALUES. Git-ignored; do not commit.
 * See config.sample.php for documentation.
 */

define('BBR_SMTP_HOST', 'smtp.googlemail.com');
define('BBR_SMTP_PORT', 465);
define('BBR_SMTP_SECURE', 'ssl');
define('BBR_SMTP_USER', 'adarshsolankurkar8367@gmail.com');
define('BBR_SMTP_PASS', 'nasclmwwscprbczi');

define('OSC_FROM_EMAIL', BBR_SMTP_USER);
define('OSC_FROM_NAME', "O'Sullivan Communications (OSC)");
define('OSC_COMPANY_TO', 'adarshsolankurkar8367@gmail.com');
define('OSC_COMPANY_REPLY_TO', 'adarshsolankurkar8367@gmail.com');

define('OSC_SITE_URL', 'https://adarsh2772.github.io/OWOS/');
define('OSC_ALLOWED_ORIGINS', ['https://adarsh2772.github.io']);

define('OSC_RATE_LIMIT', 5);
define('OSC_RATE_WINDOW', 600);
