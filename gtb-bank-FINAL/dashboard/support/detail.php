<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Ticket support';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$id      = (int)($_GET['id'] ?? 0);
$ticket  = DB::one("SELECT t.*,a.first_name as c_fn,a.last_name as c_ln FROM tickets t LEFT JOIN admins a ON t.conseiller_id=a.id WHERE t.id=:id AND t.user_id=:uid", ['id'=>$id,'uid'=>Session::userId()]);
if (!$ticket) { header('Location: index.php'); exit; }

$messages = DB::all("SELECT * FROM support_messages WHERE ticket_id=:id ORDER BY cree_le ASC", ['id'=>$id]);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $msg = trim($_POST['message'] ?? '');
    if ($msg && strlen($msg) >= 3) {
        DB::insertInto('support_messages', ['ticket_id'=>$id,'auteur_type'=>'client','auteur_id'=>Session::userId(),'message'=>$msg]);
        if ($ticket['statut'] === 'resolu') DB::update("UPDATE tickets SET statut='en_cours',mis_a_jour=NOW() WHERE id=:id", ['id'=>$id]);
        header("Location: detail.php?id=$id"); exit;
    }
}
DB::update("UPDATE support_messages SET lu=1 WHERE ticket_id=:id AND auteur_type='conseiller' AND lu=0", ['id'=>$id]);
$csrf = csrf_token();

$sc_map = ['ouvert'=>'gold','en_cours'=>'gold','resolu'=>'green','ferme'=>'sub'];
$sc = $sc_map[$ticket['statut']] ?? 'sub';
$sl = ucfirst(str_replace('_', ' ', $ticket['statut']));

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <a href="index.php" style="font-size:12px;color:var(--sub);text-decoration:none;display:flex;align-items:center;gap:4px;margin-bottom:6px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Retour
    </a>
    <h1 class="gtb-page-title">#<?= e($ticket['reference']) ?></h1>
    <p class="gtb-page-sub"><?= e($ticket['sujet']) ?></p>
  </div>
  <span style="display:inline-flex;align-items:center;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--<?= $sc ?>-light,rgba(0,0,0,.06));color:var(--<?= $sc ?>)"><?= $sl ?></span>
</div>

<!-- Infos ticket -->
<div class="gtb-card" style="padding:14px 20px;margin-bottom:12px;display:flex;gap:20px;flex-wrap:wrap">
  <div><div style="font-size:10px;color:var(--sub);margin-bottom:2px">Catégorie</div><div style="font-size:12px;font-weight:600"><?= ucfirst(e($ticket['categorie'])) ?></div></div>
  <div><div style="font-size:10px;color:var(--sub);margin-bottom:2px">Priorité</div><div style="font-size:12px;font-weight:600"><?= ucfirst(e($ticket['priorite'])) ?></div></div>
  <div><div style="font-size:10px;color:var(--sub);margin-bottom:2px">Créé le</div><div style="font-size:12px;font-weight:600"><?= date('d/m/Y H:i', strtotime($ticket['cree_le'])) ?></div></div>
  <div><div style="font-size:10px;color:var(--sub);margin-bottom:2px">Conseiller</div><div style="font-size:12px;font-weight:600"><?= $ticket['c_fn'] ? e($ticket['c_fn'].' '.$ticket['c_ln']) : 'Non assigné' ?></div></div>
</div>

<!-- Conversation -->
<div class="gtb-card" style="margin-bottom:12px">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Conversation</div>
  <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;max-height:400px;overflow-y:auto" id="convo">
    <?php foreach ($messages as $msg):
      $is_client = $msg['auteur_type'] === 'client';
    ?>
    <div style="display:flex;flex-direction:column;align-items:<?= $is_client ? 'flex-end' : 'flex-start' ?>">
      <div style="max-width:85%;padding:10px 14px;border-radius:<?= $is_client ? '16px 16px 4px 16px' : '16px 16px 16px 4px' ?>;font-size:13px;line-height:1.6;background:<?= $is_client ? 'var(--dark)' : 'var(--bg)' ?>;color:<?= $is_client ? '#fff' : 'var(--dark)' ?>;border:<?= $is_client ? 'none' : '1px solid var(--border)' ?>">
        <?= nl2br(e($msg['message'])) ?>
      </div>
      <div style="font-size:10px;color:var(--sub);margin-top:3px">
        <?= $is_client ? 'Vous' : e(($ticket['c_fn'] ?? 'Conseiller').' '.($ticket['c_ln'] ?? '')) ?> · <?= time_ago($msg['cree_le']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Répondre -->
<?php if ($ticket['statut'] !== 'ferme'): ?>
<div class="gtb-card" style="padding:20px">
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>
    <label class="gtb-label">Votre réponse</label>
    <textarea class="gtb-input" name="message" placeholder="Écrivez votre message…" rows="3" required style="resize:vertical;height:auto;margin-bottom:12px"></textarea>
    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Envoyer</button>
  </form>
</div>
<?php else: ?>
<div class="gtb-card" style="padding:16px 20px;text-align:center;color:var(--sub);font-size:13px">Ce ticket est fermé.</div>
<?php endif; ?>

<script>
const c = document.getElementById('convo');
if (c) c.scrollTop = c.scrollHeight;
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
