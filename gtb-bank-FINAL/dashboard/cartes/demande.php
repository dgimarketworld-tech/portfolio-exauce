<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Demande de carte';
$navActive   = 'cartes';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$success = ''; $error = '';
$comptes = DB::all("SELECT * FROM comptes WHERE user_id=:id AND statut='actif'", ['id'=>Session::userId()]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $type       = $_POST['type'] ?? '';
    $reseau     = $_POST['reseau'] ?? '';
    $compte_id  = (int)($_POST['compte_id'] ?? 0);
    $compte     = DB::one("SELECT * FROM comptes WHERE id=:id AND user_id=:uid", ['id'=>$compte_id,'uid'=>Session::userId()]);
    if (!$type || !$reseau || !$compte) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $expire = date('Y-m-d', strtotime('+3 years'));
        $masque = '**** **** **** '.str_pad((string)rand(1000,9999), 4, '0', STR_PAD_LEFT);
        DB::insertInto('cartes', ['compte_id'=>$compte_id,'numero_masque'=>$masque,'type'=>$type,'reseau'=>$reseau,'expire_le'=>$expire,'statut'=>'verification']);
        notify(Session::userId(), 'Carte en cours', "Votre demande de carte ".strtoupper($type)." est en cours de traitement (3-5 jours ouvrés).", 'info');
        send_notification_email(Session::userId(), 'Demande de carte — GTB Bank', "Votre demande de carte <strong>".strtoupper($type)."</strong> a été enregistrée. Disponible sous <strong>3 à 5 jours ouvrés</strong>.");
        $u = DB::one("SELECT COALESCE(prenom,first_name,'') AS prenom, COALESCE(nom,last_name,'') AS nom, email FROM users WHERE id=:id", ['id'=>Session::userId()]);
        $html_adm = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'><h2>Nouvelle demande de carte</h2><p><b>Client :</b> {$u['prenom']} {$u['nom']} ({$u['email']})<br><b>Type :</b> ".strtoupper($type)." / ".strtoupper($reseau)."<br><b>Date :</b> ".date('d/m/Y H:i')."</p></div>";
        send_email(MAIL_SUPPORT, 'Admin GTB', "Demande carte ".strtoupper($type)." — {$u['prenom']} {$u['nom']}", $html_adm);
        $success = 'Demande enregistrée. Votre carte sera disponible sous 3 à 5 jours ouvrés.';
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
    <h1 class="gtb-page-title">Demander une carte</h1>
  </div>
</div>

<?php if ($success): ?>
<div class="gtb-card" style="text-align:center;padding:40px 20px">
  <div style="width:56px;height:56px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
  </div>
  <div style="font-weight:700;font-size:16px;color:var(--dark);margin-bottom:8px">Demande enregistrée !</div>
  <p style="font-size:13px;color:var(--sub);margin-bottom:20px"><?= e($success) ?></p>
  <a href="index.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">Voir mes cartes</a>
</div>
<?php else: ?>
<?php if ($error): ?><div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:12px;font-size:13px;color:var(--red)"><?= e($error) ?></div><?php endif; ?>

<div class="gtb-card" style="padding:20px">
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>

    <div style="margin-bottom:16px">
      <label class="gtb-label">Compte à associer</label>
      <select name="compte_id" class="gtb-input" required>
        <?php foreach ($comptes as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= ucfirst(e($c['type'])) ?> — <?= e(substr($c['numero'],-8)) ?> (<?= number_format((float)$c['solde'],2,',','') ?> €)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="margin-bottom:16px">
      <label class="gtb-label">Type de carte</label>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
        <?php foreach (['standard'=>'Standard (gratuit)','gold'=>'Gold (3,90€/mois)','infinite'=>'Infinite (9,90€/mois)','business'=>'Business (7,90€/mois)'] as $val => $label): ?>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;border-radius:10px;border:1.5px solid var(--border)">
          <input type="radio" name="type" value="<?= $val ?>" required style="accent-color:var(--gold)"/>
          <span style="font-size:13px;font-weight:500;color:var(--dark)"><?= $label ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <label class="gtb-label">Réseau</label>
      <div style="display:flex;gap:12px;margin-top:8px">
        <?php foreach (['visa'=>'Visa','mastercard'=>'Mastercard'] as $val => $label): ?>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 16px;border-radius:10px;border:1.5px solid var(--border)">
          <input type="radio" name="reseau" value="<?= $val ?>" required style="accent-color:var(--gold)"/>
          <span style="font-size:13px;font-weight:500;color:var(--dark)"><?= $label ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Envoyer ma demande</button>
    <p style="text-align:center;font-size:11px;color:var(--sub);margin-top:10px">Livraison par courrier sécurisé · 3 à 5 jours ouvrés</p>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
