<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Création d'un administrateur
 *
 *  Usage CLI :
 *    php bin/create-admin.php email password "Prénom" "Nom" [role]
 *
 *  Usage navigateur (DEV uniquement) :
 *    /gtb-new/bin/create-admin.php?email=...&password=...&first=...&last=...
 *    (refusé en production)
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/security.php';

$isCli = php_sapi_name() === 'cli';

// Sécurité : si HTTP, refuser en production
if (!$isCli && !GTB_DEBUG) {
    http_response_code(403);
    die('Ce script n\'est accessible qu\'en CLI en production.');
}

// Récupération des paramètres
if ($isCli) {
    $email     = $argv[1] ?? null;
    $password  = $argv[2] ?? null;
    $firstName = $argv[3] ?? null;
    $lastName  = $argv[4] ?? null;
    $role      = $argv[5] ?? 'superadmin';
} else {
    $email     = $_GET['email']     ?? null;
    $password  = $_GET['password']  ?? null;
    $firstName = $_GET['first']     ?? null;
    $lastName  = $_GET['last']      ?? null;
    $role      = $_GET['role']      ?? 'superadmin';
}

if (!$email || !$password || !$firstName || !$lastName) {
    $msg = "Usage : php bin/create-admin.php <email> <password> <first> <last> [role]\n"
         . "Roles : superadmin (def.), admin, compliance, support, readonly";
    if ($isCli) { echo $msg; exit(1); } else { echo nl2br($msg); exit; }
}

$validRoles = ['superadmin','admin','compliance','support','readonly'];
if (!in_array($role, $validRoles, true)) {
    echo "Rôle invalide. Valides : " . implode(', ', $validRoles) . "\n";
    exit(1);
}

// Vérif unicité
$exists = DB::scalar("SELECT 1 FROM admins WHERE email = :e", ['e' => strtolower($email)]);
if ($exists) {
    echo "❌ Un administrateur avec cet email existe déjà.\n";
    exit(1);
}

$id = DB::insertInto('admins', [
    'email'         => strtolower($email),
    'password_hash' => Security::hashPassword($password),
    'first_name'    => $firstName,
    'last_name'     => $lastName,
    'role'           => $role,
    'status'         => 'active',
    'two_fa_enabled' => 0,
    'created_at'     => date('Y-m-d H:i:s'),
]);

$out = "✅ Administrateur créé avec succès\n"
     . "   ID    : $id\n"
     . "   Email : $email\n"
     . "   Rôle  : $role\n"
     . "   Nom   : $firstName $lastName\n";

if ($isCli) echo $out; else echo '<pre>' . htmlspecialchars($out) . '</pre>';