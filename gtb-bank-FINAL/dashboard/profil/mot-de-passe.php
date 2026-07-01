<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mot de passe';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $old     = $_POST['old_pw'] ?? '';
    $new     = $_POST['new_pw'] ?? '';
    $confirm = $_POST['confirm_pw'] ?? '';
    $user    = DB::one("SELECT password_hash FROM users WHERE id=:id", ['id'=>Session::userId()]);
    if (!$user || !password_verify($old, $user['password_hash'])) {
        $error = 'Mot de passe actuel incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'Minimum 8 caractères.';
    } elseif ($new !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($new, PASSWORD_ALGO, PASSWORD_OPTIONS);
        DB::update("UPDATE users SET password_hash=:h,updated_at=NOW() WHERE id=:id", ['h'=>$hash,'id'=>Session::userId()]);
        notify(Session::userId(), 'Mot de passe modifié', 'Votre mot de passe a été changé avec succès.', 'success');
        $success = 'Mot de passe modifié avec succès.';
    }
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
    <h1 class="gtb-page-title">Changer le mot de passe</h1>
  </div>
</div>

<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>

<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Mot de passe actuel</label>
      <input class="gtb-input" type="password" name="old_pw" required/>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Nouveau mot de passe</label>
      <input class="gtb-input" type="password" name="new_pw" id="newPw" oninput="checkPw(this.value)" required/>
      <div style="margin-top:8px">
        <div style="height:4px;background:var(--border);border-radius:99px;overflow:hidden;margin-bottom:4px"><div id="pwBar" style="height:100%;border-radius:99px;width:0;transition:width .4s;background:var(--red)"></div></div>
        <div id="pwLbl" style="font-size:11px;color:var(--sub)">Saisissez un mot de passe</div>
      </div>
    </div>
    <div style="margin-bottom:20px">
      <label class="gtb-label">Confirmer</label>
      <input class="gtb-input" type="password" name="confirm_pw" required/>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Enregistrer</button>
  </form>
</div>

<!-- Critères -->
<div class="gtb-card" style="padding:16px 20px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:12px">Critères requis</div>
  <div style="display:flex;flex-direction:column;gap:8px">
    <div id="cr-len" style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--sub)"><div style="width:8px;height:8px;border-radius:50%;background:var(--border);flex-shrink:0" id="dot-len"></div>Minimum 8 caractères</div>
    <div id="cr-maj" style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--sub)"><div style="width:8px;height:8px;border-radius:50%;background:var(--border);flex-shrink:0" id="dot-maj"></div>Au moins une majuscule</div>
    <div id="cr-num" style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--sub)"><div style="width:8px;height:8px;border-radius:50%;background:var(--border);flex-shrink:0" id="dot-num"></div>Au moins un chiffre</div>
    <div id="cr-spe" style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--sub)"><div style="width:8px;height:8px;border-radius:50%;background:var(--border);flex-shrink:0" id="dot-spe"></div>Un caractère spécial</div>
  </div>
</div>

<script>
function checkPw(v) {
  const c = [v.length>=8, /[A-Z]/.test(v), /[0-9]/.test(v), /[^A-Za-z0-9]/.test(v)];
  const score = c.filter(Boolean).length;
  const bar = document.getElementById('pwBar');
  const lbl = document.getElementById('pwLbl');
  const colors = ['var(--red)','#f97316','#eab308','var(--green)'];
  const labels = ['Trop faible','Faible','Moyen','Fort'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score-1] || 'var(--border)';
  lbl.textContent = score > 0 ? labels[score-1] : 'Saisissez un mot de passe';
  ['len','maj','num','spe'].forEach((k,i) => {
    document.getElementById('dot-'+k).style.background = c[i] ? 'var(--green)' : 'var(--border)';
    document.getElementById('cr-'+k).style.color = c[i] ? 'var(--green)' : 'var(--sub)';
  });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
