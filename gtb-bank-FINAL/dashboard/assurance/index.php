<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Assurances';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$uid = Session::userId();
try { DB::pdo()->exec("CREATE TABLE IF NOT EXISTS assurances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(40) NOT NULL,
    numero_contrat VARCHAR(60),
    compagnie VARCHAR(60) DEFAULT 'GTB Protect',
    prime_mensuelle DECIMAL(10,2),
    statut ENUM('actif','en_attente','resilie') DEFAULT 'en_attente',
    date_debut DATE,
    date_fin DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)"); } catch (\Exception $e) {}
$contrats = DB::all("SELECT * FROM assurances WHERE user_id=:id ORDER BY created_at DESC", ['id'=>$uid]);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes assurances</h1>
    <p class="gtb-page-sub">Protégez ce qui compte</p>
  </div>
  <a href="souscription.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Souscrire</a>
</div>

<!-- Produits -->
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
  <?php foreach ([
    ['Assurance Habitation','Couverture complète multi-risques','À partir de 12€/mois','habitation'],
    ['Assurance Auto','Tous risques ou tiers','À partir de 45€/mois','auto'],
    ['Assurance Vie','Épargne + protection','Rendement 2,8%/an','vie'],
    ['Assurance Santé','Complémentaire santé GTB','À partir de 28€/mois','sante'],
    ['Assurance Mobile','Casse, vol, oxydation','3,99€/mois','mobile'],
    ['Assurance Voyage','Couverture internationale','8€/voyage','voyage'],
  ] as [$nom,$desc,$prix,$type]): ?>
  <div class="gtb-card" style="display:flex;align-items:center;gap:14px;padding:14px 20px">
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:700;color:var(--dark)"><?= $nom ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= $desc ?></div>
      <div style="font-size:11px;color:var(--gold);font-weight:600;margin-top:2px"><?= $prix ?></div>
    </div>
    <a href="souscription.php?type=<?= $type ?>" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="flex-shrink:0">Souscrire</a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Contrats actifs -->
<div class="gtb-card">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Mes contrats (<?= count($contrats) ?>)</div>
  <?php if (empty($contrats)): ?>
  <div style="text-align:center;padding:32px 20px;color:var(--sub);font-size:13px">Aucun contrat actif.</div>
  <?php else: ?>
  <?php foreach ($contrats as $c):
    $sc = $c['statut']==='actif' ? 'green' : ($c['statut']==='resilie' ? 'red' : 'gold');
  ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= ucfirst(e($c['type'])) ?></div>
      <div style="font-size:11px;color:var(--sub);font-family:monospace"><?= e($c['numero_contrat']??'—') ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= number_format((float)$c['prime_mensuelle'],2,',','') ?> €/mois</div>
    </div>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--<?= $sc ?>-light,rgba(0,0,0,.05));color:var(--<?= $sc ?>);flex-shrink:0"><?= ucfirst($c['statut']) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
