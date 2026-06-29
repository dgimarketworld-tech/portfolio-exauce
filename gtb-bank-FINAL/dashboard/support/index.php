<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Support client';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
$tickets     = DB::all("SELECT t.*,a.first_name as c_fn,a.last_name as c_ln FROM tickets t LEFT JOIN admins a ON t.conseiller_id=a.id WHERE t.user_id=:id ORDER BY t.cree_le DESC", ['id'=>Session::userId()]);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Support client</h1>
    <p class="gtb-page-sub">Nous répondons sous 24h ouvrées</p>
  </div>
  <a href="nouveau.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Nouvelle demande</a>
</div>

<!-- Canaux de contact -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
  <div class="gtb-card" style="text-align:center;padding:16px;cursor:pointer" onclick="alert('0800 GTB BANK')">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="1.8" style="margin-bottom:8px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
    <div style="font-weight:700;font-size:12px;color:var(--dark)">Téléphone</div>
    <div style="font-size:10px;color:var(--sub)">0800 GTB BANK</div>
  </div>
  <div class="gtb-card" style="text-align:center;padding:16px;cursor:pointer" onclick="alert('Chat en cours — ~2 min')">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="1.8" style="margin-bottom:8px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <div style="font-weight:700;font-size:12px;color:var(--dark)">Chat</div>
    <div style="font-size:10px;color:var(--green)">● En ligne</div>
  </div>
  <a href="nouveau.php" class="gtb-card" style="text-align:center;padding:16px;text-decoration:none">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="1.8" style="margin-bottom:8px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <div style="font-weight:700;font-size:12px;color:var(--dark)">Ticket</div>
    <div style="font-size:10px;color:var(--sub)">Réponse &lt;24h</div>
  </a>
</div>

<!-- Liste des tickets -->
<div class="gtb-card">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--dark)">Mes demandes (<?= count($tickets) ?>)</div>
  <?php if (empty($tickets)): ?>
  <div style="text-align:center;padding:40px 20px;color:var(--sub);font-size:13px">
    Aucun ticket — <a href="nouveau.php" style="color:var(--gold);font-weight:600">Créer une demande</a>
  </div>
  <?php else: ?>
  <?php foreach ($tickets as $t):
    $sc_map = ['ouvert'=>'gold','en_cours'=>'gold','resolu'=>'green','ferme'=>'sub'];
    $sc = $sc_map[$t['statut']] ?? 'sub';
    $sl = ucfirst(str_replace('_', ' ', $t['statut']));
  ?>
  <a href="detail.php?id=<?= (int)$t['id'] ?>" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit">
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--dark);margin-bottom:3px">#<?= e($t['reference']) ?> — <?= e($t['sujet']) ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= date('d/m/Y', strtotime($t['cree_le'])) ?> · <?= ucfirst(e($t['categorie'])) ?> · <?= $t['c_fn'] ? e($t['c_fn'].' '.$t['c_ln']) : 'Non assigné' ?></div>
    </div>
    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:10px;font-weight:600;background:var(--<?= $sc ?>-light,rgba(0,0,0,.05));color:var(--<?= $sc ?>);flex-shrink:0"><?= $sl ?></span>
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
