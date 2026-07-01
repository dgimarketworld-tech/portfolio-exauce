<?php
ob_start();
/**
 * /authentification/api/signup.php
 * Inscription d'un nouveau client
 * Reçoit JSON ou POST : prenom, nom, email, phone, birthday, pays,
 *   password, plan, cgu_accepted, csrf_token
 */

require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/security.php';
require_once __DIR__ . '/../../backend/helpers.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Méthode non autorisée', 405);
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];
if (empty($data)) $data = $_POST;

// CSRF
if (!Security::csrfCheck($data['csrf_token'] ?? '')) {
    json_error('Jeton CSRF invalide.', 403);
}

$prenom       = trim($data['prenom']        ?? '');
$nom          = trim($data['nom']          ?? '');
$email        = strtolower(trim($data['email'] ?? ''));
$phone        = trim($data['phone']        ?? '');
$birthday     = $data['birthday']          ?? null;
$pays         = strtoupper(trim($data['pays'] ?? ''));
$password     = (string)($data['password'] ?? '');
$document_type = trim($data['document_type'] ?? '');
$plan         = in_array($data['plan'] ?? '', ['standard','premium','business'], true) ? $data['plan'] : 'standard';
$cguAccepted  = !empty($data['cgu_accepted']);

$errors = [];
if (mb_strlen($prenom) < 2)                              $errors['prenom']        = 'Prénom requis (2 caractères minimum).';
if (mb_strlen($nom) < 2)                                 $errors['nom']           = 'Nom requis.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors['email']         = 'Email invalide.';
if (!preg_match('/^\+?[0-9 ]{8,20}$/', $phone))         $errors['phone']         = 'Numéro de téléphone invalide.';
if ($birthday && !DateTime::createFromFormat('Y-m-d', $birthday)) $errors['birthday'] = 'Date de naissance invalide.';
if (strlen($password) < 8)                               $errors['password']      = 'Mot de passe : 8 caractères minimum.';
if (!$cguAccepted)                                       $errors['cgu']           = 'Vous devez accepter les CGU.';
$allowed_docs = ['cni_ue', 'passport', 'titre_sejour'];
if (!in_array($document_type, $allowed_docs, true))      $errors['document_type'] = 'Document accepté : CNI européenne, passeport ou titre de séjour.';

if (!empty($errors)) {
    json_response(['success' => false, 'error' => 'Veuillez corriger les champs en erreur.', 'fields' => $errors]);
    exit;
}

// Email déjà utilisé ?
if (DB::scalar("SELECT COUNT(*) FROM users WHERE email=:e", ['e' => $email]) > 0) {
    json_response(['success' => false, 'error' => 'Un compte existe déjà avec cet email.', 'fields' => ['email' => 'Email déjà utilisé.']]);
    exit;
}

// Détermine region et devise selon le pays
$northam_countries = ['US','CA','MX'];
$europe_countries  = ['FR','BE','CH','LU','MC','DE','ES','IT','PT','NL','AT','GB','IE','SE','NO','DK','FI','PL','CZ','HU','RO','BG','HR','SK','SI','EE','LV','LT','GR','CY','MT'];
if (in_array($pays, $northam_countries, true)) {
    $region = 'northam';
    $devise = 'USD';
} elseif (in_array($pays, $europe_countries, true)) {
    $region = 'europe';
    $devise = 'EUR';
} else {
    $region = 'latam';
    $devise = 'XOF';
}

