<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Paramètres';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$uid = Session::userId();
DB::pdo()->exec("CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT UNSIGNED PRIMARY KEY,
    plafond_virement DECIMAL(12,2) DEFAULT 5000.00,
    plafond_paiement DECIMAL(12,2) DEFAULT 2000.00,
    notif_email TINYINT(1) DEFAULT 1,
    notif_sms TINYINT(1) DEFAULT 1,
    notif_push TINYINT(1) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");
$settings = DB::one("SELECT * FROM user_settings WHERE user_id=:id", ['id'=>$uid])
    ?? ['plafond_virement'=>5000,'plafond_paiement'=>2000,'notif_email'=>1,'notif_sms'=>1,'notif_push'=>0];

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $tab = $_POST['_tab'] ?? 'notifs';
    if ($tab === 'paiements') {
        $pv = (float)($_POST['plafond_virement'] ?? 5000);
        $pp = (float)($_POST['plafond_paiement'] ?? 2000);
        DB::pdo()->prepare("INSERT INTO user_settings (user_id,plafond_virement,plafond_paiement) VALUES (:uid,:pv,:pp) ON DUPLICATE KEY UPDATE plafond_virement=:pv2,plafond_paiement=:pp2")
            ->execute(['uid'=>$uid,'pv'=>$pv,'pp'=>$pp,'pv2'=>$pv,'pp2'=>$pp]);
        $success = 'Plafonds mis à jour.';
    } elseif ($tab === 'notifs') {
        $ne = isset($_POST['notif_email']) ? 1 : 0;
        $ns = isset($_POST['notif_sms']) ? 1 : 0;
        $np = isset($_POST['notif_push']) ? 1 : 0;
        DB::pdo()->prepare("INSERT INTO user_settings (user_id,notif_email,notif_sms,notif_push) VALUES (:uid,:ne,:ns,:np) ON DUPLICATE KEY UPDATE notif_email=:ne2,notif_sms=:ns2,notif_push=:np2")
            ->execute(['uid'=>$uid,'ne'=>$ne,'ns'=>$ns,'np'=>$np,'ne2'=>$ne,'ns2'=>$ns,'np2'=>$np]);
        $success = 'Notifications mises à jour.';
    }
    $settings = DB::one("SELECT * FROM user_settings WHERE user_id=:id", ['id'=>$uid]) ?? $settings;
}
$csrf = csrf_token();
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Paramètres</h1>
    <p class="gtb-page-sub">Gérez votre compte</p>
  </div>
</div>

<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>

<!-- Liens rapides -->
<div class="gtb-card" style="margin-bottom:12px">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Compte</div>
  <a href="../profil/index.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    <span style="font-size:14px;color:var(--dark)">Profil &amp; Informations</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <a href="../profil/securite.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    <span style="font-size:14px;color:var(--dark)">Sécurité &amp; 2FA</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <a href="../profil/preferences.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
    <span style="font-size:14px;color:var(--dark)">Préférences</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
</div>

<!-- Plafonds -->
<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Plafonds de paiement</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_tab" value="paiements"/>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div>
        <label class="gtb-label">Plafond virement mensuel (€)</label>
        <input class="gtb-input" type="number" name="plafond_virement" value="<?= (float)$settings['plafond_virement'] ?>" step="100" min="0"/>
      </div>
      <div>
        <label class="gtb-label">Plafond paiement quotidien (€)</label>
        <input class="gtb-input" type="number" name="plafond_paiement" value="<?= (float)$settings['plafond_paiement'] ?>" step="100" min="0"/>
      </div>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm">Enregistrer les plafonds</button>
  </form>
</div>

<!-- Notifications -->
<div class="gtb-card" style="padding:20px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Notifications</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_tab" value="notifs"/>
    <?php foreach ([['notif_email','Email','Transactions et alertes importantes'],['notif_sms','SMS','Vérification et alertes critiques'],['notif_push','Push','Notifications dans le navigateur']] as [$name,$label,$sub]): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--dark)"><?= $label ?></div>
        <div style="font-size:11px;color:var(--sub)"><?= $sub ?></div>
      </div>
      <input type="checkbox" name="<?= $name ?>" value="1" <?= ($settings[$name]??0) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--gold);cursor:pointer"/>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm" style="margin-top:14px">Enregistrer les notifications</button>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
