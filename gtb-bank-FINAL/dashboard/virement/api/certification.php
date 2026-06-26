<?php
require_once __DIR__ . '/../../../backend/auth_required.php';
$trxId = (int)($_GET['id'] ?? 0);
if (!$trxId) json_error('ID requis.');

$t = DB::one(
    "SELECT t.id, t.certification_pct, t.certification_status, t.certification_speed,
            t.certification_message, c.user_id
     FROM transactions t JOIN comptes c ON c.id=t.compte_id
     WHERE t.id=:id LIMIT 1",
    ['id'=>$trxId]
);
if (!$t || (int)$t['user_id'] !== Session::userId()) json_error('Non autorisé.', 403);

// Alerter l'admin si pas encore fait
if (in_array($t['certification_status'], ['idle','running'])) {
    DB::run("UPDATE transactions SET admin_alerted=1 WHERE id=:id AND admin_alerted=0", ['id'=>$trxId]);
}

$statusLabels = [
    'idle'      => '⏳ Initialisation...',
    'running'   => '🔄 Certification en cours...',
    'frozen'    => '⏸️ Vérification en cours',
    'blocked'   => '🚫 En attente de validation',
    'validated' => '✅ Virement validé !',
    'rejected'  => '❌ Virement refusé',
];

json_response([
    'success'      => true,
    'pct'          => (int)$t['certification_pct'],
    'status'       => $t['certification_status'],
    'status_label' => $statusLabels[$t['certification_status']] ?? '...',
    'speed'        => $t['certification_speed'],
    'message'      => $t['certification_message'],
]);
