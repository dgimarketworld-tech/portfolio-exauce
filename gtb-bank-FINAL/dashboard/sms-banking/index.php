<?php
require_once __DIR__ . '/../../backend/auth_required.php';
require_once __DIR__ . '/../../backend/helpers.php';

$pageTitle   = 'SMS Banking';
$navActive   = 'home';
$userId      = Session::userId();
$u           = $currentUser;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>$userId]);
$csrf        = Security::csrfToken();

DB::run("CREATE TABLE IF NOT EXISTS sms_banking (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL UNIQUE,
  telephone  VARCHAR(20) NOT NULL,
  pin        VARCHAR(10) DEFAULT '0000',
  is_active  TINYINT(1) DEFAULT 0,
  alert_debit  TINYINT(1) DEFAULT 1,
  alert_credit TINYINT(1) DEFAULT 1,
  alert_min    DECIMAL(10,2) DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
DB::run("CREATE TABLE IF NOT EXISTS sms_banking_logs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  direction  ENUM('in','out') DEFAULT 'out',
  message    TEXT,
  status     VARCHAR(20) DEFAULT 'sent',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$sms  = DB::one("SELECT * FROM sms_banking WHERE user_id=:id", ['id'=>$userId]);
$logs = DB::all("SELECT * FROM sms_banking_logs WHERE user_id=:id ORDER BY created_at DESC LIMIT 20", ['id'=>$userId]);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::csrfCheck($_POST['_csrf'] ?? '')) {
    $action = $_POST['_action'] ?? '';
    if ($action === 'activate') {
        $tel = preg_replace('/\D/', '', $_POST['telephone'] ?? '');
        $pin = preg_replace('/\D/', '', $_POST['pin'] ?? '');
        if (strlen($tel) < 8) { $error = 'Numéro de téléphone invalide.'; }
        elseif (strlen($pin) !== 4) { $error = 'Le code PIN doit avoir exactement 4 chiffres.'; }
        else {
            if ($sms) {
                DB::update("UPDATE sms_banking SET telephone=:t,pin=:p,is_active=1,updated_at=NOW() WHERE user_id=:id", ['t'=>$tel,'p'=>$pin,'id'=>$userId]);
            } else {
                DB::insertInto('sms_banking', ['user_id'=>$userId,'telephone'=>$tel,'pin'=>$pin,'is_active'=>1]);
            }
            $sms     = DB::one("SELECT * FROM sms_banking WHERE user_id=:id", ['id'=>$userId]);
            $success = 'SMS Banking activé sur le +'.$tel;
            notify($userId, 'SMS Banking activé', 'Votre service SMS Banking est maintenant actif.', 'success');
        }
    } elseif ($action === 'deactivate') {
        DB::update("UPDATE sms_banking SET is_active=0 WHERE user_id=:id", ['id'=>$userId]);
        $sms     = DB::one("SELECT * FROM sms_banking WHERE user_id=:id", ['id'=>$userId]);
        $success = 'Service SMS Banking désactivé.';
    } elseif ($action === 'update_alerts') {
        $ad = isset($_POST['alert_debit']) ? 1 : 0;
        $ac = isset($_POST['alert_credit']) ? 1 : 0;
        $am = max(0, (float)($_POST['alert_min'] ?? 0));
        DB::update("UPDATE sms_banking SET alert_debit=:d,alert_credit=:c,alert_min=:m WHERE user_id=:id", ['d'=>$ad,'c'=>$ac,'m'=>$am,'id'=>$userId]);
        $sms     = DB::one("SELECT * FROM sms_banking WHERE user_id=:id", ['id'=>$userId]);
        $success = 'Alertes SMS mises à jour.';
    } elseif ($action === 'change_pin') {
        $p1 = preg_replace('/\D/', '', $_POST['new_pin'] ?? '');
        $p2 = preg_replace('/\D/', '', $_POST['confirm_pin'] ?? '');
        if (strlen($p1) !== 4) { $error = 'Le PIN doit contenir 4 chiffres.'; }
        elseif ($p1 !== $p2)   { $error = 'Les deux PIN ne correspondent pas.'; }
        else {
            DB::update("UPDATE sms_banking SET pin=:p WHERE user_id=:id", ['p'=>$p1,'id'=>$userId]);
            $sms     = DB::one("SELECT * FROM sms_banking WHERE user_id=:id", ['id'=>$userId]);
            $success = 'Code PIN SMS mis à jour.';
        }
    }
    $logs = DB::all("SELECT * FROM sms_banking_logs WHERE user_id=:id ORDER BY created_at DESC LIMIT 20", ['id'=>$userId]);
}

if (!defined('SMS_BANKING_NUMBER')) define('SMS_BANKING_NUMBER', '+33 7 57 00 0001');
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">SMS Banking</h1>
    <p class="gtb-page-sub">Gérez vos comptes par SMS</p>
  </div>
</div>

<?php if ($success): ?><div style="background:var(--green-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--green)"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>

<!-- Statut -->
<div class="gtb-card" style="padding:18px 20px;margin-bottom:12px;display:flex;align-items:center;gap:14px">
  <div style="width:44px;height:44px;border-radius:12px;background:<?= ($sms && $sms['is_active']) ? 'var(--green-light)' : 'var(--border)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="<?= ($sms && $sms['is_active']) ? 'var(--green)' : 'var(--sub2)' ?>" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
  </div>
  <div style="flex:1">
    <div style="font-size:13px;font-weight:700;color:var(--dark)">SMS Banking</div>
    <div style="font-size:11px;color:var(--sub)"><?= ($sms && $sms['is_active']) ? 'Actif sur le +'.(e($sms['telephone'])) : 'Service désactivé' ?></div>
  </div>
  <span style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:<?= ($sms && $sms['is_active']) ? 'var(--green-light)' : 'var(--red-light)' ?>;color:<?= ($sms && $sms['is_active']) ? 'var(--green)' : 'var(--red)' ?>"><?= ($sms && $sms['is_active']) ? 'Actif' : 'Inactif' ?></span>
</div>

<!-- Activation / désactivation -->
<?php if (!$sms || !$sms['is_active']): ?>
<div class="gtb-card" style="padding:20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px">Activer le service</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_action" value="activate"/>
    <div style="margin-bottom:12px">
      <label class="gtb-label">Numéro de téléphone</label>
      <input class="gtb-input" type="tel" name="telephone" value="<?= e($sms['telephone'] ?? $u['telephone'] ?? '') ?>" placeholder="+33 6 …" required/>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Code PIN (4 chiffres)</label>
      <input class="gtb-input" type="password" name="pin" maxlength="4" pattern="\d{4}" placeholder="••••" required/>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Activer</button>
  </form>
</div>
<?php else: ?>
<div class="gtb-card" style="padding:18px 20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:12px">Alertes SMS</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_action" value="update_alerts"/>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
      <span style="font-size:13px;color:var(--dark)">Alertes débits</span>
      <input type="checkbox" name="alert_debit" value="1" <?= ($sms['alert_debit']??1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--gold)"/>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
      <span style="font-size:13px;color:var(--dark)">Alertes crédits</span>
      <input type="checkbox" name="alert_credit" value="1" <?= ($sms['alert_credit']??1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--gold)"/>
    </div>
    <div style="padding:10px 0;margin-bottom:12px">
      <label class="gtb-label">Montant minimum d'alerte (€)</label>
      <input class="gtb-input" type="number" name="alert_min" value="<?= (float)($sms['alert_min']??0) ?>" step="10" min="0"/>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm">Enregistrer</button>
  </form>
</div>
<div class="gtb-card" style="padding:18px 20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:12px">Changer le PIN</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_action" value="change_pin"/>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
      <div><label class="gtb-label">Nouveau PIN</label><input class="gtb-input" type="password" name="new_pin" maxlength="4" pattern="\d{4}" placeholder="••••" required/></div>
      <div><label class="gtb-label">Confirmer</label><input class="gtb-input" type="password" name="confirm_pin" maxlength="4" pattern="\d{4}" placeholder="••••" required/></div>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm">Changer le PIN</button>
  </form>
</div>
<div class="gtb-card" style="padding:14px 20px;margin-bottom:12px">
  <form method="POST" style="display:flex;align-items:center;justify-content:space-between">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <input type="hidden" name="_action" value="deactivate"/>
    <span style="font-size:13px;color:var(--sub)">Désactiver le service SMS Banking</span>
    <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="color:var(--red);border-color:var(--red)">Désactiver</button>
  </form>
</div>
<?php endif; ?>

<!-- Commandes -->
<div class="gtb-card" style="padding:18px 20px;margin-bottom:12px">
  <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:12px">Commandes SMS disponibles</div>
  <div style="font-size:12px;color:var(--sub);margin-bottom:10px">Envoyez au <?= SMS_BANKING_NUMBER ?></div>
  <?php foreach (['SOLDE'=>'Consulter votre solde','HIST'=>'5 dernières transactions','AIDE'=>'Liste des commandes'] as $cmd=>$desc): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
    <code style="font-size:12px;background:var(--bg);padding:3px 8px;border-radius:6px;color:var(--dark);font-weight:600"><?= $cmd ?></code>
    <span style="font-size:12px;color:var(--sub)"><?= $desc ?></span>
  </div>
  <?php endforeach; ?>
</div>

<!-- Historique -->
<div class="gtb-card">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Historique SMS</div>
  <?php if (empty($logs)): ?>
  <div style="text-align:center;padding:32px 20px;color:var(--sub);font-size:13px">Aucun historique</div>
  <?php else: ?>
  <?php foreach ($logs as $log): ?>
  <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)">
    <span style="display:inline-flex;width:32px;height:32px;border-radius:50%;align-items:center;justify-content:center;font-size:10px;font-weight:700;background:<?= $log['direction']==='out' ? 'var(--gold-light)' : 'var(--green-light)' ?>;color:<?= $log['direction']==='out' ? 'var(--gold)' : 'var(--green)' ?>;flex-shrink:0"><?= $log['direction']==='out' ? '→' : '←' ?></span>
    <div style="flex:1;min-width:0">
      <div style="font-size:12px;color:var(--dark)"><?= e($log['message'] ?? '') ?></div>
      <div style="font-size:10px;color:var(--sub)"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></div>
    </div>
    <span style="font-size:10px;color:var(--sub2)"><?= e($log['status'] ?? '') ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
