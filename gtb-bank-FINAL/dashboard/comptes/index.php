<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mes comptes';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
$comptes     = DB::all("SELECT * FROM comptes WHERE user_id=:id ORDER BY type", ['id'=>Session::userId()]);
$total_solde = array_sum(array_column($comptes, 'solde'));

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes comptes</h1>
    <p class="gtb-page-sub"><?= count($comptes) ?> compte(s) actif(s)</p>
  </div>
</div>

<!-- Solde global -->
<div class="gtb-balance-card" style="margin-bottom:20px">
  <div style="font-size:12px;opacity:.7;margin-bottom:6px;letter-spacing:.05em;text-transform:uppercase">Solde global</div>
  <div style="font-size:2rem;font-weight:800;letter-spacing:-.02em"><?= number_format($total_solde, 2, ',', ' ') ?> €</div>
  <div style="font-size:12px;opacity:.6;margin-top:6px"><?= count($comptes) ?> compte(s) consolidé(s)</div>
</div>

<?php if (empty($comptes)): ?>
<div class="gtb-card" style="text-align:center;padding:48px 20px">
  <p style="color:var(--sub);font-size:14px">Aucun compte trouvé.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
  <?php foreach ($comptes as $c):
    $type_icons = [
      'courant'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
      'epargne'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/>',
      'business' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>',
    ];
    $icon = $type_icons[$c['type']] ?? $type_icons['courant'];
    $statut_color = $c['statut'] === 'actif' ? 'var(--green)' : 'var(--red)';
  ?>
  <a href="detail.php?id=<?= (int)$c['id'] ?>" class="gtb-card" style="display:flex;align-items:center;gap:16px;padding:18px 20px;text-decoration:none;color:inherit">
    <div style="width:44px;height:44px;border-radius:12px;background:var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="1.8"><?= $icon ?></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:14px;color:var(--dark)"><?= ucfirst(e($c['type'])) ?></div>
      <div style="font-size:11px;color:var(--sub);font-family:monospace;margin-top:2px"><?= e($c['numero']) ?></div>
      <div style="font-size:11px;color:var(--sub2);margin-top:2px"><?= e($c['devise'] ?? 'EUR') ?> · <span style="color:<?= $statut_color ?>"><?= ucfirst(e($c['statut'] ?? 'actif')) ?></span></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-weight:800;font-size:16px;color:var(--dark)"><?= number_format((float)$c['solde'], 2, ',', ' ') ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= e($c['devise'] ?? 'EUR') ?></div>
    </div>
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
