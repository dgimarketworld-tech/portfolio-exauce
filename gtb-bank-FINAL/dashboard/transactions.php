<?php
require_once __DIR__ . '/../backend/auth_required.php';

$pageTitle   = 'Transactions';
$navActive   = 'transactions';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$per_page    = 20;
$page        = max(1, (int)($_GET['page'] ?? 1));
$type_filter = $_GET['type'] ?? '';

$where  = 'c.user_id=:uid';
$params = ['uid'=>Session::userId()];
if ($type_filter && in_array($type_filter, ['depot','retrait','virement_in','virement_out'])) {
    $where .= ' AND t.type=:type';
    $params['type'] = $type_filter;
}

$total = (int)DB::scalar("SELECT COUNT(*) FROM transactions t JOIN comptes c ON t.compte_id=c.id WHERE $where", $params);
$pg    = pagination($total, $per_page, $page);
$params['limit']  = $per_page;
$params['offset'] = $pg['offset'];

$txs = DB::all(
    "SELECT t.*, c.numero FROM transactions t JOIN comptes c ON t.compte_id=c.id
     WHERE $where ORDER BY t.cree_le DESC LIMIT :limit OFFSET :offset",
    $params
);

require __DIR__ . '/includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Transactions</h1>
    <p class="gtb-page-sub"><?= $total ?> opération(s) au total</p>
  </div>
  <button class="gtb-btn gtb-btn-outline gtb-btn-sm" onclick="window.print()">Exporter</button>
</div>

<!-- FILTRE -->
<div class="gtb-card gtb-card-sm" style="margin-bottom:16px">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select name="type" class="gtb-select" style="width:200px;padding:10px 14px" onchange="this.form.submit()">
      <option value="">Tous les types</option>
      <option value="depot"        <?= $type_filter==='depot'        ?'selected':''?>>Dépôts</option>
      <option value="retrait"      <?= $type_filter==='retrait'      ?'selected':''?>>Retraits</option>
      <option value="virement_in"  <?= $type_filter==='virement_in'  ?'selected':''?>>Virements reçus</option>
      <option value="virement_out" <?= $type_filter==='virement_out' ?'selected':''?>>Virements émis</option>
    </select>
    <?php if ($type_filter): ?>
    <a href="transactions.php" class="gtb-btn gtb-btn-ghost gtb-btn-sm">Effacer</a>
    <?php endif; ?>
  </form>
</div>

<!-- LISTE MOBILE (cards) -->
<div class="gtb-tx-list">
  <?php if (empty($txs)): ?>
  <div class="gtb-card gtb-empty">Aucune transaction pour le moment</div>
  <?php else: ?>
  <?php foreach ($txs as $tx):
    $in    = (strpos($tx['type'],'in') !== false || $tx['type'] === 'depot');
    $label = e($tx['description'] ?? ucfirst(str_replace('_',' ',$tx['type'])));
    $statut_class = $tx['statut'] === 'terminee' ? 'gtb-badge-green' : ($tx['statut'] === 'en_cours' ? 'gtb-badge-blue' : 'gtb-badge-red');
  ?>
  <div class="gtb-tx-row">
    <div class="gtb-list-icon" style="background:<?= $in ? 'var(--green-light)' : 'var(--red-light)' ?>">
      <?php if ($in): ?>
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
      <?php else: ?>
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--red)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
      <?php endif; ?>
    </div>
    <div class="gtb-list-info">
      <div class="gtb-list-title"><?= $label ?></div>
      <div class="gtb-list-sub"><?= format_datetime($tx['cree_le']) ?> · <?= e($tx['reference']) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:14px;color:<?= $in ? 'var(--green)' : 'var(--red)' ?>">
        <?= ($in ? '+' : '-') . format_money($tx['montant']) ?>
      </div>
      <span class="gtb-badge <?= $statut_class ?>" style="margin-top:4px"><?= ucfirst($tx['statut']) ?></span>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- TABLEAU DESKTOP -->
<div class="gtb-tx-table gtb-table-wrap">
  <table class="gtb-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Description</th>
        <th>Référence</th>
        <th>Compte</th>
        <th style="text-align:right">Montant</th>
        <th>Statut</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($txs)): ?>
    <tr><td colspan="6" class="gtb-empty">Aucune transaction</td></tr>
    <?php else: ?>
    <?php foreach ($txs as $tx):
      $in = (strpos($tx['type'],'in') !== false || $tx['type'] === 'depot');
      $statut_class = $tx['statut'] === 'terminee' ? 'gtb-badge-green' : ($tx['statut'] === 'en_cours' ? 'gtb-badge-blue' : 'gtb-badge-red');
    ?>
    <tr>
      <td><?= format_datetime($tx['cree_le']) ?></td>
      <td><strong><?= e($tx['description'] ?? ucfirst(str_replace('_',' ',$tx['type']))) ?></strong></td>
      <td style="font-family:monospace;font-size:12px;color:var(--sub)"><?= e($tx['reference']) ?></td>
      <td style="font-size:12px;color:var(--sub)"><?= e($tx['numero']) ?></td>
      <td style="text-align:right;font-family:'Sora',sans-serif;font-weight:700;color:<?= $in ? 'var(--green)' : 'var(--red)' ?>">
        <?= ($in ? '+' : '-') . format_money($tx['montant']) ?>
      </td>
      <td><span class="gtb-badge <?= $statut_class ?>"><?= ucfirst($tx['statut']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- PAGINATION -->
<?php if ($pg['total_pages'] > 1): ?>
<div style="display:flex;justify-content:center;align-items:center;gap:8px;margin-top:20px">
  <?php if ($pg['has_prev']): ?>
  <a href="?page=<?= $pg['current']-1 ?>&type=<?= $type_filter ?>" class="gtb-btn gtb-btn-outline gtb-btn-sm">← Préc.</a>
  <?php endif; ?>
  <span style="font-size:13px;color:var(--sub)">Page <?= $pg['current'] ?> / <?= $pg['total_pages'] ?></span>
  <?php if ($pg['has_next']): ?>
  <a href="?page=<?= $pg['current']+1 ?>&type=<?= $type_filter ?>" class="gtb-btn gtb-btn-outline gtb-btn-sm">Suiv. →</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<style>
/* Mobile : liste de cards */
.gtb-tx-list { display: flex; flex-direction: column; gap: 0; }
.gtb-tx-row {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 16px;
  background: var(--card);
  border-bottom: 1px solid var(--border);
}
.gtb-tx-row:first-child { border-radius: var(--r-lg) var(--r-lg) 0 0; border-top: 1px solid var(--border); }
.gtb-tx-row:last-child { border-radius: 0 0 var(--r-lg) var(--r-lg); }
.gtb-tx-row:only-child { border-radius: var(--r-lg); }
/* Desktop : table */
.gtb-tx-table { display: none; }
@media (min-width: 768px) {
  .gtb-tx-list { display: none; }
  .gtb-tx-table { display: block; }
}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
