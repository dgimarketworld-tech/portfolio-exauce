<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Souscription assurance';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$type_param = htmlspecialchars($_GET['type'] ?? 'habitation', ENT_QUOTES, 'UTF-8');
$types = ['habitation'=>'Assurance Habitation','auto'=>'Assurance Auto','vie'=>'Assurance Vie','sante'=>'Assurance Santé','mobile'=>'Assurance Mobile','voyage'=>'Assurance Voyage'];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $uid  = Session::userId();
    $type = $_POST['type'] ?? 'habitation';
    DB::insertInto('assurances', [
        'user_id'        => $uid,
        'type'           => $type,
        'numero_contrat' => 'GTB-'.strtoupper($type).'-'.date('Ymd').'-'.rand(1000,9999),
        'compagnie'      => 'GTB Protect',
        'prime_mensuelle'=> 12.00,
        'statut'         => 'en_attente',
        'date_debut'     => date('Y-m-d'),
        'date_fin'       => date('Y-m-d', strtotime('+1 year')),
    ]);
    notify($uid, 'Souscription en cours', "Votre demande d'assurance $type est en cours d'étude.", 'info');
    $success = true;
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
    <h1 class="gtb-page-title">Souscrire une assurance</h1>
  </div>
</div>

<?php if ($success): ?>
<div class="gtb-card" style="text-align:center;padding:40px 20px">
  <div style="width:56px;height:56px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
  </div>
  <div style="font-weight:700;font-size:16px;color:var(--dark);margin-bottom:8px">Demande envoyée !</div>
  <p style="font-size:13px;color:var(--sub);margin-bottom:20px">Votre demande de souscription est en cours d'étude. Vous recevrez une réponse sous 24h.</p>
  <div style="display:flex;gap:8px;justify-content:center">
    <a href="index.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">Mes assurances</a>
    <a href="../index.php" class="gtb-btn gtb-btn-outline gtb-btn-sm">Accueil</a>
  </div>
</div>
<?php else: ?>
<div class="gtb-card" style="padding:20px">
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Type d'assurance</label>
      <select name="type" class="gtb-input">
        <?php foreach ($types as $val => $label): ?>
        <option value="<?= $val ?>" <?= $val === $type_param ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Valeur à assurer (€)</label>
      <input class="gtb-input" type="number" name="valeur" min="0" step="1000" placeholder="250 000"/>
    </div>
    <div style="margin-bottom:20px">
      <label class="gtb-label">Informations complémentaires</label>
      <textarea class="gtb-input" name="info" placeholder="Adresse, immatriculation, etc." rows="3" style="resize:vertical;height:auto"></textarea>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Envoyer ma demande</button>
    <p style="text-align:center;font-size:11px;color:var(--sub);margin-top:10px">Étude de dossier sous 24h. Sans engagement.</p>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
