<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Virements récurrents';
$navActive   = 'virement';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_POST['vr_id'] ?? 0);
        DB::update("UPDATE virements_recurrents SET statut='annule' WHERE id=:id AND user_id=:uid", ['id'=>$id,'uid'=>Session::userId()]);
        $success = 'Virement récurrent annulé.';
    } elseif ($action === 'add') {
        $compte_id = (int)($_POST['compte_id'] ?? 0);
        $iban      = trim($_POST['iban'] ?? '');
        $nom       = trim($_POST['nom_dest'] ?? '');
        $montant   = (float)($_POST['montant'] ?? 0);
        $freq      = $_POST['frequence'] ?? 'mensuel';
        $jour      = (int)($_POST['jour'] ?? 1);
        $motif     = trim($_POST['motif'] ?? '');
        $prochain  = date('Y-m-'.str_pad($jour, 2, '0', STR_PAD_LEFT));
        if ($montant > 0 && $iban && $nom && $compte_id) {
            DB::insertInto('virements_recurrents', ['user_id'=>Session::userId(),'compte_id'=>$compte_id,'iban_dest'=>$iban,'nom_dest'=>$nom,'montant'=>$montant,'frequence'=>$freq,'jour_execution'=>$jour,'prochain_le'=>$prochain,'motif'=>$motif]);
            $success = 'Virement récurrent créé.';
        } else { $error = 'Champs incomplets.'; }
    }
}
$recurrents    = DB::all("SELECT vr.*,c.numero FROM virements_recurrents vr JOIN comptes c ON vr.compte_id=c.id WHERE vr.user_id=:id AND vr.statut='actif' ORDER BY vr.jour_execution", ['id'=>Session::userId()]);
$comptes       = DB::all("SELECT * FROM comptes WHERE user_id=:id AND statut='actif'", ['id'=>Session::userId()]);
$total_mensuel = array_sum(array_column(array_filter($recurrents, fn($r) => $r['frequence'] === 'mensuel'), 'montant'));
$csrf          = csrf_token();
$show_form     = isset($_GET['new']) || !empty($error);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Virements récurrents</h1>
    <p class="gtb-page-sub"><?= count($recurrents) ?> actif(s) · <?= number_format($total_mensuel,2,',','') ?> €/mois</p>
  </div>
  <a href="?new=1" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Nouveau</a>
</div>

<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>

<!-- Formulaire nouveau -->
<?php if ($show_form): ?>
<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Nouveau virement récurrent</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="action" value="add"/>
    <div style="margin-bottom:12px">
      <label class="gtb-label">Compte source</label>
      <select name="compte_id" class="gtb-input" required>
        <?php foreach ($comptes as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= ucfirst(e($c['type'])) ?> — <?= e(substr($c['numero'],-8)) ?> (<?= number_format((float)$c['solde'],2,',','') ?> €)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
      <div><label class="gtb-label">Nom destinataire</label><input class="gtb-input" name="nom_dest" placeholder="Jean Dupont" required/></div>
      <div><label class="gtb-label">Montant (€)</label><input class="gtb-input" type="number" name="montant" min="1" step="0.01" required/></div>
    </div>
    <div style="margin-bottom:12px">
      <label class="gtb-label">IBAN destinataire</label>
      <input class="gtb-input" name="iban" placeholder="FR76 …" style="font-family:monospace" required/>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
      <div>
        <label class="gtb-label">Fréquence</label>
        <select name="frequence" class="gtb-input">
          <option value="mensuel">Mensuel</option>
          <option value="hebdomadaire">Hebdomadaire</option>
          <option value="trimestriel">Trimestriel</option>
        </select>
      </div>
      <div><label class="gtb-label">Jour du mois</label><input class="gtb-input" type="number" name="jour" min="1" max="28" value="1"/></div>
      <div><label class="gtb-label">Motif (opt.)</label><input class="gtb-input" name="motif" placeholder="Loyer…"/></div>
    </div>
    <div style="display:flex;gap:8px">
      <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm">Créer</button>
      <a href="recurrents.php" class="gtb-btn gtb-btn-outline gtb-btn-sm">Annuler</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Liste -->
<div class="gtb-card">
  <?php if (empty($recurrents)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">Aucun virement récurrent actif.</div>
  <?php else: ?>
  <?php foreach ($recurrents as $r): ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= e($r['nom_dest']) ?></div>
      <div style="font-size:11px;color:var(--sub);font-family:monospace"><?= e($r['iban_dest']) ?></div>
      <div style="font-size:11px;color:var(--sub2)"><?= ucfirst($r['frequence']) ?> · <?= $r['jour_execution'] ?>e du mois · Prochain : <?= date('d/m/Y', strtotime($r['prochain_le'])) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0;margin-right:8px">
      <div style="font-weight:700;font-size:14px;color:var(--dark)"><?= number_format((float)$r['montant'],2,',','') ?> €</div>
    </div>
    <form method="POST" style="flex-shrink:0">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
      <input type="hidden" name="action" value="delete"/>
      <input type="hidden" name="vr_id" value="<?= (int)$r['id'] ?>"/>
      <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="color:var(--red);border-color:var(--red)">Annuler</button>
    </form>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
