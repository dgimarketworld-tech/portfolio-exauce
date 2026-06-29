<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Sécurité';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$uid      = Session::userId();
$sessions = DB::all("SELECT * FROM sessions_actives WHERE user_id=:id AND user_type='user' ORDER BY last_activity DESC", ['id'=>$uid]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    if (isset($_POST['toggle_2fa'])) {
        $current  = (int)DB::scalar("SELECT two_fa_enabled FROM users WHERE id=:id", ['id'=>$uid]);
        $new_val  = $current ? 0 : 1;
        DB::pdo()->prepare("UPDATE users SET two_fa_enabled=:v WHERE id=:id")->execute(['v'=>$new_val,'id'=>$uid]);
        redirect('?msg='.($new_val ? '2fa_on' : '2fa_off'));
    }
    $sid = $_POST['session_id'] ?? '';
    if ($sid && $sid !== session_id()) {
        DB::update("DELETE FROM sessions_actives WHERE id=:id AND user_id=:uid", ['id'=>$sid,'uid'=>$uid]);
        redirect('?msg=session_closed');
    }
}
$twofa = (bool)DB::scalar("SELECT two_fa_enabled FROM users WHERE id=:id", ['id'=>$uid]);
$msg   = $_GET['msg'] ?? '';
$csrf  = csrf_token();
$u     = $currentUser;

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <a href="index.php" style="font-size:12px;color:var(--sub);text-decoration:none;display:flex;align-items:center;gap:4px;margin-bottom:6px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Retour
    </a>
    <h1 class="gtb-page-title">Sécurité du compte</h1>
  </div>
</div>

<?php if ($msg === 'session_closed'): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)">Session fermée.</div><?php endif; ?>
<?php if ($msg === '2fa_on'): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)">2FA activé.</div><?php endif; ?>
<?php if ($msg === '2fa_off'): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)">2FA désactivé.</div><?php endif; ?>

<!-- 2FA -->
<div class="gtb-card" style="padding:18px 20px;margin-bottom:12px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;font-weight:700;color:var(--dark)">Double authentification (2FA)</div>
    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:10px;font-weight:600;background:<?= $twofa ? 'var(--green-light)' : 'var(--red-light)' ?>;color:<?= $twofa ? 'var(--green)' : 'var(--red)' ?>"><?= $twofa ? 'Actif' : 'Inactif' ?></span>
  </div>
  <div style="background:var(--bg);border-radius:10px;padding:12px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
    <div>
      <div style="font-size:13px;font-weight:600">SMS — <?= e(substr($u['telephone']??'••••••••', 0, -4).'••••') ?></div>
      <div style="font-size:11px;color:var(--sub)">Méthode principale</div>
    </div>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--green-light);color:var(--green);margin-left:auto">Actif</span>
  </div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="toggle_2fa" value="1"/>
    <button type="submit" class="gtb-btn <?= $twofa ? 'gtb-btn-outline' : 'gtb-btn-primary' ?> gtb-btn-sm"><?= $twofa ? 'Désactiver la 2FA' : 'Activer la 2FA' ?></button>
  </form>
</div>

<!-- Sessions -->
<div class="gtb-card">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Sessions actives</div>

  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);background:rgba(0,198,122,.04)">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <div style="flex:1">
      <div style="font-size:13px;font-weight:600;color:var(--dark)">Session actuelle</div>
      <div style="font-size:11px;color:var(--sub)"><?= substr($_SERVER['HTTP_USER_AGENT']??'Navigateur', 0, 50) ?></div>
    </div>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--green-light);color:var(--green)">Active</span>
  </div>

  <?php foreach ($sessions as $s): if ($s['id'] === session_id()) continue; ?>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
    <div style="flex:1">
      <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= e($s['device_name'] ?? 'Appareil inconnu') ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= time_ago($s['last_activity']) ?> · <?= e($s['ip_address'] ?? 'IP inconnue') ?></div>
    </div>
    <form method="POST" style="display:inline;flex-shrink:0">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
      <input type="hidden" name="session_id" value="<?= e($s['id']) ?>"/>
      <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="color:var(--red);border-color:var(--red)">Fermer</button>
    </form>
  </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
