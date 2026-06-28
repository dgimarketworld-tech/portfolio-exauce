<?php
require_once __DIR__ . '/../../backend/auth_required.php';

$pageTitle   = 'Virements';
$navActive   = 'virement';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$comptes       = DB::all("SELECT * FROM comptes WHERE user_id=:id AND statut='actif'", ['id'=>Session::userId()]);
$beneficiaires = DB::all("SELECT * FROM beneficiaires WHERE user_id=:id ORDER BY nom", ['id'=>Session::userId()]);

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $error = 'Token CSRF invalide.';
    } else {
        $from_id  = (int)($_POST['from_compte'] ?? 0);
        $iban     = trim($_POST['iban'] ?? '');
        $nom      = trim($_POST['nom_dest'] ?? '');
        $montant  = (float)str_replace([',',' '], ['.',''], $_POST['montant'] ?? '0');
        $motif    = trim($_POST['motif'] ?? '');
        $type_vir = $_POST['type_vir'] ?? 'sepa';

        if (!$from_id || !$iban || !$nom || $montant <= 0) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            $compte = DB::one("SELECT * FROM comptes WHERE id=:id AND user_id=:uid AND statut='actif'", ['id'=>$from_id,'uid'=>Session::userId()]);
            if (!$compte) {
                $error = 'Compte source invalide.';
            } elseif ($compte['solde'] < $montant) {
                $error = 'Solde insuffisant.';
            } else {
                $frais = $type_vir === 'instant' ? TRANSFER_FEE_INSTANT : TRANSFER_FEE_SEPA;
                $total = $montant + $frais;
                if ($compte['solde'] < $total) {
                    $error = 'Solde insuffisant (frais inclus).';
                } else {
                    DB::transaction(function() use ($from_id,$montant,$total,$motif,$iban,$nom,$frais) {
                        $ref = generate_reference('VIR');
                        DB::update("UPDATE comptes SET solde=solde-:t WHERE id=:id", ['t'=>$total,'id'=>$from_id]);
                        $nouveau_solde = (float)DB::scalar("SELECT solde FROM comptes WHERE id=:id", ['id'=>$from_id]);
                        DB::insertInto('transactions', ['compte_id'=>$from_id,'type'=>'virement_out','montant'=>$montant,'solde_apres'=>$nouveau_solde,'description'=>$motif ?: "Virement vers $nom",'reference'=>$ref,'statut'=>'terminee']);
                        if ($frais > 0) {
                            DB::insertInto('transactions', ['compte_id'=>$from_id,'type'=>'frais','montant'=>$frais,'solde_apres'=>$nouveau_solde,'description'=>'Frais virement instantané','reference'=>generate_reference('FRAIS'),'statut'=>'terminee']);
                        }
                        notify(Session::userId(), 'Virement exécuté', "Virement de ".format_money($montant)." vers $nom effectué.", 'success', 'fa-check');
                        send_notification_email(Session::userId(), 'Confirmation de virement — GTB Bank', "Votre virement de <strong>".format_money($montant)."</strong> vers <strong>$nom</strong> a été exécuté avec succès.");
                        $u = DB::one("SELECT COALESCE(prenom,first_name,'') AS prenom, COALESCE(nom,last_name,'') AS nom, email FROM users WHERE id=:id", ['id'=>Session::userId()]);
                        $html_adm = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'><h2 style='color:#1a3c5e'>Nouveau virement effectué</h2><table style='width:100%;border-collapse:collapse;color:#374151'><tr><td style='padding:6px 0;font-weight:600'>Client</td><td>{$u['prenom']} {$u['nom']} ({$u['email']})</td></tr><tr><td style='padding:6px 0;font-weight:600'>Montant</td><td>".format_money($montant)."</td></tr><tr><td style='padding:6px 0;font-weight:600'>Vers</td><td>$nom ($iban)</td></tr><tr><td style='padding:6px 0;font-weight:600'>Référence</td><td>$ref</td></tr><tr><td style='padding:6px 0;font-weight:600'>Date</td><td>".date('d/m/Y H:i')."</td></tr></table><p style='color:#9ca3af;font-size:12px;margin-top:24px'>Global Trust Bank — Notification automatique</p></div>";
                        send_email(MAIL_SUPPORT, 'Admin GTB', "Virement ".format_money($montant)." — {$u['prenom']} {$u['nom']}", $html_adm);
                    });
                    $success = "Virement de ".format_money($montant)." vers $nom exécuté avec succès.";
                }
            }
        }
    }
}
$csrf = csrf_token();

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Nouveau virement</h1>
    <p class="gtb-page-sub">SEPA ou instantané</p>
  </div>
  <a href="historique.php" class="gtb-btn gtb-btn-outline gtb-btn-sm">Historique</a>
</div>

<?php if ($error): ?>
<div class="gtb-alert gtb-alert-error">
  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <?= e($error) ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="gtb-alert gtb-alert-success">
  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <?= e($success) ?>
