<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mes crédits';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
$credits     = DB::all("SELECT * FROM credits WHERE user_id=:id ORDER BY statut,cree_le DESC", ['id'=>Session::userId()]);
$actifs      = array_filter($credits, fn($c) => $c['statut'] === 'en_cours');

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes crédits</h1>
    <p class="gtb-page-sub"><?= count($actifs) ?> crédit(s) en cours</p>
  </div>
  <a href="demande.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Nouvelle demande</a>
</div>

<?php if (empty($credits)): ?>
<div class="gtb-card" style="text-align:center;padding:48px 20px">
  <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="1.5" style="margin-bottom:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
  <p style="color:var(--sub);font-size:14px;margin-bottom:16px">Aucun crédit</p>
  <a href="demande.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">Faire une demande</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
  <?php foreach ($credits as $cr):
    $pct = $cr['montant'] > 0 ? round((($cr['montant'] - $cr['solde_restant']) / $cr['montant']) * 100) : 0;
    $statut_map = ['en_cours'=>['green','En cours'],'en_etude'=>['gold','En étude'],'accepte'=>['gold','Accepté'],'refuse'=>['red','Refusé'],'solde'=>['sub','Soldé']];
    [$sc, $sl] = $statut_map[$cr['statut']] ?? ['sub', ucfirst($cr['statut'])];
  ?>
  <div class="gtb-card" style="padding:18px 20px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
      <div>
        <div style="font-size:11px;color:var(--sub);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px"><?= ucfirst(e($cr['type'])) ?></div>
        <div style="font-size:1.3rem;font-weight:800;color:var(--dark)"><?= number_format((float)$cr['montant'], 2, ',', ' ') ?> €</div>
      </div>
      <span style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--<?= $sc ?>-light,rgba(0,0,0,.06));color:var(--<?= $sc ?>)"><?= $sl ?></span>
    </div>
    <?php if ($cr['statut'] === 'en_cours'): ?>
    <div style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--sub);margin-bottom:5px"><span>Remboursé</span><span><?= $pct ?>%</span></div>
      <div style="height:6px;background:var(--border);border-radius:99px;overflow:hidden"><div style="width:<?= $pct ?>%;height:100%;background:var(--gold);border-radius:99px"></div></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:10px">
      <div><div style="color:var(--sub);font-size:11px;margin-bottom:2px">Mensualité</div><div style="font-weight:700"><?= number_format((float)$cr['mensualite'], 2, ',', ' ') ?> €</div></div>
      <div style="text-align:right"><div style="color:var(--sub);font-size:11px;margin-bottom:2px">Restant dû</div><div style="font-weight:700"><?= number_format((float)$cr['solde_restant'], 2, ',', ' ') ?> €</div></div>
    </div>
    <?php endif; ?>
    <div style="font-size:11px;color:var(--sub2)">Réf. <?= e($cr['reference']) ?> · <?= $cr['duree_mois'] ?> mois · <?= $cr['taux'] ?>%</div>
    <?php if ($cr['motif_refus'] ?? null): ?>
    <div style="margin-top:8px;font-size:11px;color:var(--red);background:var(--red-light);padding:6px 10px;border-radius:6px"><?= e($cr['motif_refus']) ?></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
