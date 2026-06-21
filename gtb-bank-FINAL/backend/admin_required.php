<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Garde d'authentification ADMIN
 *
 *  À inclure en tête de toute page d'administration.
 *  Redirige vers /admin/login.php si non authentifié.
 *
 *  Pour exiger un rôle spécifique :
 *    $required_role = 'superadmin';
 *    require_once __DIR__ . '/../backend/admin_required.php';
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

Session::start();

// Hiérarchie des rôles (du plus puissant au plus faible)
$ROLE_RANK = [
    'superadmin' => 5,
    'admin'      => 4,
    'compliance' => 3,
    'support'    => 2,
    'readonly'   => 1,
];

if (!Session::isAdmin()) {
    $_SESSION['_return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
    redirect(GTB_BASE_URL . '/admin/login.php?reason=auth_required');
}

$adminId = Session::adminId();
$currentAdmin = DB::one(
    "SELECT id, email, first_name, last_name, role, permissions, status
     FROM admins WHERE id = :id LIMIT 1",
    ['id' => $adminId]
);

if (!$currentAdmin || $currentAdmin['status'] !== 'active') {
    Session::destroy();
    redirect(GTB_BASE_URL . '/authentification/login.php?reason=account_suspended');
}

// Vérification du rôle minimum requis (si défini par la page)
if (isset($required_role)) {
    $userRank = $ROLE_RANK[$currentAdmin['role']] ?? 0;
    $needRank = $ROLE_RANK[$required_role]        ?? 99;
    if ($userRank < $needRank) {
        http_response_code(403);
        die('Accès refusé : rôle insuffisant.');
    }
}

// Hydrate
$_SESSION['admin'] = array_merge($_SESSION['admin'] ?? [], [
    'first_name' => $currentAdmin['first_name'],
    'last_name'  => $currentAdmin['last_name'],
    'role'       => $currentAdmin['role'],
]);

Security::requireCsrf();