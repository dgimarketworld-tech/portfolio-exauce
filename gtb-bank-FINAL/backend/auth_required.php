<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Garde d'authentification CLIENT
 *
 *  Inclus en tête de toute page nécessitant un client connecté.
 *  Redirige vers /authentification/login.php si non authentifié.
 *
 *  Usage :
 *    require_once __DIR__ . '/../backend/auth_required.php';
 *    $user = $currentUser; // disponible
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

Session::start();

// Pas connecté → redirection login
if (!Session::isUser()) {
    $returnTo = $_SERVER['REQUEST_URI'] ?? '/';
    $_SESSION['_return_to'] = $returnTo;
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=auth_required');
}

// Vérification que l'utilisateur existe toujours et n'est pas suspendu
$userId = Session::userId();
$currentUser = DB::one(
    "SELECT id, client_number, email, first_name, last_name, civility, telephone AS phone,
            avatar_url, kyc_status, status, two_fa_enabled, language, plan
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

// Hydrate la session avec les infos fraîches (au cas où mise à jour côté admin)
$_SESSION['user'] = array_merge($_SESSION['user'] ?? [], [
    'first_name' => $currentUser['first_name'],
    'last_name'  => $currentUser['last_name'],
    'avatar_url' => $currentUser['avatar_url'],
    'kyc_status' => $currentUser['kyc_status'],
]);

// CSRF auto pour les POST
Security::requireCsrf();