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
    "SELECT id,
            COALESCE(client_number, '')                            AS client_number,
            email,
            COALESCE(first_name, prenom, '')                      AS first_name,
            COALESCE(last_name,  nom,    '')                      AS last_name,
            telephone                                              AS phone,
            COALESCE(avatar_url, '')                              AS avatar_url,
            COALESCE(kyc_status, 'pending')                       AS kyc_status,
            COALESCE(status, IF(is_active=1,'active','suspended')) AS status,
            COALESCE(two_fa_enabled, 0)                           AS two_fa_enabled,
            COALESCE(plan, 'standard')                            AS plan
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

$_SESSION['user'] = array_merge($_SESSION['user'] ?? [], [
    'first_name' => $currentUser['first_name'],
    'last_name'  => $currentUser['last_name'],
    'avatar_url' => $currentUser['avatar_url'],
    'kyc_status' => $currentUser['kyc_status'],
]);

Security::requireCsrf();
