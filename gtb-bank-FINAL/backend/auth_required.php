<?php
/**
 * GTB BANK — Garde d'authentification CLIENT
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

Session::start();

if (!Session::isUser()) {
    $returnTo = $_SERVER['REQUEST_URI'] ?? '/';
    $_SESSION['_return_to'] = $returnTo;
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=auth_required');
}

$userId = Session::userId();
$currentUser = DB::one(
    "SELECT id, client_number, email,
            first_name, last_name, telephone AS phone,
            avatar_url, kyc_status, status, two_fa_enabled, plan,
            region, langue, devise, interface_color, pref_theme,
            pref_email_alerts, pref_sms_alerts,
            access_blocked, access_block_reason, access_block_type, access_block_until,
            transfer_stop_pct
     FROM users
     WHERE id = :id
     LIMIT 1",
    ['id' => $userId]
);

if (!$currentUser) {
    Session::destroy();
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=user_not_found');
}

if ($currentUser['status'] === 'suspended') {
    Session::destroy();
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=account_suspended');
}

if ($currentUser['status'] === 'closed') {
    Session::destroy();
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=account_closed');
}

// Blocage admin
if (!empty($currentUser['access_blocked'])) {
    $blockUntil = $currentUser['access_block_until'];
    if ($currentUser['access_block_type'] === 'temporary' && $blockUntil && strtotime($blockUntil) < time()) {
        DB::run("UPDATE users SET access_blocked=0 WHERE id=:id", ['id' => $userId]);
    } else {
        $reason = e($currentUser['access_block_reason'] ?? 'Votre compte a été temporairement suspendu.');
        $until  = ($blockUntil && $currentUser['access_block_type'] === 'temporary')
            ? '<p style="color:#6b7280;font-size:13px">Déblocage prévu le : <strong>' . date('d/m/Y à H:i', strtotime($blockUntil)) . '</strong></p>'
            : '';
        http_response_code(403);
        die('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Accès bloqué — GTB</title>
        <style>body{font-family:sans-serif;background:#f3f4f6;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .box{background:white;padding:2.5rem;border-radius:16px;max-width:480px;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,.1)}
        h2{color:#1a3c5e;margin-bottom:.75rem}p{color:#374151;line-height:1.6}</style></head><body>
        <div class="box"><div style="font-size:3rem;margin-bottom:1rem">🔒</div>
        <h2>Accès temporairement bloqué</h2>
        <p>' . $reason . '</p>' . $until . '
        <p style="margin-top:1.5rem;font-size:13px;color:#9ca3af">Contactez le support : <a href="mailto:support@globaltrusty.com">support@globaltrusty.com</a></p>
        </div></body></html>');
    }
}

$_SESSION['user'] = array_merge($_SESSION['user'] ?? [], [
    'first_name' => $currentUser['first_name'],
    'last_name'  => $currentUser['last_name'],
    'avatar_url' => $currentUser['avatar_url'],
    'kyc_status' => $currentUser['kyc_status'],
]);

Security::requireCsrf();
