<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mon profil';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $fn  = trim($_POST['first_name'] ?? '');
    $ln  = trim($_POST['last_name'] ?? '');
    $tel = trim($_POST['telephone'] ?? '');
    if (!$fn || !$ln) {
        $error = 'Prénom et nom obligatoires.';
    } else {
        DB::update("UPDATE users SET first_name=:fn,last_name=:ln,telephone=:tel,updated_at=NOW() WHERE id=:id",
            ['fn'=>$fn,'ln'=>$ln,'tel'=>$tel,'id'=>Session::userId()]);
        $_SESSION['user']['first_name'] = $fn;
        $_SESSION['user']['last_name']  = $ln;
        $currentUser = array_merge($currentUser, ['first_name'=>$fn,'last_name'=>$ln]);
        $success = 'Profil mis à jour.';
    }
}
$csrf = csrf_token();
$u = $currentUser;
$initials = strtoupper(substr($u['first_name']??'A',0,1).substr($u['last_name']??'U',0,1));

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mon profil</h1>
    <p class="gtb-page-sub">Informations personnelles</p>
  </div>
</div>

<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>

<!-- Avatar card -->
<div class="gtb-card" style="text-align:center;padding:24px 20px;margin-bottom:12px">
  <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--dark),var(--gold));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;color:#fff;margin:0 auto 12px"><?= $initials ?></div>
  <div style="font-weight:700;font-size:15px;color:var(--dark)"><?= e(($u['first_name']??'').' '.($u['last_name']??'')) ?></div>
  <div style="font-size:12px;color:var(--sub);margin:4px 0"><?= e($u['email']??'') ?></div>
  <?php if ($u['client_number'] ?? null): ?>
  <div style="font-size:11px;color:var(--sub2);font-family:monospace"><?= e($u['client_number']) ?></div>
  <?php endif; ?>
</div>

<!-- Formulaire -->
<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:16px">Modifier mes informations</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
      <div>
        <label class="gtb-label">Prénom</label>
        <input class="gtb-input" name="first_name" value="<?= e($u['first_name']??'') ?>" required/>
      </div>
      <div>
        <label class="gtb-label">Nom</label>
        <input class="gtb-input" name="last_name" value="<?= e($u['last_name']??'') ?>" required/>
      </div>
    </div>
    <div style="margin-bottom:12px">
      <label class="gtb-label">Email</label>
      <input class="gtb-input" value="<?= e($u['email']??'') ?>" disabled style="opacity:.6"/>
    </div>
    <div style="margin-bottom:20px">
      <label class="gtb-label">Téléphone</label>
      <input class="gtb-input" name="telephone" value="<?= e($u['telephone']??'') ?>" placeholder="+33 6 …"/>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Enregistrer</button>
  </form>
</div>

<!-- Liens rapides -->
<div class="gtb-card">
  <a href="mot-de-passe.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
    <span style="font-size:14px;font-weight:500;color:var(--dark)">Changer le mot de passe</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <a href="securite.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    <span style="font-size:14px;font-weight:500;color:var(--dark)">Sécurité &amp; sessions</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <a href="preferences.php" style="display:flex;align-items:center;gap:12px;padding:14px 20px;text-decoration:none;color:inherit">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
    <span style="font-size:14px;font-weight:500;color:var(--dark)">Préférences</span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="margin-left:auto"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
