<?php
/**
 * HTML email templates for the OSC contact form.
 * Table-based layout with inline styles for Gmail / Outlook / Apple Mail compatibility.
 * The logo is attached inline with Content-ID "osclogo" (see contact.php).
 */

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Shared shell: header with logo, content slot, signature footer. */
function osc_email_layout(string $preheader, string $title, string $kicker, string $content): string
{
    $year = date('Y');
    $site = e(OSC_SITE_URL);
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<title>{$title}</title>
<style>
  @media (max-width:620px){ .osc-container{width:100% !important} .osc-pad{padding-left:20px !important;padding-right:20px !important} .osc-col{display:block !important;width:100% !important;box-sizing:border-box !important} .osc-col-gap{padding-top:16px !important} }
</style>
</head>
<body style="margin:0;padding:0;background:#eef2f8;-webkit-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{$preheader}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f8;">
<tr><td align="center" style="padding:32px 12px;">
  <table role="presentation" class="osc-container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(9,76,178,0.08);">
    <!-- Header -->
    <tr><td style="height:6px;background:#094cb2;line-height:6px;font-size:0;">&nbsp;</td></tr>
    <tr><td class="osc-pad" style="padding:28px 40px 20px 40px;border-bottom:1px solid #e3e2e3;">
      <a href="{$site}" style="text-decoration:none;"><img src="cid:osclogo" width="200" height="69" alt="O'Sullivan Communications (OSC) – One World One Stop" style="display:block;width:200px;height:auto;border:0;"></a>
    </td></tr>
    <!-- Title -->
    <tr><td class="osc-pad" style="padding:32px 40px 8px 40px;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
      <div style="font-size:12px;line-height:1.4;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#6d5e00;">{$kicker}</div>
      <h1 style="margin:8px 0 0 0;font-family:'Noto Serif',Georgia,'Times New Roman',serif;font-size:26px;line-height:1.25;font-weight:600;color:#1b1c1d;">{$title}</h1>
    </td></tr>
    <!-- Content -->
    <tr><td class="osc-pad" style="padding:16px 40px 32px 40px;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#434653;">
      {$content}
    </td></tr>
    <!-- Signature footer -->
    <tr><td class="osc-pad" style="padding:28px 40px;background:#f6f7fb;border-top:1px solid #e3e2e3;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#434653;">
      <div style="font-family:'Noto Serif',Georgia,serif;font-size:16px;font-weight:600;color:#1b1c1d;">O'Sullivan Communications (OSC)</div>
      <div style="color:#6d5e00;font-size:12px;font-weight:600;letter-spacing:.5px;margin-bottom:14px;">Program Inquiries Team · One World, One Stop</div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="osc-col" width="50%" valign="top" style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#434653;padding-right:12px;">
            <strong style="color:#1b1c1d;">United States</strong><br>P.O. Box 1116, Glen Rock, NJ 07452<br>
            <a href="tel:9732275112" style="color:#094cb2;text-decoration:none;font-weight:600;">973-227-5112</a>
          </td>
          <td class="osc-col osc-col-gap" width="50%" valign="top" style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#434653;">
            <strong style="color:#1b1c1d;">United Kingdom</strong><br>Unit 3, Hurricane Way, Apex Park, Slough, SL3 8AG<br>
            <a href="tel:+441753942467" style="color:#094cb2;text-decoration:none;font-weight:600;">+44 01753 942467</a>
          </td>
        </tr>
      </table>
      <div style="margin-top:14px;">
        <a href="mailto:inquiries@oneworldonestop.com" style="color:#094cb2;text-decoration:none;font-weight:600;">inquiries@oneworldonestop.com</a>
        &nbsp;·&nbsp; <a href="{$site}" style="color:#094cb2;text-decoration:none;font-weight:600;">Visit our website</a>
      </div>
    </td></tr>
    <tr><td style="padding:14px 40px;background:#1b1c1d;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:11px;line-height:1.5;color:#c7c6ca;text-align:center;">
      A Maryknoll Group Company · Established 1973 · Member: ALC — Association of Language Companies<br>
      © {$year} O'Sullivan Communications. All rights reserved.
    </td></tr>
  </table>
</td></tr>
</table>
</body>
</html>
HTML;
}

/** Key/value rows for the submission details table. */
function osc_details_table(array $d): string
{
    $services = $d['capabilities']
        ? '<ul style="margin:0;padding-left:18px;">' . implode('', array_map(fn($c) => '<li style="margin:0 0 2px 0;">' . e($c) . '</li>', $d['capabilities'])) . '</ul>'
        : '<span style="color:#737784;">None selected</span>';
    $phone = $d['phone'] !== ''
        ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $d['phone'])) . '" style="color:#094cb2;text-decoration:none;">' . e($d['phone']) . '</a>'
        : '<span style="color:#737784;">Not provided</span>';
    $rows = [
        ['Full name', e($d['name'])],
        ['Work email', '<a href="mailto:' . e($d['email']) . '" style="color:#094cb2;text-decoration:none;">' . e($d['email']) . '</a>'],
        ['Company / organization', e($d['company'])],
        ['Direct telephone', $phone],
        ['Capabilities of interest', $services],
    ];
    $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e3e2e3;border-radius:8px;border-collapse:separate;overflow:hidden;">';
    foreach ($rows as $i => [$k, $v]) {
        $border = $i ? 'border-top:1px solid #e3e2e3;' : '';
        $html .= '<tr>'
            . '<td class="osc-col" width="38%" valign="top" style="' . $border . 'padding:12px 16px;background:#f6f7fb;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:12px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:#737784;">' . $k . '</td>'
            . '<td class="osc-col" valign="top" style="' . $border . 'padding:12px 16px;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#1b1c1d;">' . $v . '</td>'
            . '</tr>';
    }
    $html .= '</table>';
    $html .= '<div style="margin-top:20px;font-size:12px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:#737784;">Program scope or operational requirements</div>'
        . '<div style="margin-top:8px;padding:16px;background:#f6f7fb;border-left:3px solid #094cb2;border-radius:4px;font-size:14px;line-height:1.6;color:#1b1c1d;">' . nl2br(e($d['details'])) . '</div>';
    return $html;
}

