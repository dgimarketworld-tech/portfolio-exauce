<?php
/**
 * GTB SMS Banking — Envoi d'une alerte SMS à un utilisateur
 * Usage interne : send_sms_alert($userId, $message)
 * Nécessite Twilio SDK ou cURL direct.
 */
require_once __DIR__ . '/../../../backend/config.php';
require_once __DIR__ . '/../../../backend/db.php';

function send_sms_alert(int $userId, string $message): bool {
    $sms = DB::one("SELECT telephone FROM sms_banking WHERE user_id=:id AND is_active=1 AND alert_debit=1", ['id'=>$userId]);
    if (!$sms) return false;

    $tel = '+' . $sms['telephone'];

    // ── Twilio ──────────────────────────────────────────────────
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN') || !defined('TWILIO_FROM_NUMBER')) {
        // En dev : log uniquement
        error_log("[GTB-SMS] TO=$tel MSG=$message");
        // Logger dans la table quand même
        DB::insertInto('sms_banking_logs', [
            'user_id'   => $userId,
            'direction' => 'OUT',
            'telephone' => $tel,
            'contenu'   => substr($message, 0, 500),
            'commande'  => 'ALERT',
            'statut'    => 'pending',
        ]);
        return true;
    }

    $sid  = TWILIO_ACCOUNT_SID;
    $token= TWILIO_AUTH_TOKEN;
    $from = TWILIO_FROM_NUMBER;

    $payload = http_build_query(['To'=>$tel,'From'=>$from,'Body'=>$message]);
    $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json");
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_USERPWD        => "$sid:$token",
    ]);
    $resp   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $ok = $status >= 200 && $status < 300;
    DB::insertInto('sms_banking_logs', [
        'user_id'   => $userId,
        'direction' => 'OUT',
        'telephone' => $tel,
        'contenu'   => substr($message, 0, 500),
        'commande'  => 'ALERT',
        'statut'    => $ok ? 'success' : 'error',
    ]);

    if (!$ok) error_log("[GTB-SMS] Erreur Twilio status=$status resp=$resp");
    return $ok;
}

/**
 * Alerte débit
 */
function sms_alert_debit(int $userId, float $montant, string $libelle, float $nouveauSolde): void {
    $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id AND is_active=1 AND alert_debit=1", ['id'=>$userId]);
    if (!$sms) return;
    if ($montant < (float)($sms['alert_min'] ?? 0)) return;

    $msg = "GTB DEBIT : -" . number_format($montant, 2, ',', ' ') . "€\n"
         . mb_substr($libelle, 0, 30) . "\n"
         . "Solde : " . number_format($nouveauSolde, 2, ',', ' ') . "€\n"
         . date('d/m/Y H:i');
    send_sms_alert($userId, $msg);
}

/**
 * Alerte crédit
 */
function sms_alert_credit(int $userId, float $montant, string $libelle, float $nouveauSolde): void {
    $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id AND is_active=1 AND alert_credit=1", ['id'=>$userId]);
    if (!$sms) return;
    if ($montant < (float)($sms['alert_min'] ?? 0)) return;

    $msg = "GTB CREDIT : +" . number_format($montant, 2, ',', ' ') . "€\n"
         . mb_substr($libelle, 0, 30) . "\n"
         . "Solde : " . number_format($nouveauSolde, 2, ',', ' ') . "€\n"
         . date('d/m/Y H:i');
    send_sms_alert($userId, $msg);
}
