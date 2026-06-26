<?php
require_once __DIR__ . '/../../../backend/admin_required.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Méthode non autorisée', 405);

$data      = json_decode(file_get_contents('php://input'), true) ?: [];
$action    = trim($data['action']    ?? '');
$clientId  = (int)($data['client_id'] ?? 0);
$formData  = $data['form_data']  ?? [];
$notifTitle= trim($data['notif_title'] ?? '');
$notifMsg  = trim($data['notif_msg']   ?? '');
$sendEmail = !empty($data['send_email']);
$backdated = $data['backdated_at'] ?? null;
$adminId   = Session::adminId();

if (!$clientId || !$action) json_error('Paramètres manquants.');

$user = DB::one("SELECT id, first_name, last_name, email, status, access_blocked FROM users WHERE id=:id AND role='user'", ['id'=>$clientId]);
if (!$user) json_error('Client introuvable.');

$compte = DB::one("SELECT id, solde, iban, bic, devise FROM comptes WHERE user_id=:id AND statut='actif' ORDER BY id ASC LIMIT 1", ['id'=>$clientId]);
$carte  = DB::one("SELECT c.id, c.statut, c.plafond, c.paiement_en_ligne, c.paiement_etranger FROM cartes c JOIN comptes co ON co.id=c.compte_id WHERE co.user_id=:id ORDER BY c.id DESC LIMIT 1", ['id'=>$clientId]);

$message = 'Action effectuée.';
$logData = $formData;