/** Email 1: internal notification to the OSC team. */
function osc_company_email(array $d, array $meta): array
{
    $first = e(strtok($d['name'], ' ') ?: $d['name']);
    $subject = 'New Program Inquiry: ' . $d['name'] . ($d['company'] !== '' ? ' – ' . $d['company'] : '');
    $replyHref = e('mailto:' . $d['email'] . '?subject=' . rawurlencode('Re: Your program inquiry with OSC'));
    $content = '<p style="margin:0 0 20px 0;">A new inquiry was submitted through the website contact form. Reply directly to this email to respond to ' . $first . '.</p>'
        . osc_details_table($d)
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;"><tr><td style="border-radius:6px;background:#094cb2;">'
        . '<a href="' . $replyHref . '" style="display:inline-block;padding:13px 26px;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:6px;">Reply to ' . $first . ' &rarr;</a>'
        . '</td></tr></table>'
        . '<div style="margin-top:24px;padding-top:16px;border-top:1px dashed #e3e2e3;font-size:12px;line-height:1.6;color:#737784;">'
        . 'Submitted ' . e($meta['time']) . ' · Page: ' . e($meta['page']) . ' · IP: ' . e($meta['ip']) . ' · Ref: ' . e($meta['ref'])
        . '</div>';
    $html = osc_email_layout('New inquiry from ' . e($d['name']) . ' — ' . e($d['company']), 'New Program Inquiry', 'Website contact form · Ref ' . e($meta['ref']), $content);
    $text = "NEW PROGRAM INQUIRY (Ref {$meta['ref']})\n\n" . osc_details_text($d) . "\n\nSubmitted {$meta['time']} | Page: {$meta['page']} | IP: {$meta['ip']}\n";
    return [$subject, $html, $text];
}

/** Email 2: confirmation to the person who submitted the form. */
function osc_sender_email(array $d, array $meta): array
{
    $first = e(strtok($d['name'], ' ') ?: $d['name']);
    $subject = "We've received your program inquiry – O'Sullivan Communications (OSC)";
    $steps = [
        ['1', 'Review', 'Our U.S. and U.K. account teams review your requirements.'],
        ['2', 'Follow-up', 'A project manager follows up with you directly.'],
        ['3', 'Consultation', 'We scope timelines, languages, and production logistics with you.'],
    ];
    $stepsHtml = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 24px 0;">';
    foreach ($steps as [$n, $t, $s]) {
        $stepsHtml .= '<tr><td width="40" valign="top" style="padding:8px 0;"><div style="width:28px;height:28px;line-height:28px;border-radius:14px;background:#d9e2ff;color:#094cb2;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;">' . $n . '</div></td>'
            . '<td valign="top" style="padding:8px 0;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#434653;"><strong style="color:#1b1c1d;">' . $t . '</strong> — ' . $s . '</td></tr>';
    }
    $stepsHtml .= '</table>';
    $content = '<p style="margin:0 0 16px 0;">Dear ' . $first . ',</p>'
        . '<p style="margin:0 0 16px 0;">Thank you for contacting O\'Sullivan Communications (OSC). We have received your program inquiry and our team will be in touch shortly. Your reference number is <strong style="color:#1b1c1d;">' . e($meta['ref']) . '</strong>.</p>'
        . '<div style="font-size:12px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:#6d5e00;margin-top:8px;">What happens next</div>'
        . $stepsHtml
        . '<div style="font-size:12px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:#6d5e00;margin-bottom:10px;">A copy of your submission</div>'
        . osc_details_table($d)
        . '<p style="margin:24px 0 0 0;">Need to reach us sooner? Call U.S. Operations at <a href="tel:9732275112" style="color:#094cb2;text-decoration:none;font-weight:600;">973-227-5112</a> or simply reply to this email.</p>'
        . '<p style="margin:20px 0 0 0;">Kind regards,<br><strong style="color:#1b1c1d;">The OSC Program Inquiries Team</strong></p>';
    $html = osc_email_layout('Thank you, ' . $first . ' — we\'ve received your inquiry (Ref ' . e($meta['ref']) . ').', 'Thank you for your inquiry', 'Inquiry received · Ref ' . e($meta['ref']), $content);
    $text = "Dear {$d['name']},\n\nThank you for contacting O'Sullivan Communications (OSC). We have received your program inquiry (Ref {$meta['ref']}). "
        . "Our U.S. and U.K. account teams will review your requirements and a project manager will follow up with you directly.\n\n"
        . "A COPY OF YOUR SUBMISSION\n" . osc_details_text($d)
        . "\n\nNeed us sooner? Call U.S. Operations: 973-227-5112, or reply to this email.\n\nKind regards,\nThe OSC Program Inquiries Team\n"
        . "O'Sullivan Communications (OSC) · A Maryknoll Group Company · Established 1973\n";
    return [$subject, $html, $text];
}

function osc_details_text(array $d): string
{
    return "Name: {$d['name']}\nEmail: {$d['email']}\nCompany: {$d['company']}\nPhone: " . ($d['phone'] ?: 'Not provided')
        . "\nCapabilities: " . ($d['capabilities'] ? implode('; ', $d['capabilities']) : 'None selected')
        . "\n\nProgram scope:\n{$d['details']}";
}
