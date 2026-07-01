<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Nouvelle demande';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $cat  = trim($_POST['categorie'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $prio = $_POST['priorite'] ?? 'normale';
    if (!$cat || !$sujet || !$desc) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $ref = generate_reference('TK', 4);
        $tid = DB::insertInto('tickets', ['user_id'=>Session::userId(),'reference'=>$ref,'categorie'=>$cat,'sujet'=>$sujet,'priorite'=>$prio,'statut'=>'ouvert']);
        DB::insertInto('support_messages', ['ticket_id'=>$tid,'auteur_type'=>'client','auteur_id'=>Session::userId(),'message'=>$desc]);
        notify(Session::userId(), 'Ticket créé', "Votre demande #$ref a été enregistrée.", 'info');
        send_notification_email(Session::userId(), 'Votre ticket support — GTB Bank', "Votre ticket <strong>#$ref</strong> a été créé. Notre équipe vous répondra sous <strong>24h ouvrées</strong>.");
        $u = DB::one("SELECT COALESCE(prenom,first_name,'') AS prenom, COALESCE(nom,last_name,'') AS nom, email FROM users WHERE id=:id", ['id'=>Session::userId()]);
        $html_adm = "<div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px'><h2>Nouveau ticket support</h2><p><b>Client :</b> {$u['prenom']} {$u['nom']} ({$u['email']})<br><b>Réf :</b> $ref<br><b>Sujet :</b> $sujet<br><b>Catégorie :</b> $cat<br><b>Priorité :</b> $prio</p></div>";
        send_email(MAIL_SUPPORT, 'Admin GTB', "Ticket support #$ref — {$u['prenom']} {$u['nom']}", $html_adm);
        $success = "Votre ticket <strong>#$ref</strong> a été créé. Réponse sous 24h ouvrées.";
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
    <h1 class="gtb-page-title">Nouvelle demande</h1>
  </div>
</div>

<?php if ($success): ?>
<div class="gtb-card" style="text-align:center;padding:40px 20px">
  <div style="width:56px;height:56px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
  </div>
  <div style="font-weight:700;font-size:16px;color:var(--dark);margin-bottom:8px">Demande envoyée !</div>
  <p style="font-size:13px;color:var(--sub);margin-bottom:20px"><?= $success ?></p>
  <div style="display:flex;gap:8px;justify-content:center">
    <a href="index.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">Voir mes tickets</a>
    <a href="../index.php" class="gtb-btn gtb-btn-outline gtb-btn-sm">Accueil</a>
  </div>
</div>
<?php else: ?>
<?php if ($error): ?>
<div style="background:var(--red-light);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--red)"><?= e($error) ?></div>
<?php endif; ?>
<div class="gtb-card" style="padding:20px">
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Catégorie</label>
      <select name="categorie" class="gtb-input" required>
        <option value="">Sélectionnez…</option>
        <option value="transaction">Transaction / Débit inconnu</option>
        <option value="carte">Carte bancaire</option>
        <option value="virement">Virement</option>
        <option value="compte">Compte bancaire</option>
        <option value="credit">Crédit</option>
        <option value="autre">Autre</option>
      </select>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Sujet</label>
      <input class="gtb-input" name="sujet" placeholder="Résumez en une phrase" maxlength="180" required/>
    </div>
    <div style="margin-bottom:16px">
      <label class="gtb-label">Description détaillée</label>
      <textarea class="gtb-input" name="description" placeholder="Date, montant, circonstances précises…" rows="5" required style="resize:vertical;height:auto"></textarea>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
      <div>
        <label class="gtb-label">Priorité</label>
        <select name="priorite" class="gtb-input">
          <option value="normale">Normale</option>
          <option value="urgente">Urgente</option>
          <option value="basse">Basse</option>
        </select>
      </div>
      <div>
        <label class="gtb-label">Canal de réponse</label>
        <select name="canal" class="gtb-input">
          <option>Email</option><option>SMS</option>
        </select>
      </div>
    </div>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Envoyer ma demande</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
