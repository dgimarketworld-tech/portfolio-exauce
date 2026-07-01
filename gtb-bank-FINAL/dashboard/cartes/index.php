<?php
require_once __DIR__ . '/../../backend/auth_required.php';

$pageTitle   = 'Mes Cartes';
$navActive   = 'cartes';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

// Traitement POST (bloquer / débloquer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $card_id = (int)($_POST['card_id'] ?? 0);
    if ($card_id && in_array($action, ['block','unblock'])) {
        $statut = $action === 'block' ? 'bloquee' : 'active';
        DB::update("UPDATE cartes SET statut=:s WHERE id=:id AND compte_id IN (SELECT id FROM comptes WHERE user_id=:uid)", ['s'=>$statut,'id'=>$card_id,'uid'=>Session::userId()]);
    }
}

$cartes = DB::all(
    "SELECT ca.*, co.numero AS compte_numero, co.type AS compte_type
     FROM cartes ca JOIN comptes co ON ca.compte_id=co.id
     WHERE co.user_id=:id ORDER BY ca.statut, ca.cree_le DESC",
    ['id'=>Session::userId()]
);

require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes Cartes</h1>
    <p class="gtb-page-sub"><?= count($cartes) ?> carte(s)</p>
  </div>
  <a href="demande.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">+ Demander une carte</a>
</div>

<?php if (empty($cartes)): ?>
<div class="gtb-card" style="text-align:center;padding:48px 24px">
  <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="1.5" style="margin:0 auto 16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
  <p style="font-family:'Sora',sans-serif;font-weight:700;font-size:16px;color:var(--text);margin-bottom:8px">Aucune carte active</p>
  <p style="font-size:14px;color:var(--sub);margin-bottom:20px">Demandez votre première carte bancaire GTB</p>
  <a href="demande.php" class="gtb-btn gtb-btn-primary">Demander une carte</a>
</div>

<?php else: ?>
<div class="gtb-cartes-grid">
  <?php foreach ($cartes as $c):
    $active = $c['statut'] === 'active';
    $gradients = [
      'gold'     => 'linear-gradient(135deg,#B8860B,#FFD700)',
      'infinite' => 'linear-gradient(135deg,#1a1a2e,#16213e)',
      'standard' => 'linear-gradient(135deg,#0D1B2A,#1a3c5e)',
      'visa'     => 'linear-gradient(135deg,#0D1B2A,#1a3c5e)',
    ];
    $grad = $gradients[$c['type']] ?? $gradients['standard'];
  ?>
  <div>
    <!-- Visuel carte -->
    <div style="background:<?= $grad ?>;border-radius:var(--r-xl);padding:22px;color:#fff;margin-bottom:12px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(13,27,42,0.18)">
      <div style="position:absolute;right:-20px;top:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06)"></div>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;position:relative;z-index:1">
        <span style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.5)"><?= strtoupper($c['type']) ?> <?= strtoupper($c['reseau']) ?></span>
        <span class="gtb-badge <?= $active ? 'gtb-badge-green' : 'gtb-badge-red' ?>"><?= $active ? '● Active' : '● Bloquée' ?></span>
      </div>
      <div style="font-family:monospace;font-size:16px;letter-spacing:0.2em;margin-bottom:22px;position:relative;z-index:1">
        <?= e($c['numero_masque']) ?>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1">
        <div style="font-size:11px;color:rgba(255,255,255,0.45)">
          Expire <strong style="color:rgba(255,255,255,0.7)"><?= date('m/y', strtotime($c['expire_le'])) ?></strong>
        </div>
        <?php if ($c['plafond']): ?>
        <div style="font-size:11px;color:rgba(255,255,255,0.45)">
          Plafond <strong style="color:rgba(255,255,255,0.7)"><?= format_money($c['plafond']) ?></strong>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Actions -->
    <div class="gtb-card gtb-card-sm">
      <p style="font-size:12px;color:var(--sub);margin-bottom:12px">
        Compte lié : <?= ucfirst($c['compte_type']) ?> — <?= e(substr($c['compte_numero'],-8)) ?>
      </p>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php if ($active): ?>
        <form method="POST" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"/>
          <input type="hidden" name="card_id" value="<?= (int)$c['id'] ?>"/>
          <input type="hidden" name="action" value="block"/>
          <button type="submit" class="gtb-btn gtb-btn-outline gtb-btn-sm">Bloquer</button>
        </form>
        <button class="gtb-btn gtb-btn-ghost gtb-btn-sm" onclick="alert('Code PIN envoyé par SMS')">Voir PIN</button>
        <button class="gtb-btn gtb-btn-danger gtb-btn-sm" onclick="alert('Opposition enregistrée — contactez le support')">Opposition</button>
        <?php else: ?>
        <form method="POST" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"/>
          <input type="hidden" name="card_id" value="<?= (int)$c['id'] ?>"/>
          <input type="hidden" name="action" value="unblock"/>
          <button type="submit" class="gtb-btn gtb-btn-primary gtb-btn-sm">Débloquer</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.gtb-cartes-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}
@media (min-width: 480px) {
  .gtb-cartes-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
  .gtb-cartes-grid { grid-template-columns: repeat(3, 1fr); }
}
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
