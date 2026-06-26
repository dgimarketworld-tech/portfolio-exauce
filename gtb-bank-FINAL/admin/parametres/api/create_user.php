<?php
require_once __DIR__ . '/../../../backend/admin_required.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Méthode non autorisée.', 405);

$data     = json_decode(file_get_contents('php://input'), true) ?: [];
$civility = in_array($data['civility'] ?? '', ['M.', 'Mme']) ? $data['civility'] : null;
$first    = trim($data['first_name'] ?? '');
$last     = trim($data['last_name']  ?? '');
$email    = strtolower(trim($data['email'] ?? ''));
$tel      = trim($data['telephone'] ?? '');
$pwd      = trim($data['password']  ?? '');
$plan     = in_array($data['plan'] ?? '', ['standard','premium','business']) ? $data['plan'] : 'standard';
$devise   = strtoupper(trim($data['devise'] ?? 'EUR'));
$langue   = in_array($data['langue'] ?? '', ['fr','en','es','de']) ? $data['langue'] : 'fr';
$with_card = !empty($data['with_card']);

if (!$first || !$last)          json_error('Prénom et nom requis.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email invalide.');
if (strlen($pwd) < 8)           json_error('Mot de passe : 8 caractères minimum.');

if (DB::scalar("SELECT 1 FROM users WHERE email=:e", ['e'=>$email])) {
    json_error('Cet email est déjà utilisé.');
}

require_once __DIR__ . '/../../../backend/security.php';
require_once __DIR__ . '/../../../backend/iban.php';

try {
    DB::begin();

    $clientNum = generate_client_number();
    $userId = DB::insertInto('users', [
        'civility'    => $civility,
        'first_name'  => $first,
        'last_name'   => $last,
        'prenom'      => $first,
        'nom'         => $last,
        'email'       => $email,
        'telephone'   => $tel ?: null,
        'password_hash' => Security::hashPassword($pwd),
        'client_number' => $clientNum,
        'plan'        => $plan,
        'role'        => 'user',
        'status'      => 'active',
        'is_active'   => 1,
        'kyc_status'  => 'pending',
        'langue'      => $langue,
        'devise'      => $devise,
        'email_verified' => 1,
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    // Compte bancaire
    $accountNum = strtoupper(substr(md5($userId . microtime()), 0, 11));
    $iban = IBAN::generateFR($accountNum);
    $bic  = GTB_BIC;
    $compteType = ($plan === 'business') ? 'business' : 'courant';
    $compteId = DB::insertInto('comptes', [
        'user_id'     => $userId,
        'numero'      => $accountNum,
        'iban'        => $iban,
        'bic'         => $bic,
        'solde'       => 0.00,
        'devise'      => $devise,
        'type'        => $compteType,
        'statut'      => 'actif',
        'plafond_retrait'   => 10000.00,
        'plafond_virement'  => 50000.00,
        'plafond_paiement'  => 5000.00,
        'decouvert_autorise'=> 500.00,
    ]);

    // Carte si demandée
    if ($with_card) {
        $cardNum = implode(' ', str_split(str_pad((string)random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT), 4));
        $cvv     = str_pad((string)random_int(0, 999), 3, '0', STR_PAD_LEFT);
        DB::insertInto('cartes', [
            'compte_id'        => $compteId,
            'numero_masque'    => '**** **** **** ' . substr(preg_replace('/\s+/','',$cardNum), -4),
            'type'             => ($plan === 'premium' ? 'gold' : ($plan === 'business' ? 'business' : 'standard')),
            'reseau'           => 'mastercard',
            'cvv'              => $cvv,
            'expire_le'        => date('Y-m-d', strtotime('+3 years')),
            'statut'           => 'active',
            'plafond'          => 3000.00,
            'paiement_en_ligne'=> 1,
            'paiement_etranger'=> 0,
            'cree_le'          => date('Y-m-d H:i:s'),
        ]);
    }

    DB::commit();

    Security::audit('admin_create_user', 'users', $userId, [
        'email'  => $email,
        'plan'   => $plan,
        'admin'  => Session::adminId(),
    ]);

    json_response([
        'success'       => true,
        'message'       => "Client {$first} {$last} créé avec succès.",
        'user_id'       => $userId,
        'client_number' => $clientNum,
        'iban'          => IBAN::format($iban),
        'bic'           => $bic,
        'temp_password' => $pwd,
    ]);

} catch (Throwable $e) {
    DB::rollback();
    error_log('[create_user] ' . $e->getMessage());
    json_error('Erreur interne : ' . $e->getMessage(), 500);
}