</div>
<?php endif; ?>

<div class="gtb-vir-layout">
  <!-- FORMULAIRE -->
  <div class="gtb-card">
    <form method="POST">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>

      <div class="gtb-form-group">
        <label class="gtb-label">Compte source</label>
        <select name="from_compte" class="gtb-select" required onchange="updateSolde(this)">
          <option value="">Sélectionner un compte</option>
          <?php foreach ($comptes as $c): ?>
          <option value="<?= $c['id'] ?>" data-solde="<?= $c['solde'] ?>">
            <?= ucfirst($c['type']) ?> — <?= format_money($c['solde'], $c['devise']) ?> — <?= e(substr($c['numero'],-8)) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <div id="solde_disp" style="font-size:12px;color:var(--sub);margin-top:4px"></div>
      </div>

      <?php if (!empty($beneficiaires)): ?>
      <div class="gtb-form-group">
        <label class="gtb-label">Bénéficiaire enregistré</label>
        <select name="_ben" class="gtb-select" onchange="fillBen(this)">
          <option value="">Saisir manuellement…</option>
          <?php foreach ($beneficiaires as $b): ?>
          <option value="<?= e($b['iban']) ?>" data-nom="<?= e($b['nom']) ?>">
            <?= e($b['nom']) ?> — <?= e($b['iban']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="gtb-form-group">
        <label class="gtb-label">Nom du bénéficiaire</label>
        <input class="gtb-input" name="nom_dest" id="nom_dest" placeholder="Prénom Nom ou Raison sociale" required/>
      </div>

      <div class="gtb-form-group">
        <label class="gtb-label">IBAN</label>
        <input class="gtb-input" name="iban" id="iban_dest" placeholder="FR76 …" required/>
      </div>

      <div class="gtb-two-col">
        <div class="gtb-form-group">
          <label class="gtb-label">Montant (€)</label>
          <input class="gtb-input" name="montant" type="number" step="0.01" min="0.01" placeholder="0,00" required/>
        </div>
        <div class="gtb-form-group">
          <label class="gtb-label">Type</label>
          <select name="type_vir" class="gtb-select">
            <option value="sepa">SEPA standard (gratuit, J+1)</option>
            <option value="instant">Instantané (<?= format_money(TRANSFER_FEE_INSTANT) ?>, &lt;10s)</option>
          </select>
        </div>
      </div>

      <div class="gtb-form-group">
        <label class="gtb-label">Motif (optionnel)</label>
        <input class="gtb-input" name="motif" placeholder="Ex: Loyer, Remboursement…" maxlength="140"/>
      </div>

      <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-full">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        Exécuter le virement
      </button>
    </form>
  </div>

  <!-- INFO PANEL -->
  <div class="gtb-card">
    <div class="gtb-card-title">Informations</div>
    <ul class="gtb-info-list">
      <li>
        <div class="gtb-info-icon" style="background:#EFF6FF">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#1A73E8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div><strong>SEPA standard</strong><br>J+1 ouvré, gratuit</div>
      </li>
      <li>
        <div class="gtb-info-icon" style="background:#FFF8E1">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#D4AF37" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div><strong>Instantané</strong><br>&lt;10 secondes, <?= format_money(TRANSFER_FEE_INSTANT) ?></div>
      </li>
      <li>
        <div class="gtb-info-icon" style="background:#F0FDF4">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#00C67A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div><strong>Plafond journalier</strong><br><?= format_money(TRANSFER_LIMIT_DAILY) ?></div>
      </li>
    </ul>
    <a href="beneficiaires.php" class="gtb-btn gtb-btn-outline gtb-btn-full" style="margin-top:20px">
      Gérer mes bénéficiaires
    </a>
  </div>
</div>

<style>
.gtb-vir-layout {
  display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: 0;
}
@media (min-width: 768px) {
  .gtb-vir-layout { grid-template-columns: 1fr 280px; }
}
.gtb-info-list { list-style: none; display: flex; flex-direction: column; gap: 16px; }
.gtb-info-list li { display: flex; align-items: flex-start; gap: 12px; font-size: 13px; color: var(--sub); line-height: 1.5; }
.gtb-info-list li strong { color: var(--text); display: block; }
.gtb-info-icon { width: 32px; height: 32px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; }
</style>

<script>
function updateSolde(sel) {
  const o = sel.options[sel.selectedIndex];
  const s = o.dataset.solde;
  document.getElementById('solde_disp').textContent = s
    ? 'Solde disponible : ' + parseFloat(s).toLocaleString('fr-FR', {style:'currency', currency:'EUR'})
    : '';
}
function fillBen(sel) {
  const o = sel.options[sel.selectedIndex];
  if (o.value) {
    document.getElementById('iban_dest').value = o.value;
    document.getElementById('nom_dest').value  = o.dataset.nom || '';
  }
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
