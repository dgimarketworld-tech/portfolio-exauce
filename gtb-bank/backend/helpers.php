<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Fonctions utilitaires (helpers)
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ─────────────────────────────────────────────────────────────────
//  FORMATAGE MONÉTAIRE
// ─────────────────────────────────────────────────────────────────

/**
 * Formate un montant en euros à la française : 1 234,56 €
 */
function format_money(float|string|null $amount, string $currency = 'EUR', bool $withSymbol = true): string
{
    $amount = (float) ($amount ?? 0);
    $formatted = number_format($amount, 2, ',', "\u{202F}"); // espace fine insécable
    if (!$withSymbol) return $formatted;

    return match($currency) {
        'EUR' => $formatted . ' €',
        'USD' => '$ ' . $formatted,
        'GBP' => '£ ' . $formatted,
        'XOF', 'XAF' => $formatted . ' FCFA',
        default => $formatted . ' ' . $currency,
    };
}

/**
 * Formate un montant signé avec sa direction.
 * Ex: format_money_signed(50, 'credit') => "+50,00 €"
 */
function format_money_signed(float|string $amount, string $direction = 'debit', string $currency = 'EUR'): string
{
    $sign = $direction === 'credit' ? '+' : '-';
    return $sign . format_money(abs((float) $amount), $currency);
}

// ─────────────────────────────────────────────────────────────────
//  DATES
// ─────────────────────────────────────────────────────────────────

function format_date(?string $datetime, string $pattern = 'd M Y'): string
{
    if (!$datetime) return '—';
    $months_fr = [
        '01' => 'janv.', '02' => 'févr.', '03' => 'mars',  '04' => 'avr.',
        '05' => 'mai',   '06' => 'juin',  '07' => 'juil.', '08' => 'août',
        '09' => 'sept.', '10' => 'oct.',  '11' => 'nov.',  '12' => 'déc.',
    ];
    $ts = strtotime($datetime);
    if ($ts === false) return '—';
    $formatted = date($pattern, $ts);
    foreach ($months_fr as $num => $name) {
        $formatted = str_replace(
            [date('M', mktime(0,0,0,(int)$num,1)), date('F', mktime(0,0,0,(int)$num,1))],
            [$name, $name],
            $formatted
        );
    }
    return $formatted;
}

function format_datetime(?string $datetime): string
{
    if (!$datetime) return '—';
    $ts = strtotime($datetime);
    if ($ts === false) return '—';
    return format_date($datetime, 'd M Y') . ' à ' . date('H\hi', $ts);
}

/** "il y a 2 heures" */
function time_ago(?string $datetime): string
{
    if (!$datetime) return '—';
    $ts = strtotime($datetime);
    if ($ts === false) return '—';
    $diff = time() - $ts;
    if ($diff < 60)      return 'À l\'instant';
    if ($diff < 3600)    return 'il y a ' . intdiv($diff, 60)   . ' min';
    if ($diff < 86400)   return 'il y a ' . intdiv($diff, 3600) . ' h';
    if ($diff < 604800)  return 'il y a ' . intdiv($diff, 86400). ' j';
    return format_date($datetime);
}

// ─────────────────────────────────────────────────────────────────
//  RÉFÉRENCES MÉTIER (TRX, TIC, LOAN, etc.)
// ─────────────────────────────────────────────────────────────────

function generate_reference(string $prefix, int $length = 6): string
{
    $rand = '';
    for ($i = 0; $i < $length; $i++) {
        $rand .= random_int(0, 9);
    }
    return strtoupper($prefix) . '-' . date('Ymd') . '-' . $rand;
}

function generate_client_number(): string
{
    do {
        $num = 'GTB-' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $exists = DB::scalar("SELECT 1 FROM users WHERE client_number = :n", ['n' => $num]);
    } while ($exists);
    return $num;
}

