<?php
/**
 * GTB SMS Banking — Webhook entrant (compatible Twilio)
 * URL à configurer dans Twilio : https://globaltrust-b.com/dashboard/sms-banking/api/webhook.php
 * Méthode : POST
 */
require_once __DIR__ . '/../../../backend/config.php';
require_once __DIR__ . '/../../../backend/db.php';
require_once __DIR__ . '/../../../backend/helpers.php';

header('Content-Type: text/xml; charset=utf-8');

function sms_reply(string $msg): string {
    return '<?xml version="1.0" encoding="UTF-8"?><Response><Message>' . htmlspecialchars($msg) . '</Message></Response>';
}

// ── Validation signature Twilio (désactiver en dev) ──────────────
if (defined('TWILIO_AUTH_TOKEN') && GTB_ENV === 'production') {
    $sig  = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
    $url  = GTB_BASE_URL . '/dashboard/sms-banking/api/webhook.php';
    $data = $_POST;
    ksort($data);
    $str  = $url . implode('', array_map(fn($k,$v)=>$k.$v, array_keys($data), $data));
    $expected = base64_encode(hash_hmac('sha1', $str, TWILIO_AUTH_TOKEN, true));
    if (!hash_equals($expected, $sig)) {
        http_response_code(403);
        exit(sms_reply('Accès refusé.'));
    }
}

// ── Lecture paramètres Twilio ────────────────────────────────────
$from    = preg_replace('/\D/', '', $_POST['From'] ?? '');   // Ex: 33612345678
$body    = strtoupper(trim($_POST['Body'] ?? ''));

if (!$from || !$body) {
    exit(sms_reply('Commande invalide. Envoyez AIDE pour la liste des commandes.'));
}

