<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Historique virements';
$navActive   = 'virement';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$per_page = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$type_f   = $_GET['type'] ?? 'all';
$where    = "c.user_id=:uid AND t.type IN ('virement_in','virement_out')";
if ($type_f === 'emis')  $where .= " AND t.type='virement_out'";
if ($type_f === 'recus') $where .= " AND t.type='virement_in'";
$params   = ['uid'=>Session::userId()];
$total    = (int)DB::scalar("SELECT COUNT(*) FROM transactions t JOIN comptes c ON t.compte_id=c.id WHERE $where", $params);
$pg       = pagination($total, $per_page, $page);
$params['l'] = $per_page; $params['o'] = $pg['offset'];
$txs = DB::all("SELECT t.*,c.numero FROM transactions t JOIN comptes c ON t.compte_id=c.id WHERE $where ORDER BY t.cree_le DESC LIMIT :l OFFSET :o", $params);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Historique virements</h1>
    <p class="gtb-page-sub"><?= $total ?> virement(s)</p>
  </div>
  <a href="index.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Nouveau</a>
</div>

<!-- Filtres -->
<div style="display:flex;gap:8px;margin-bottom:12px">
  <?php foreach (['all'=>'Tous','emis'=>'Émis','recus'=>'Reçus'] as $val => $label): ?>
  <a href="?type=<?= $val ?>" class="gtb-btn <?= $type_f===$val ? 'gtb-btn-primary' : 'gtb-btn-outline' ?> gtb-btn-sm"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="gtb-card">
  <?php if (empty($txs)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">Aucun virement</div>
  <?php else: ?>
  <?php foreach ($txs as $tx):
    $in = $tx['type'] === 'virement_in';
  ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <div style="width:36px;height:36px;border-radius:50%;background:<?= $in ? 'var(--green-light)' : 'var(--red-light)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="<?= $in ? 'var(--green)' : 'var(--red)' ?>" stroke-width="2.5">
        <?= $in ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>' ?>
      </svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($tx['description'] ?? 'Virement') ?></div>
      <div style="font-size:11px;color:var(--sub);font-family:monospace"><?= e($tx['reference'] ?? '') ?> · <?= date('d/m/Y H:i', strtotime($tx['cree_le'])) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-weight:700;font-size:14px;color:<?= $in ? 'var(--green)' : 'var(--red)' ?>"><?= ($in ? '+' : '−').number_format(abs((float)$tx['montant']),2,',',' ') ?> €</div>
      <div style="font-size:10px;color:var(--sub)"><?= ucfirst($tx['statut'] ?? '') ?></div>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Pagination -->
  <?php if ($pg['pages'] > 1): ?>
  <div style="display:flex;justify-content:center;gap:8px;padding:16px">
    <?php for ($p = 1; $p <= $pg['pages']; $p++): ?>
    <a href="?type=<?= $type_f ?>&page=<?= $p ?>" class="gtb-btn <?= $p===$page ? 'gtb-btn-primary' : 'gtb-btn-outline' ?> gtb-btn-sm" style="min-width:36px;justify-content:center"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
