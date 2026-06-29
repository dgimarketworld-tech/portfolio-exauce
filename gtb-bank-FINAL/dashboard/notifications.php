<?php
require_once __DIR__ . '/../backend/auth_required.php';

$pageTitle   = 'Notifications';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

// Mark all as read
if (($_GET['action'] ?? '') === 'read_all') {
    DB::update("UPDATE notifications SET is_read=1 WHERE user_id=:id", ['id'=>Session::userId()]);
    header('Location: notifications.php');
    exit;
}

$notifs = DB::all("SELECT * FROM notifications WHERE user_id=:id ORDER BY cree_le DESC LIMIT 50", ['id'=>Session::userId()]);
$unread = array_filter($notifs, fn($n) => !$n['is_read']);

require __DIR__ . '/includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Notifications</h1>
    <p class="gtb-page-sub"><?= count($unread) ?> non lue(s)</p>
  </div>
  <?php if (count($unread) > 0): ?>
  <a href="?action=read_all" class="gtb-btn gtb-btn-outline gtb-btn-sm">Tout marquer lu</a>
  <?php endif; ?>
</div>

<div class="gtb-card">
  <?php if (empty($notifs)): ?>
  <div class="gtb-empty" style="padding:48px 20px;text-align:center">
    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="1.5" style="margin-bottom:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    <p style="font-size:14px;color:var(--sub)">Aucune notification pour le moment</p>
  </div>
  <?php else: ?>
  <?php foreach ($notifs as $n):
    $type_colors = ['success'=>'var(--green-light)','error'=>'var(--red-light)','warning'=>'rgba(251,191,36,.15)'];
    $type_icons  = [
      'success' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      'error'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    ];
    $type_stroke = ['success'=>'var(--green)','error'=>'var(--red)','warning'=>'#D97706'];
    $t = $n['type'] ?? 'info';
    $bg  = $type_colors[$t]  ?? 'var(--blue-light)';
    $ico = $type_icons[$t]   ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    $str = $type_stroke[$t]  ?? 'var(--blue)';
  ?>
  <div class="gtb-list-row" style="<?= !$n['is_read'] ? 'background:rgba(212,175,55,.04);' : '' ?>">
    <div class="gtb-list-icon" style="background:<?= $bg ?>;flex-shrink:0;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="<?= $str ?>" stroke-width="2"><?= $ico ?></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:14px;font-weight:600;color:var(--dark);display:flex;align-items:center;gap:8px">
        <?= e($n['title']) ?>
        <?php if (!$n['is_read']): ?>
        <span style="width:7px;height:7px;border-radius:50%;background:var(--gold);display:inline-block;flex-shrink:0"></span>
        <?php endif; ?>
      </div>
      <div style="font-size:13px;color:var(--sub);margin-top:3px;line-height:1.5"><?= e($n['message']) ?></div>
      <div style="font-size:11px;color:var(--sub2);margin-top:4px"><?= time_ago($n['cree_le']) ?></div>
    </div>
    <?php if ($n['link_url']): ?>
    <a href="<?= e($n['link_url']) ?>" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="flex-shrink:0"><?= e($n['link_label'] ?? 'Voir') ?></a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<style>
.gtb-list-row{display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border)}
.gtb-list-row:last-child{border-bottom:none}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