try { DB::begin();

switch ($action) {

  // ── VIREMENTS ──
  case 'virement_entrant':
    $montant = (float)($formData['montant'] ?? 0);
    if ($montant <= 0) json_error('Montant invalide.');
    $source  = trim($formData['source'] ?? 'Banque partenaire');
    $ref     = trim($formData['ref']    ?? generate_reference('VIR'));
    $motif   = trim($formData['motif']  ?? 'Virement entrant');
    $devise  = $formData['devise'] ?? 'EUR';
    $dateOp  = $backdated ? date('Y-m-d H:i:s', strtotime($backdated)) : date('Y-m-d H:i:s');
    DB::run("UPDATE comptes SET solde=solde+:m WHERE id=:id", ['m'=>$montant,'id'=>$compte['id']]);
    DB::insertInto('transactions', [
        'compte_id'   => $compte['id'],
        'type'        => 'virement_in',
        'montant'     => $montant,
        'solde_apres' => (float)$compte['solde'] + $montant,
        'description' => $motif,
        'reference'   => $ref,
        'statut'      => 'terminee',
        'cree_le'     => $dateOp,
        'backdated_at'=> $backdated ? $dateOp : null,
        'certification_status' => 'validated',
        'certification_pct'    => 100,
    ]);
    $message = "Virement entrant de ".number_format($montant,2,',',' ')." {$devise} crédité avec succès.";
  break;

  case 'virement_sortant':
    $montant = (float)($formData['montant'] ?? 0);
    if ($montant <= 0) json_error('Montant invalide.');
    $dest   = trim($formData['dest']    ?? 'Bénéficiaire');
    $ref    = trim($formData['ref']     ?? generate_reference('VIR'));
    $motif  = trim($formData['motif']   ?? 'Virement sortant');
    $devise = $formData['devise'] ?? 'EUR';
    $dateOp = $backdated ? date('Y-m-d H:i:s', strtotime($backdated)) : date('Y-m-d H:i:s');
    DB::run("UPDATE comptes SET solde=solde-:m WHERE id=:id", ['m'=>$montant,'id'=>$compte['id']]);
    DB::insertInto('transactions', [
        'compte_id'   => $compte['id'],
        'type'        => 'virement_out',
        'montant'     => $montant,
        'solde_apres' => (float)$compte['solde'] - $montant,
        'description' => $motif,
        'reference'   => $ref,
        'statut'      => 'terminee',
        'cree_le'     => $dateOp,
        'backdated_at'=> $backdated ? $dateOp : null,
        'certification_status' => 'validated',
        'certification_pct'    => 100,
    ]);
    $message = "Virement sortant de ".number_format($montant,2,',',' ')." {$devise} débité avec succès.";
  break;

  case 'remboursement':
    $montant = (float)($formData['montant'] ?? 0);
    if ($montant <= 0) json_error('Montant invalide.');
    $motif   = trim($formData['motif'] ?? 'Remboursement');
    $ref     = trim($formData['ref']   ?? generate_reference('RMB'));
    $dateOp  = $backdated ? date('Y-m-d H:i:s', strtotime($backdated)) : date('Y-m-d H:i:s');
    DB::run("UPDATE comptes SET solde=solde+:m WHERE id=:id", ['m'=>$montant,'id'=>$compte['id']]);
    DB::insertInto('transactions', [
        'compte_id'   => $compte['id'],
        'type'        => 'depot',
        'montant'     => $montant,
        'solde_apres' => (float)$compte['solde'] + $montant,
        'description' => 'Remboursement — '.$motif,
        'reference'   => $ref,
        'statut'      => 'terminee',
        'cree_le'     => $dateOp,
        'certification_status' => 'validated',
        'certification_pct'    => 100,
    ]);
    $message = "Remboursement de ".number_format($montant,2,',',' ')." crédité.";
  break;

  // ── BARRE CERTIFICATION ──
  case 'cert_bloquer':
    $pct = min(99, max(0, (int)($formData['pct'] ?? 50)));
    $msg = trim($formData['cert-msg'] ?? 'Vérification en cours...');
    DB::run("UPDATE transactions SET certification_status='frozen', certification_pct=:p, certification_message=:m WHERE compte_id=:c AND certification_status IN('idle','running') ORDER BY id DESC LIMIT 1",
        ['p'=>$pct,'m'=>$msg,'c'=>$compte['id']]);
    $message = "Barre de certification gelée à {$pct}%.";
  break;

  case 'cert_debloquer':
    DB::run("UPDATE transactions SET certification_status='running' WHERE compte_id=:c AND certification_status='frozen' ORDER BY id DESC LIMIT 1", ['c'=>$compte['id']]);
    $message = "Barre de certification débloquée.";
  break;

  case 'cert_reset':
    DB::run("UPDATE transactions SET certification_status='idle', certification_pct=0 WHERE compte_id=:c ORDER BY id DESC LIMIT 1", ['c'=>$compte['id']]);
    $message = "Barre de certification remise à 0%.";
  break;

  case 'cert_forcer_100':
  case 'cert_valider':
    DB::run("UPDATE transactions SET certification_status='validated', certification_pct=100, statut='completed' WHERE compte_id=:c AND certification_status IN('idle','running','frozen','blocked') ORDER BY id DESC LIMIT 1", ['c'=>$compte['id']]);
    $message = "Virement validé manuellement — barre à 100%.";
  break;

  case 'cert_geler':
    DB::run("UPDATE transactions SET certification_status='frozen' WHERE compte_id=:c AND certification_status='running' ORDER BY id DESC LIMIT 1", ['c'=>$compte['id']]);
    $message = "Barre de certification gelée à sa position actuelle.";
  break;

  case 'cert_vitesse':
    $v = in_array($formData['vitesse']??'normal',['slow','normal','fast']) ? $formData['vitesse'] : 'normal';
    DB::run("UPDATE transactions SET certification_speed=:v WHERE compte_id=:c ORDER BY id DESC LIMIT 1", ['v'=>$v,'c'=>$compte['id']]);
    $message = "Vitesse de la barre mise à : {$v}.";
  break;

  case 'cert_message':
    $msg = trim($formData['cert-msg'] ?? '');
    if (!$msg) json_error('Message requis.');
    DB::run("UPDATE transactions SET certification_message=:m WHERE compte_id=:c ORDER BY id DESC LIMIT 1", ['m'=>$msg,'c'=>$compte['id']]);
    $message = "Message de blocage mis à jour.";
  break;

  case 'cert_rejeter':
    DB::run("UPDATE transactions SET certification_status='rejected', certification_pct=0, statut='failed' WHERE compte_id=:c AND certification_status IN('idle','running','frozen','blocked') ORDER BY id DESC LIMIT 1", ['c'=>$compte['id']]);
    $message = "Virement rejeté — barre remise à 0%.";
  break;

  case 'stop_virement_pct':
    $pct = min(100, max(0, (int)($formData['pct'] ?? 0)));
    DB::run("UPDATE users SET transfer_stop_pct=:p WHERE id=:id", ['p'=>$pct,'id'=>$clientId]);
    $message = "Seuil d'arrêt virement fixé à {$pct}%.";
  break;

  // ── GESTION COMPTE ──
  case 'bloquer_acces':
    $motif    = trim($formData['motif']       ?? 'Blocage administratif');
    $type     = $formData['block-type']       ?? 'permanent';
    $until    = trim($formData['block-until'] ?? '');
    DB::run("UPDATE users SET access_blocked=1, access_block_reason=:r, access_block_type=:t, access_block_until=:u WHERE id=:id",
        ['r'=>$motif,'t'=>$type,'u'=>($until?:null),'id'=>$clientId]);
    $message = "Accès client bloqué ({$type}).";
  break;

  case 'debloquer_acces':
    DB::run("UPDATE users SET access_blocked=0, access_block_reason=NULL, access_block_until=NULL WHERE id=:id", ['id'=>$clientId]);
    $message = "Accès client rétabli.";
  break;

  case 'suspendre_compte':
    DB::run("UPDATE users SET status='suspended' WHERE id=:id", ['id'=>$clientId]);
    $message = "Compte suspendu.";
  break;

  case 'fermer_compte':
    DB::run("UPDATE users SET status='closed' WHERE id=:id", ['id'=>$clientId]);
    DB::run("UPDATE comptes SET statut='cloture' WHERE user_id=:id", ['id'=>$clientId]);
    $message = "Compte fermé.";
  break;

  case 'modifier_solde':
    $montant = (float)($formData['montant'] ?? 0);
    DB::run("UPDATE comptes SET solde=:m WHERE id=:id", ['m'=>$montant,'id'=>$compte['id']]);
    $message = "Solde modifié à ".number_format($montant,2,',',' ').".";
  break;

  case 'modifier_decouvert':
    $montant = (float)($formData['montant'] ?? 500);
    DB::run("UPDATE comptes SET decouvert_autorise=:m WHERE id=:id", ['m'=>$montant,'id'=>$compte['id']]);
    $message = "Découvert autorisé modifié à ".number_format($montant,2,',',' ').".";
  break;

  case 'changer_type_compte':
    $type = in_array($formData['type-compte']??'',['courant','epargne','business']) ? $formData['type-compte'] : 'courant';
    $plan_map = ['courant'=>'standard','epargne'=>'standard','business'=>'business'];
    DB::run("UPDATE comptes SET type=:t WHERE id=:id", ['t'=>$type,'id'=>$compte['id']]);
    DB::run("UPDATE users SET plan=:t WHERE id=:id", ['t'=>$plan_map[$type],'id'=>$clientId]);
    $message = "Type de compte changé en : {$type}.";
  break;

  // ── CARTE ──
  case 'bloquer_carte':
    if (!$carte) json_error('Aucune carte trouvée.');
    DB::run("UPDATE cartes SET statut='bloquee' WHERE id=:id", ['id'=>$carte['id']]);
    $message = "Carte bloquée.";
  break;

  case 'debloquer_carte':
    if (!$carte) json_error('Aucune carte trouvée.');
    DB::run("UPDATE cartes SET statut='active' WHERE id=:id", ['id'=>$carte['id']]);
    $message = "Carte débloquée.";
  break;

  case 'renouveler_carte':
    if (!$carte) json_error('Aucune carte trouvée.');
    DB::run("UPDATE cartes SET statut='verification', expire_le=DATE_ADD(NOW(), INTERVAL 3 YEAR) WHERE id=:id", ['id'=>$carte['id']]);
    $message = "Renouvellement de carte lancé.";
  break;

  case 'modifier_infos_carte':
    if (!$carte) json_error('Aucune carte trouvée.');
    $updates = [];
    if (!empty($formData['card-num'])) $updates['numero'] = preg_replace('/\s+/','',$formData['card-num']);
    if (!empty($formData['cvv']))      $updates['cvv']    = $formData['cvv'];
    if (!empty($formData['expire']))   $updates['expire_le'] = $formData['expire'].'-01';
    if ($updates) {
        $sets = implode(',', array_map(fn($k) => "`{$k}`=:{$k}", array_keys($updates)));
        DB::run("UPDATE cartes SET {$sets} WHERE id=:id", array_merge($updates,['id'=>$carte['id']]));
    }
    $message = "Informations de carte mises à jour.";
  break;

  case 'modifier_plafond_carte':
    if (!$carte) json_error('Aucune carte trouvée.');
    $plafond = (float)($formData['montant'] ?? 3000);
    DB::run("UPDATE cartes SET plafond=:p WHERE id=:id", ['p'=>$plafond,'id'=>$carte['id']]);
    $message = "Plafond carte modifié à ".number_format($plafond,2,',',' ').".";
  break;

  case 'toggle_paiement_en_ligne':
    if (!$carte) json_error('Aucune carte trouvée.');
    $new = ($carte['paiement_en_ligne'] ? 0 : 1);
    DB::run("UPDATE cartes SET paiement_en_ligne=:v WHERE id=:id", ['v'=>$new,'id'=>$carte['id']]);
    $message = "Paiements en ligne ".($new?'activés':'désactivés').".";
  break;

  case 'toggle_paiement_etranger':
    if (!$carte) json_error('Aucune carte trouvée.');
    $new = ($carte['paiement_etranger'] ? 0 : 1);
    DB::run("UPDATE cartes SET paiement_etranger=:v WHERE id=:id", ['v'=>$new,'id'=>$carte['id']]);
    $message = "Paiements à l'étranger ".($new?'activés':'désactivés').".";
  break;

  // ── COORDONNÉES BANCAIRES ──
  case 'modifier_iban_bic':
    $iban = preg_replace('/\s+/', '', strtoupper($formData['iban'] ?? ''));
    $bic  = strtoupper(trim($formData['bic'] ?? ''));
    if (!$iban || !$bic) json_error('IBAN et BIC requis.');
    DB::run("UPDATE comptes SET iban=:i, bic=:b WHERE id=:c", ['i'=>$iban,'b'=>$bic,'c'=>$compte['id']]);
    $message = "IBAN et BIC mis à jour.";
  break;

  case 'modifier_rib':
    $rib = trim($formData['rib'] ?? '');
    if (!$rib) json_error('Numéro de compte requis.');
    DB::run("UPDATE comptes SET numero=:n WHERE id=:c", ['n'=>$rib,'c'=>$compte['id']]);
    $message = "RIB mis à jour.";
  break;

  // ── SÉCURITÉ ──
  case 'reset_password':
    $tmp = bin2hex(random_bytes(8));
    require_once __DIR__ . '/../../../backend/security.php';
    DB::run("UPDATE users SET password_hash=:h WHERE id=:id",
        ['h'=>Security::hashPassword($tmp),'id'=>$clientId]);
    $notifMsg = $notifMsg ?: "Votre mot de passe a été réinitialisé. Mot de passe temporaire : {$tmp}\nChangez-le à votre prochaine connexion.";
    $message  = "Mot de passe réinitialisé. Mot de passe temporaire : {$tmp}";
  break;

  case 'toggle_2fa':
    $cur = (int) DB::scalar("SELECT two_fa_enabled FROM users WHERE id=:id", ['id'=>$clientId]);
    DB::run("UPDATE users SET two_fa_enabled=:v WHERE id=:id", ['v'=>($cur?0:1),'id'=>$clientId]);
    $message = "2FA ".($cur?'désactivé':'activé').".";
  break;

  case 'forcer_deconnexion':
    DB::run("DELETE FROM sessions_actives WHERE user_id=:id", ['id'=>$clientId]);
    $message = "Toutes les sessions du client ont été révoquées.";
  break;

  case 'modifier_niveau_securite':
    $lvl = $formData['sec-level'] ?? 'standard';
    DB::run("UPDATE users SET plan=:l WHERE id=:id", ['l'=>$lvl,'id'=>$clientId]);
    $message = "Niveau de sécurité changé en : {$lvl}.";
  break;

  // ── KYC ──
  case 'valider_kyc':
    DB::run("UPDATE users SET kyc_status='verified' WHERE id=:id", ['id'=>$clientId]);
    $message = "KYC validé.";
  break;

  case 'refuser_kyc':
    DB::run("UPDATE users SET kyc_status='rejected' WHERE id=:id", ['id'=>$clientId]);
    $message = "KYC refusé.";
  break;

  case 'changer_statut_kyc':
    $s = $formData['kyc-status'] ?? 'pending';
    DB::run("UPDATE users SET kyc_status=:s WHERE id=:id", ['s'=>$s,'id'=>$clientId]);
    $message = "Statut KYC changé en : {$s}.";
  break;

  case 'demander_documents':
    $docs = trim($formData['docs'] ?? '');
    $message = "Documents demandés : {$docs}";
    $notifMsg = $notifMsg ?: "Documents requis : {$docs}\nMerci de les fournir depuis votre espace personnel.";
  break;

  // ── PLAFONDS ──
  case 'plafond_retrait':
    $v = (float)($formData['montant'] ?? 10000);
    DB::run("UPDATE comptes SET plafond_retrait=:v WHERE id=:c", ['v'=>$v,'c'=>$compte['id']]);
    $message = "Plafond de retrait modifié à ".number_format($v,2,',',' ').".";
  break;

  case 'plafond_virement':
    $v = (float)($formData['montant'] ?? 50000);
    DB::run("UPDATE comptes SET plafond_virement=:v WHERE id=:c", ['v'=>$v,'c'=>$compte['id']]);
    $message = "Plafond de virement modifié à ".number_format($v,2,',',' ').".";
  break;

  case 'plafond_paiement':
    $v = (float)($formData['montant'] ?? 5000);
    DB::run("UPDATE comptes SET plafond_paiement=:v WHERE id=:c", ['v'=>$v,'c'=>$compte['id']]);
    $message = "Plafond de paiement modifié à ".number_format($v,2,',',' ').".";
  break;

  // ── INTERFACE & COMMUNICATION ──
  case 'changer_couleur':
    $c = $formData['couleur'] ?? 'default';
    DB::run("UPDATE users SET interface_color=:c WHERE id=:id", ['c'=>$c,'id'=>$clientId]);
    $message = "Couleur de l'interface changée en : {$c}.";
  break;

  case 'changer_langue':
    $l = $formData['langue'] ?? 'fr';
    DB::run("UPDATE users SET langue=:l WHERE id=:id", ['l'=>$l,'id'=>$clientId]);
    $message = "Langue d'affichage changée en : {$l}.";
  break;

  case 'envoyer_notification':
    $t = trim($formData['notif-t'] ?? 'Notification');
    $m = trim($formData['notif-m'] ?? '');
    $tp= $formData['notif-type'] ?? 'info';
    if (!$m) json_error('Message requis.');
    notify($clientId, $t, $m, $tp);
    $message = "Notification envoyée.";
    $notifTitle = $t; $notifMsg = $m;
  break;

  case 'envoyer_message':
    $m = trim($formData['message'] ?? '');
    if (!$m) json_error('Message requis.');
    notify($clientId, 'Message de GTB Bank', $m, 'info');
    $notifMsg = $m;
    $message = "Message envoyé.";
  break;

  case 'toggle_alertes_email':
    $cur = (int) DB::scalar("SELECT two_fa_enabled FROM users WHERE id=:id", ['id'=>$clientId]);
    notify($clientId, 'Alertes email', ($cur ? 'Alertes email désactivées.' : 'Alertes email activées.'), 'info');
    $message = "Alertes email ".($cur?'désactivées':'activées').".";
  break;

  default:
    json_error('Action non reconnue : '.$action);
}

// Log de l'action admin
DB::insertInto('admin_actions', [
    'admin_id'    => $adminId,
    'user_id'     => $clientId,
    'action_type' => $action,
    'action_data' => json_encode($logData),
    'note'        => $message,
    'notif_sent'  => 0,
    'email_sent'  => 0,
    'created_at'  => date('Y-m-d H:i:s'),
]);

// Notification in-app si titre et message fournis
if ($notifTitle && $notifMsg) {
    notify($clientId, $notifTitle, $notifMsg, 'info');
    DB::run("UPDATE admin_actions SET notif_sent=1 WHERE id=LAST_INSERT_ID()");
}

// Email si demandé
if ($sendEmail && $notifMsg) {
    $name = trim(($user['first_name']??'').' '.($user['last_name']??'')) ?: 'Client';
    $subj = $notifTitle ?: 'Mise à jour de votre compte GTB';
    $html = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'>
      <h2 style='color:#1a3c5e'>Global Trust Bank</h2>
      <p>Bonjour <strong>{$name}</strong>,</p>
      <div style='background:#f3f4f6;padding:16px;border-radius:6px;margin:16px 0'>".nl2br(htmlspecialchars($notifMsg))."</div>
      <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'>
      <p style='color:#9ca3af;font-size:12px'>Global Trust Bank — La banque d'un monde qui change</p>
    </div>";
    send_email($user['email'], $name, $subj, $html);
    DB::run("UPDATE admin_actions SET email_sent=1 WHERE id=LAST_INSERT_ID()");
}

DB::commit();
json_response(['success'=>true,'message'=>$message]);

} catch (Throwable $e) {
    DB::rollback();
    error_log('[admin_action] '.$e->getMessage());
    json_error('Erreur interne : '.$e->getMessage(), 500);
}
