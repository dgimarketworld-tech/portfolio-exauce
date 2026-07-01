<?php
/**
 * GTB BANK — api/verify_otp.php
 * Étape 2 : valide OTP, ouvre la session, redirige
 */
header('Content-Type: application/json; charset=utf-8');
set_exception_handler(function(Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Erreur serveur.','debug'=>defined('GTB_DEBUG')&&GTB_DEBUG?$e->getMessage():null]);
    exit;
});
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/security.php';
require_once __DIR__ . '/../../backend/helpers.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    json_response(['success' => false, 'error' => 'Méthode non autorisée'], 405);

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$code = preg_replace('/\D/', '', (string)($data['code'] ?? ''));

$pending = Session::pending2FA();
if (!$pending)
    json_response(['success' => false, 'error' => 'Session expirée. Recommencez.'], 401);

if (strlen($code) !== OTP_LENGTH)
    json_response(['success' => false, 'error' => 'Code OTP invalide.']);

$otps = DB::all(
    "SELECT id, code_hash FROM otp_codes
     WHERE user_id = :uid AND used = 0 AND expires_at > NOW()
     ORDER BY id DESC LIMIT 3",
    ['uid' => $pending['user_id']]
);

$matched = null;
foreach ($otps as $otp)
    if (password_verify($code, $otp['code_hash'])) { $matched = $otp; break; }

if (!$matched)
    json_response(['success' => false, 'error' => 'Code incorrect ou expiré.']);

DB::update('UPDATE otp_codes SET used = 1 WHERE id = :id', ['id' => $matched['id']]);

$user = DB::one(
    "SELECT id, email,
            COALESCE(first_name, prenom, '') AS first_name,
            COALESCE(last_name,  nom,    '') AS last_name,
            role, client_number, avatar_url, kyc_status, status
     FROM users WHERE id = :id LIMIT 1",
    ['id' => $pending['user_id']]
);
if (!$user)
    json_response(['success' => false, 'error' => 'Utilisateur introuvable.'], 500);

Session::loginUser($user);
Session::clearPending2FA();

$redirect = GTB_BASE_URL . (($user['role'] ?? 'user') === 'admin'
    ? '/admin/index.php'
    : '/dashboard/index.php');

json_response(['success' => true, 'redirect' => $redirect]);
