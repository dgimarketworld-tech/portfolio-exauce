<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Investissements';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$uid = Session::userId();
DB::pdo()->exec("CREATE TABLE IF NOT EXISTS investissements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    produit VARCHAR(60) NOT NULL,
    type VARCHAR(40) DEFAULT 'performance',
    montant_initial DECIMAL(12,2) NOT NULL,
    montant_actuel DECIMAL(12,2) NOT NULL,
    rendement_pct DECIMAL(5,2) DEFAULT 0,
    statut ENUM('actif','cloture','suspendu') DEFAULT 'actif',
    date_debut DATE NOT NULL,
    date_fin DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$inv_error = ''; $inv_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $type_inv    = $_POST['type_inv'] ?? 'GTB Performance';
    $montant_inv = (float)($_POST['montant_inv'] ?? 0);
    $rendement   = ['GTB Performance'=>6.8,'GTB Sécurisé'=>2.5,'GTB Premium Plus'=>12.0][$type_inv] ?? 0;
    if ($montant_inv < 100) {
        $inv_error = 'Montant minimum : 100 €.';
    } else {
        $compte = DB::one("SELECT * FROM comptes WHERE user_id=:uid AND statut='actif' ORDER BY solde DESC LIMIT 1", ['uid'=>$uid]);
        if (!$compte || $compte['solde'] < $montant_inv) {
            $inv_error = 'Solde insuffisant.';
        } else {
            DB::pdo()->prepare("UPDATE comptes SET solde=solde-:m WHERE id=:id")->execute(['m'=>$montant_inv,'id'=>$compte['id']]);
            DB::insertInto('investissements', ['user_id'=>$uid,'produit'=>$type_inv,'type'=>'performance','montant_initial'=>$montant_inv,'montant_actuel'=>$montant_inv,'rendement_pct'=>$rendement,'statut'=>'actif','date_debut'=>date('Y-m-d'),'date_fin'=>date('Y-m-d', strtotime('+1 year'))]);
            notify($uid, 'Investissement souscrit', "Votre investissement $type_inv de ".number_format($montant_inv,2,',','.')." € est actif.", 'success');
            $inv_success = "Investissement de ".number_format($montant_inv,2,',','.')." € souscrit avec succès.";
        }
    }
}
$investissements = DB::all("SELECT * FROM investissements WHERE user_id=:uid ORDER BY created_at DESC", ['uid'=>$uid]);
$csrf = csrf_token();
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Investissements</h1>
    <p class="gtb-page-sub">Faites fructifier votre épargne</p>
  </div>
</div>

<!-- Produits -->
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
  <?php foreach ([
    ['GTB Performance','Rendement moyen : +6,8%/an','Portefeuille diversifié — actions, obligations, ETF.','gold','Modéré'],
    ['GTB Sécurisé','Rendement garanti : +2,5%/an','Capital garanti, fonds obligataires. Idéal débutants.','green','Faible'],
    ['GTB Premium Plus','Potentiel : +12%/an','Private equity, marchés émergents. Réservé Premium.','red','Élevé'],
  ] as [$nom,$rend,$desc,$rc,$risque]): ?>
  <div class="gtb-card" style="padding:16px 20px;display:flex;align-items:center;gap:14px">
    <div style="width:44px;height:44px;border-radius:12px;background:var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:13px;color:var(--dark)"><?= $nom ?></div>
      <div style="font-size:11px;color:var(--<?= $rc ?>);font-weight:600"><?= $rend ?></div>
      <div style="font-size:11px;color:var(--sub);margin-top:2px"><?= $desc ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <span style="display:block;font-size:10px;color:var(--sub);margin-bottom:4px">Risque</span>
      <span style="display:inline-flex;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--<?= $rc ?>-light,rgba(0,0,0,.05));color:var(--<?= $rc ?>)"><?= $risque ?></span>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Souscrire -->
<div class="gtb-card" style="padding:20px;margin-bottom:16px" id="souscrire">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Souscrire un investissement</div>
  <?php if ($inv_error): ?><div style="background:var(--red-light);border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:var(--red)"><?= e($inv_error) ?></div><?php endif; ?>
  <?php if ($inv_success): ?><div style="background:var(--green-light);border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:var(--green)"><?= e($inv_success) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <div style="margin-bottom:12px">
      <label class="gtb-label">Produit</label>
      <select name="type_inv" class="gtb-input">
        <option>GTB Performance</option>
        <option>GTB Sécurisé</option>
        <option>GTB Premium Plus</option>
      </select>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Montant (min. 100 €)</label>
      <input class="gtb-input" type="number" name="montant_inv" min="100" step="50" placeholder="500"/>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Souscrire</button>
  </form>
</div>

<!-- Positions -->
<div class="gtb-card">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Mes positions</div>
  <?php if (empty($investissements)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">Aucun investissement actif.</div>
  <?php else: ?>
  <?php foreach ($investissements as $inv):
    $sc = $inv['statut']==='actif' ? 'green' : ($inv['statut']==='cloture' ? 'sub' : 'gold');
  ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= e($inv['produit']) ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= date('d/m/Y', strtotime($inv['date_debut'])) ?><?= $inv['date_fin'] ? ' → '.date('d/m/Y', strtotime($inv['date_fin'])) : '' ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-weight:700;font-size:13px;color:var(--dark)"><?= number_format((float)$inv['montant_initial'],2,',',' ') ?> €</div>
      <div style="font-size:11px;color:var(--green)">+<?= $inv['rendement_pct'] ?>%/an</div>
    </div>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--<?= $sc ?>-light,rgba(0,0,0,.05));color:var(--<?= $sc ?>);flex-shrink:0"><?= ucfirst($inv['statut']) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
