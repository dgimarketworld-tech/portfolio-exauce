<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Détail compte';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$id      = (int)($_GET['id'] ?? 0);
$compte  = DB::one("SELECT * FROM comptes WHERE id=:id AND user_id=:uid", ['id'=>$id,'uid'=>Session::userId()]);
if (!$compte) { header('Location: index.php'); exit; }

$per_page = 15;
$page     = max(1, (int)($_GET['page'] ?? 1));
$total    = (int)DB::scalar("SELECT COUNT(*) FROM transactions WHERE compte_id=:id", ['id'=>$id]);
$pg       = pagination($total, $per_page, $page);
$txs      = DB::all("SELECT * FROM transactions WHERE compte_id=:id ORDER BY cree_le DESC LIMIT :l OFFSET :o", ['id'=>$id,'l'=>$per_page,'o'=>$pg['offset']]);
$carte    = DB::one("SELECT * FROM cartes WHERE compte_id=:id AND statut='active' LIMIT 1", ['id'=>$id]);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <a href="index.php" style="font-size:12px;color:var(--sub);text-decoration:none;display:flex;align-items:center;gap:4px;margin-bottom:6px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Retour
    </a>
    <h1 class="gtb-page-title"><?= ucfirst(e($compte['type'])) ?></h1>
    <p class="gtb-page-sub" style="font-family:monospace"><?= e($compte['numero']) ?></p>
  </div>
  <span class="gtb-badge <?= $compte['statut']==='actif' ? 'gtb-badge-green' : 'gtb-badge-red' ?>"><?= ucfirst(e($compte['statut'])) ?></span>
</div>

<!-- Solde card -->
<div class="gtb-balance-card" style="margin-bottom:16px">
  <div style="font-size:12px;opacity:.7;margin-bottom:6px;letter-spacing:.05em;text-transform:uppercase">Solde disponible</div>
  <div style="font-size:2rem;font-weight:800;letter-spacing:-.02em"><?= number_format((float)$compte['solde'], 2, ',', ' ') . ' ' . e($compte['devise']) ?></div>
</div>

<!-- Infos compte -->
<div class="gtb-card" style="margin-bottom:16px;padding:16px 20px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:12px">Informations du compte</div>
  <?php
  $rows = [
    ['IBAN / Numéro', e($compte['numero'])],
    ['Type', ucfirst(e($compte['type']))],
    ['Devise', e($compte['devise'] ?? 'EUR')],
    ['Ouvert le', $compte['cree_le'] ? date('d/m/Y', strtotime($compte['cree_le'])) : '—'],
  ];
  if ($carte): $rows[] = ['Carte liée', e($carte['numero_masque'] ?? '—') . ' (' . e($carte['type']) . ')']; endif;
  foreach ($rows as [$label, $val]): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
    <span style="font-size:12px;color:var(--sub)"><?= $label ?></span>
    <span style="font-size:13px;font-weight:600;color:var(--dark);font-family:<?= $label==='IBAN / Numéro' ? 'monospace' : 'inherit' ?>"><?= $val ?></span>
  </div>
  <?php endforeach; ?>
</div>

<!-- Transactions -->
<div class="gtb-card">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div style="font-size:13px;font-weight:700;color:var(--dark)">Historique des opérations</div>
    <span style="font-size:11px;color:var(--sub)"><?= $total ?> opération(s)</span>
  </div>
  <?php if (empty($txs)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">Aucune opération</div>
  <?php else: ?>
  <?php foreach ($txs as $t):
    $pos = in_array($t['type'], ['depot','virement_in','credit'], true);
  ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="width:36px;height:36px;border-radius:50%;background:<?= $pos ? 'var(--green-light)' : 'var(--red-light)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="<?= $pos ? 'var(--green)' : 'var(--red)' ?>" stroke-width="2.5">
        <?= $pos ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>' ?>
      </svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($t['description'] ?? ucfirst($t['type'])) ?></div>
      <div style="font-size:11px;color:var(--sub);margin-top:2px"><?= date('d/m/Y H:i', strtotime($t['cree_le'] ?? 'now')) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-weight:700;font-size:14px;color:<?= $pos ? 'var(--green)' : 'var(--red)' ?>"><?= ($pos ? '+' : '−') . number_format(abs((float)$t['montant']), 2, ',', ' ') ?> <?= e($compte['devise']) ?></div>
      <?php if ($t['solde_apres'] ?? null): ?>
      <div style="font-size:10px;color:var(--sub2)"><?= number_format((float)$t['solde_apres'], 2, ',', ' ') ?> <?= e($compte['devise']) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Pagination -->
  <?php if ($pg['pages'] > 1): ?>
  <div style="display:flex;justify-content:center;gap:8px;padding:16px">
    <?php for ($p = 1; $p <= $pg['pages']; $p++): ?>
    <a href="?id=<?= $id ?>&page=<?= $p ?>" class="gtb-btn <?= $p===$page ? 'gtb-btn-primary' : 'gtb-btn-outline' ?> gtb-btn-sm" style="min-width:36px;justify-content:center"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<style>
.gtb-badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600}
.gtb-badge-green{background:var(--green-light);color:var(--green)}
.gtb-badge-red{background:var(--red-light);color:var(--red)}
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