// ── Trouver l'utilisateur et sa config SMS ───────────────────────
$sms = DB::one("SELECT s.*, u.id AS user_id, u.first_name, u.last_name, u.email
                FROM sms_banking s
                JOIN users u ON u.id = s.user_id
                WHERE s.telephone = :tel AND s.is_active = 1", ['tel' => $from]);

if (!$sms) {
    exit(sms_reply('Numéro non reconnu ou service SMS Banking inactif. Connectez-vous sur globaltrust-b.com pour activer le service.'));
}

$userId = (int)$sms['user_id'];

// ── Logger la commande reçue ─────────────────────────────────────
function log_sms(int $userId, string $direction, string $telephone, string $contenu, string $commande, string $statut): void {
    DB::insertInto('sms_banking_logs', [
        'user_id'   => $userId,
        'direction' => $direction,
        'telephone' => $telephone,
        'contenu'   => substr($contenu, 0, 500),
        'commande'  => $commande,
        'statut'    => $statut,
    ]);
}

log_sms($userId, 'IN', $from, $body, '', 'pending');

// ── Parser la commande et le PIN ─────────────────────────────────
$parts = preg_split('/\s+/', $body);
$cmd   = $parts[0] ?? '';

// Commandes sans PIN
if ($cmd === 'AIDE' || $cmd === 'HELP') {
    $rep = "GTB SMS Banking — Commandes disponibles :\n"
         . "SOLDE [PIN] — Consulter le solde\n"
         . "HIST [PIN] — 5 dernières opérations\n"
         . "COMPTE [PIN] — Vos numéros de compte\n"
         . "VIRER [PIN] [IBAN] [MONTANT] — Effectuer un virement\n"
         . "BLOQUER [PIN] [4 derniers chiffres carte] — Bloquer une carte\n"
         . "STOP — Désactiver les alertes\n"
         . "START — Réactiver les alertes\n"
         . "AIDE — Ce message\n"
         . "GTB · globaltrust-b.com";
    log_sms($userId, 'OUT', $from, $rep, 'AIDE', 'success');
    exit(sms_reply($rep));
}

if ($cmd === 'STOP') {
    DB::update("UPDATE sms_banking SET alert_debit=0,alert_credit=0 WHERE user_id=:id", ['id'=>$userId]);
    $rep = "Alertes SMS désactivées. Répondez START pour les réactiver.";
    log_sms($userId, 'OUT', $from, $rep, 'STOP', 'success');
    exit(sms_reply($rep));
}

if ($cmd === 'START') {
    DB::update("UPDATE sms_banking SET alert_debit=1,alert_credit=1 WHERE user_id=:id", ['id'=>$userId]);
    $rep = "Alertes SMS réactivées. Vous recevrez désormais les notifications de débit et crédit.";
    log_sms($userId, 'OUT', $from, $rep, 'START', 'success');
    exit(sms_reply($rep));
}

// ── Toutes les autres commandes nécessitent un PIN ───────────────
$pin = $parts[1] ?? '';
if ($pin !== $sms['pin']) {
    $rep = "PIN incorrect. Commande refusée. Connectez-vous sur globaltrust-b.com pour modifier votre PIN SMS.";
    log_sms($userId, 'OUT', $from, $rep, $cmd, 'error');
    exit(sms_reply($rep));
}

// ── SOLDE ────────────────────────────────────────────────────────
if ($cmd === 'SOLDE') {
    $comptes = DB::all("SELECT numero_compte, type_compte, solde FROM comptes WHERE user_id=:id AND statut='actif' ORDER BY type_compte", ['id'=>$userId]);
    if (!$comptes) {
        $rep = "Aucun compte actif trouvé.";
    } else {
        $rep = "GTB — Soldes au " . date('d/m/Y H:i') . "\n";
        foreach ($comptes as $c) {
            $rep .= strtoupper($c['type_compte']) . ' : ' . number_format($c['solde'],2,',',' ') . " €\n";
        }
        $rep .= "GTB · globaltrust-b.com";
    }
    log_sms($userId, 'OUT', $from, $rep, 'SOLDE', 'success');
    exit(sms_reply($rep));
}

// ── HIST ─────────────────────────────────────────────────────────
if ($cmd === 'HIST') {
    $txs = DB::all("SELECT t.type, t.montant, t.libelle, t.date_transaction
                    FROM transactions t
                    JOIN comptes c ON c.id = t.compte_id
                    WHERE c.user_id = :id
                    ORDER BY t.date_transaction DESC LIMIT 5", ['id'=>$userId]);
    if (!$txs) {
        $rep = "Aucune transaction récente.";
    } else {
        $rep = "GTB — 5 dernières opérations :\n";
        foreach ($txs as $tx) {
            $sign = $tx['type'] === 'credit' ? '+' : '-';
            $rep .= date('d/m', strtotime($tx['date_transaction'])) . ' '
                  . $sign . number_format(abs($tx['montant']),2,',',' ') . '€'
                  . ' ' . mb_substr($tx['libelle']??'',0,20) . "\n";
        }
    }
    log_sms($userId, 'OUT', $from, $rep, 'HIST', 'success');
    exit(sms_reply($rep));
}

// ── COMPTE ───────────────────────────────────────────────────────
if ($cmd === 'COMPTE') {
    $comptes = DB::all("SELECT numero_compte, type_compte FROM comptes WHERE user_id=:id AND statut='actif'", ['id'=>$userId]);
    if (!$comptes) {
        $rep = "Aucun compte actif.";
    } else {
        $rep = "GTB — Vos comptes :\n";
        foreach ($comptes as $c) {
            $rep .= strtoupper($c['type_compte']) . ' : ' . $c['numero_compte'] . "\n";
        }
    }
    log_sms($userId, 'OUT', $from, $rep, 'COMPTE', 'success');
    exit(sms_reply($rep));
}

// ── VIRER [PIN] [IBAN] [MONTANT] ────────────────────────────────
if ($cmd === 'VIRER') {
    // parts: [0]=VIRER [1]=PIN [2]=IBAN [3]=MONTANT
    $iban    = preg_replace('/\s/','', strtoupper($parts[2]??''));
    $montant = (float)str_replace(',','.',$parts[3]??'0');

    if (!$iban || $montant <= 0) {
        $rep = "Format : VIRER [PIN] [IBAN] [MONTANT]\nEx: VIRER 1234 FR761234... 100.00";
        log_sms($userId, 'OUT', $from, $rep, 'VIRER', 'error');
        exit(sms_reply($rep));
    }

    if ($montant > 500) {
        $rep = "Plafond SMS Banking : 500€ par virement. Pour des montants supérieurs, connectez-vous sur globaltrust-b.com.";
        log_sms($userId, 'OUT', $from, $rep, 'VIRER', 'error');
        exit(sms_reply($rep));
    }

    // Trouver le compte principal de l'utilisateur
    $compte = DB::one("SELECT * FROM comptes WHERE user_id=:id AND type_compte='courant' AND statut='actif' LIMIT 1", ['id'=>$userId]);
    if (!$compte || $compte['solde'] < $montant) {
        $rep = "Solde insuffisant ou aucun compte courant actif.";
        log_sms($userId, 'OUT', $from, $rep, 'VIRER', 'error');
        exit(sms_reply($rep));
    }

    // Trouver le bénéficiaire
    $benef = DB::one("SELECT * FROM beneficiaires WHERE user_id=:id AND iban=:iban", ['id'=>$userId,'iban'=>$iban]);
    if (!$benef) {
        $rep = "IBAN non trouvé parmi vos bénéficiaires enregistrés. Ajoutez-le d'abord sur globaltrust-b.com.";
        log_sms($userId, 'OUT', $from, $rep, 'VIRER', 'error');
        exit(sms_reply($rep));
    }

    // Débiter
    DB::update("UPDATE comptes SET solde = solde - :m WHERE id = :id", ['m'=>$montant,'id'=>$compte['id']]);
    $ref = 'SMS-'.strtoupper(substr(bin2hex(random_bytes(4)),0,8));
    DB::insertInto('transactions',[
        'compte_id'        => $compte['id'],
        'type'             => 'debit',
        'montant'          => -$montant,
        'libelle'          => 'Virement SMS vers '.($benef['nom']??$iban),
        'reference'        => $ref,
        'statut'           => 'effectue',
        'date_transaction' => date('Y-m-d H:i:s'),
    ]);

    $rep = "Virement effectué ✓\nMontant : " . number_format($montant,2,',',' ') . "€\nVers : " . ($benef['nom']??$iban) . "\nRéf : $ref\nGTB · " . date('d/m/Y H:i');
    log_sms($userId, 'OUT', $from, $rep, 'VIRER', 'success');
    exit(sms_reply($rep));
}

// ── BLOQUER [PIN] [4 derniers chiffres] ─────────────────────────
if ($cmd === 'BLOQUER') {
    $last4 = preg_replace('/\D/','',$parts[2]??'');
    if (strlen($last4) !== 4) {
        $rep = "Format : BLOQUER [PIN] [4 derniers chiffres de votre carte]\nEx: BLOQUER 1234 4512";
        log_sms($userId, 'OUT', $from, $rep, 'BLOQUER', 'error');
        exit(sms_reply($rep));
    }
    $carte = DB::one("SELECT id, numero_carte FROM cartes WHERE user_id=:id AND RIGHT(numero_carte,4)=:l4 AND statut='active'", ['id'=>$userId,'l4'=>$last4]);
    if (!$carte) {
        $rep = "Aucune carte active trouvée avec ces 4 derniers chiffres.";
        log_sms($userId, 'OUT', $from, $rep, 'BLOQUER', 'error');
        exit(sms_reply($rep));
    }
    DB::update("UPDATE cartes SET statut='bloquee' WHERE id=:id", ['id'=>$carte['id']]);
    DB::insertInto('notifications',['user_id'=>$userId,'type'=>'alerte','titre'=>'Carte bloquée via SMS','message'=>'Votre carte se terminant par '.$last4.' a été bloquée suite à votre demande SMS.']);

    $rep = "Carte ****$last4 bloquée avec succès.\nPour la débloquer, connectez-vous sur globaltrust-b.com.";
    log_sms($userId, 'OUT', $from, $rep, 'BLOQUER', 'success');
    exit(sms_reply($rep));
}

// ── Commande inconnue ────────────────────────────────────────────
$rep = "Commande '$cmd' non reconnue. Envoyez AIDE pour la liste des commandes disponibles.";
log_sms($userId, 'OUT', $from, $rep, $cmd, 'error');
exit(sms_reply($rep));
