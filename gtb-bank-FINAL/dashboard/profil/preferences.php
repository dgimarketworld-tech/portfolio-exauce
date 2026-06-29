<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Préférences';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$uid = Session::userId();
try { DB::pdo()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS pref_theme VARCHAR(20) DEFAULT 'light'"); } catch (\Exception $e) {}
try { DB::pdo()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS pref_email_alerts TINYINT(1) DEFAULT 1"); } catch (\Exception $e) {}
try { DB::pdo()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS pref_sms_alerts TINYINT(1) DEFAULT 1"); } catch (\Exception $e) {}
$u = DB::one("SELECT * FROM users WHERE id=:id", ['id'=>$uid]) ?? $currentUser;

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $langue      = $_POST['langue'] ?? 'fr';
    $pref_email  = isset($_POST['pref_email_alerts']) ? 1 : 0;
    $pref_sms    = isset($_POST['pref_sms_alerts']) ? 1 : 0;
    DB::pdo()->prepare("UPDATE users SET language=:l,pref_email_alerts=:e,pref_sms_alerts=:s WHERE id=:id")
        ->execute(['l'=>$langue,'e'=>$pref_email,'s'=>$pref_sms,'id'=>$uid]);
    $u       = DB::one("SELECT * FROM users WHERE id=:id", ['id'=>$uid]) ?? $u;
    $success = 'Préférences enregistrées.';
}
$csrf = csrf_token();
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <a href="index.php" style="font-size:12px;color:var(--sub);text-decoration:none;display:flex;align-items:center;gap:4px;margin-bottom:6px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Retour
    </a>
    <h1 class="gtb-page-title">Préférences</h1>
  </div>
</div>

<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>

<form method="POST">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>

  <div class="gtb-card" style="padding:18px 20px;margin-bottom:12px">
    <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Langue &amp; Région</div>
    <div>
      <label class="gtb-label">Langue de l'interface</label>
      <select name="langue" class="gtb-input">
        <option value="fr" <?= ($u['language']??'fr')==='fr'?'selected':'' ?>>Français</option>
        <option value="en" <?= ($u['language']??'')==='en'?'selected':'' ?>>English</option>
      </select>
    </div>
  </div>

  <div class="gtb-card" style="padding:18px 20px;margin-bottom:16px">
    <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Alertes &amp; Notifications</div>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--dark)">Alertes par email</div>
        <div style="font-size:11px;color:var(--sub)">Transactions, virements, sécurité</div>
      </div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="pref_email_alerts" value="1" <?= ($u['pref_email_alerts']??1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--gold);cursor:pointer"/>
      </label>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0">
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--dark)">Alertes par SMS</div>
        <div style="font-size:11px;color:var(--sub)">Transactions importantes uniquement</div>
      </div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="pref_sms_alerts" value="1" <?= ($u['pref_sms_alerts']??1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--gold);cursor:pointer"/>
      </label>
    </div>
  </div>

  <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Enregistrer</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
