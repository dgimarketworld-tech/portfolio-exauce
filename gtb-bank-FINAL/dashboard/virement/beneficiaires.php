<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mes bénéficiaires';
$navActive   = 'virement';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf($_POST['_csrf'] ?? '')) {
        $error = 'Token invalide.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $nom     = trim($_POST['nom'] ?? '');
            $iban    = strtoupper(preg_replace('/\s+/', '', $_POST['iban'] ?? ''));
            $bic     = strtoupper(trim($_POST['bic'] ?? ''));
            $intitule = trim($_POST['intitule'] ?? '');
            if (!$nom || !$iban) { $error = 'Nom et IBAN obligatoires.'; }
            else { DB::insertInto('beneficiaires', ['user_id'=>Session::userId(),'nom'=>$nom,'iban'=>$iban,'bic'=>$bic,'intitule'=>$intitule]); $success = 'Bénéficiaire ajouté.'; }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['ben_id'] ?? 0);
            DB::update("DELETE FROM beneficiaires WHERE id=:id AND user_id=:uid", ['id'=>$id,'uid'=>Session::userId()]);
            $success = 'Bénéficiaire supprimé.';
        }
    }
}
$bens = DB::all("SELECT * FROM beneficiaires WHERE user_id=:id ORDER BY nom", ['id'=>Session::userId()]);
$csrf = csrf_token();

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes bénéficiaires</h1>
    <p class="gtb-page-sub"><?= count($bens) ?> enregistré(s)</p>
  </div>
</div>

<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>

<!-- Formulaire ajout -->
<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Ajouter un bénéficiaire</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="action" value="add"/>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
      <div><label class="gtb-label">Nom</label><input class="gtb-input" name="nom" placeholder="Jean Dupont" required/></div>
      <div><label class="gtb-label">Intitulé (optionnel)</label><input class="gtb-input" name="intitule" placeholder="Loyer, remboursement…"/></div>
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:16px">
      <div><label class="gtb-label">IBAN</label><input class="gtb-input" name="iban" placeholder="FR76 3000 6000 0112 3456 7890 189" style="font-family:monospace" required/></div>
      <div><label class="gtb-label">BIC</label><input class="gtb-input" name="bic" placeholder="BNPAFRPP" style="font-family:monospace"/></div>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm">Ajouter</button>
  </form>
</div>

<!-- Liste -->
<div class="gtb-card">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Bénéficiaires enregistrés</div>
  <?php if (empty($bens)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">Aucun bénéficiaire enregistré.</div>
  <?php else: ?>
  <?php foreach ($bens as $b): ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--gold-light);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px;color:var(--gold);flex-shrink:0"><?= strtoupper(substr($b['nom'], 0, 2)) ?></div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= e($b['nom']) ?></div>
      <div style="font-size:11px;color:var(--sub);font-family:monospace"><?= e($b['iban']) ?></div>
      <?php if ($b['intitule']): ?><div style="font-size:11px;color:var(--sub2)"><?= e($b['intitule']) ?></div><?php endif; ?>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
      <a href="index.php?ben_id=<?= (int)$b['id'] ?>" class="gtb-btn gtb-btn-primary gtb-btn-sm">Virer</a>
      <form method="POST" style="display:inline">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="ben_id" value="<?= (int)$b['id'] ?>"/>
        <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="color:var(--red);border-color:var(--red)">Suppr.</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