// ─────────────────────────────────────────────────────────────────
//  REDIRECTION & RÉPONSES
// ─────────────────────────────────────────────────────────────────

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_ok(array $data = [], string $message = 'OK'): never
{
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

function json_error(string $message, int $status = 400, array $errors = []): never
{
    json_response(
        ['success' => false, 'message' => $message, 'errors' => $errors],
        $status
    );
}

// ─────────────────────────────────────────────────────────────────
//  VALIDATION
// ─────────────────────────────────────────────────────────────────

function validate_email(?string $email): bool
{
    return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_phone(?string $phone): bool
{
    if (!$phone) return false;
    $clean = preg_replace('/[\s.\-()]/', '', $phone);
    return (bool) preg_match('/^\+?[0-9]{8,15}$/', $clean);
}

function validate_date(?string $date, string $format = 'Y-m-d'): bool
{
    if (!$date) return false;
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Calcule l'âge à partir d'une date de naissance (Y-m-d).
 */
function compute_age(string $birthDate): int
{
    return (new DateTime())->diff(new DateTime($birthDate))->y;
}

// ─────────────────────────────────────────────────────────────────
//  NOTIFICATIONS — Helper de création
// ─────────────────────────────────────────────────────────────────

function notify(
    int $userId,
    string $title,
    string $message,
    string $type = 'info',
    string $icon = 'fa-bell',
    ?string $linkUrl = null,
    ?string $linkLabel = null
): int {
    return DB::insertInto('notifications', [
        'user_id'    => $userId,
        'type'       => $type,
        'icon'       => $icon,
        'title'      => $title,
        'message'    => $message,
        'link_url'   => $linkUrl,
        'link_label' => $linkLabel,
    ]);
}

// ─────────────────────────────────────────────────────────────────
//  INITIALES (pour avatar par défaut)
// ─────────────────────────────────────────────────────────────────

function initials(string $first, string $last): string
{
    $f = mb_strtoupper(mb_substr(trim($first), 0, 1, 'UTF-8'), 'UTF-8');
    $l = mb_strtoupper(mb_substr(trim($last),  0, 1, 'UTF-8'), 'UTF-8');
    return $f . $l;
}

// ─────────────────────────────────────────────────────────────────
//  RACCOURCI ESCAPE HTML
// ─────────────────────────────────────────────────────────────────

if (!function_exists('e')) {
    function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// ─────────────────────────────────────────────────────────────────
//  PAGINATION
// ─────────────────────────────────────────────────────────────────

function pagination(int $totalItems, int $perPage, int $currentPage): array
{
    $totalPages = (int) max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    return [
        'total_items' => $totalItems,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => ($currentPage - 1) * $perPage,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
    ];
}

// ─────────────────────────────────────────────────────────────────
//  EMAIL — Brevo API
// ─────────────────────────────────────────────────────────────────

function send_email(string $toEmail, string $toName, string $subject, string $htmlContent): bool
{
    $payload = json_encode([
        'sender'      => ['name' => MAIL_FROM_NAME, 'email' => MAIL_FROM],
        'to'          => [['email' => $toEmail, 'name' => $toName]],
        'replyTo'     => ['email' => MAIL_SUPPORT, 'name' => MAIL_FROM_NAME],
        'subject'     => $subject,
        'htmlContent' => $htmlContent,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        error_log("[GTB-BREVO] Erreur envoi email à {$toEmail}: HTTP {$status} — {$response}");
        return false;
    }
    return true;
}

function send_notification_email(int $userId, string $subject, string $body): bool
{
    $user = DB::one("SELECT email, COALESCE(prenom, first_name, '') AS prenom, COALESCE(nom, last_name, '') AS nom FROM users WHERE id = :id", ['id' => $userId]);
    if (!$user) return false;

    $name = trim($user['prenom'] . ' ' . $user['nom']) ?: 'Client';
    $html = "
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'>
      <h2 style='color:#1a3c5e'>Global Trust Bank</h2>
      <p style='color:#374151'>Bonjour <strong>{$name}</strong>,</p>
      <div style='background:#f3f4f6;padding:16px;border-radius:6px;margin:16px 0;color:#374151'>{$body}</div>
      <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'>
      <p style='color:#9ca3af;font-size:12px'>Global Trust Bank — La banque d'un monde qui change</p>
    </div>";
    return send_email($user['email'], $name, $subject, $html);
}

function send_otp_email(string $toEmail, string $toName, string $otp): bool
{
    $subject = 'Votre code de vérification GTB Bank';
    $html = "
    <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'>
      <h2 style='color:#1a3c5e;margin-bottom:8px'>Global Trust Bank</h2>
      <p style='color:#374151'>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
      <p style='color:#374151'>Votre code de vérification est :</p>
      <div style='font-size:36px;font-weight:bold;letter-spacing:8px;color:#1a3c5e;background:#f3f4f6;padding:16px;text-align:center;border-radius:6px;margin:16px 0'>$otp</div>
      <p style='color:#6b7280;font-size:13px'>Ce code expire dans 5 minutes. Ne le partagez avec personne.</p>
      <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'>
      <p style='color:#9ca3af;font-size:12px'>Global Trust Bank — La banque d'un monde qui change</p>
    </div>";
    return send_email($toEmail, $toName, $subject, $html);
}