// Création user + compte courant
$newUserId = null;
try {
    DB::transaction(function() use ($email, $password, $prenom, $nom, $phone, $birthday, $pays, $plan, $document_type, $region, $devise, &$newUserId) {
        $clientNum = generate_client_number();
        $hash = Security::hashPassword($password);
        $newUserId = DB::insertInto('users', [
            'email'             => $email,
            'password_hash'     => $hash,
            'prenom'            => $prenom,
            'nom'               => $nom,
            'first_name'        => $prenom,
            'last_name'         => $nom,
            'telephone'         => $phone,
            'birthday'          => $birthday ?: null,
            'pays'              => $pays ?: null,
            'region'            => $region,
            'plan'              => $plan,
            'client_number'     => $clientNum,
            'role'              => 'user',
            'is_active'         => 1,
            'status'            => 'active',
            'kyc_status'        => 'pending',
            'kyc_document_type' => $document_type,
            'two_fa_enabled'    => 1,
            'devise'            => $devise,
            'langue'            => 'fr',
            'language'          => 'fr',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $numeroCompte = 'GTB-' . ($pays ?: 'XX') . '-' . str_pad((string)$newUserId, 6, '0', STR_PAD_LEFT) . strtoupper(bin2hex(random_bytes(2)));
        DB::insertInto('comptes', [
            'user_id'   => $newUserId,
            'numero'    => $numeroCompte,
            'type'      => $plan === 'business' ? 'business' : 'courant',
            'solde'     => 0.00,
            'devise'    => $devise,
            'statut'    => 'actif',
            'ouvert_le' => date('Y-m-d H:i:s'),
        ]);
    });
    // notify hors transaction : un échec de notification ne doit pas rollback le compte
    if ($newUserId) {
        notify($newUserId, 'Bienvenue chez GTB', "Votre compte a été créé avec succès. Bienvenue $prenom !", 'success');
    }
} catch (Throwable $e) {
    error_log('Signup error: ' . $e->getMessage());
    json_error('Erreur lors de la création. Réessayez.', 500);
}

// Répondre immédiatement au navigateur, puis envoyer les mails
$json_out = json_encode(['success' => true, 'message' => 'Compte créé avec succès. Vous pouvez vous connecter.', 'email' => $email], JSON_UNESCAPED_UNICODE);
http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
header('Content-Length: ' . strlen($json_out));
header('Connection: close');
echo $json_out;
if (ob_get_level()) ob_end_flush();
flush();
if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

// Mails envoyés après la réponse (non bloquants pour le client)
ignore_user_abort(true);

$html_welcome = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'><h2 style='color:#1a3c5e'>Bienvenue chez Global Trust Bank, {$prenom} !</h2><p style='color:#374151'>Votre compte a été créé avec succès. Vous pouvez dès maintenant vous connecter à votre espace client.</p><a href='" . GTB_BASE_URL . "/authentification/login.php' style='display:inline-block;margin:20px 0;padding:12px 24px;background:#D4AF37;color:#fff;text-decoration:none;border-radius:6px;font-weight:600'>Accéder à mon espace</a><p style='color:#6b7280;font-size:13px'>Email : {$email}</p><hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'><p style='color:#9ca3af;font-size:12px'>Global Trust Bank — La banque d'un monde qui change</p></div>";
send_email($email, "$prenom $nom", 'Bienvenue chez Global Trust Bank', $html_welcome);

$html_admin = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'><h2 style='color:#1a3c5e'>Nouveau client inscrit</h2><table style='width:100%;border-collapse:collapse;color:#374151'><tr><td style='padding:6px 0;font-weight:600'>Nom</td><td>{$prenom} {$nom}</td></tr><tr><td style='padding:6px 0;font-weight:600'>Email</td><td>{$email}</td></tr><tr><td style='padding:6px 0;font-weight:600'>Plan</td><td>" . strtoupper($plan) . "</td></tr><tr><td style='padding:6px 0;font-weight:600'>Date</td><td>" . date('d/m/Y H:i') . "</td></tr></table><a href='" . GTB_BASE_URL . "/admin/utilisateurs/index.php' style='display:inline-block;margin:20px 0;padding:12px 24px;background:#1a3c5e;color:#fff;text-decoration:none;border-radius:6px;font-weight:600'>Voir le client</a><p style='color:#9ca3af;font-size:12px'>Global Trust Bank — Notification automatique</p></div>";
send_email(MAIL_SUPPORT, 'Admin GTB', "Nouveau client : {$prenom} {$nom}", $html_admin);
