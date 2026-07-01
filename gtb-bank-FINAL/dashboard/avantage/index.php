<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Mes avantages';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
$plan        = $currentUser['plan'] ?? 'standard';
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes avantages</h1>
    <p class="gtb-page-sub">Statut <?= ucfirst($plan) ?></p>
  </div>
</div>

<!-- Statut card -->
<div class="gtb-balance-card" style="margin-bottom:16px">
  <div style="font-size:12px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Votre statut</div>
  <div style="font-size:1.5rem;font-weight:800"><?= $plan === 'premium' ? '✦ GTB Premium' : 'GTB Standard' ?></div>
  <?php if ($plan !== 'premium'): ?>
  <div style="font-size:12px;opacity:.6;margin-top:6px">Passez Premium pour débloquer tous les avantages exclusifs</div>
  <?php endif; ?>
</div>

<!-- Avantages -->
<div style="display:flex;flex-direction:column;gap:10px">
  <?php foreach ([
    ['Cashback 1,5%','Sur tous vos achats avec la carte Gold','premium'],
    ['Lounges aéroports','Accès gratuit + 1 invité dans 600 salons','premium'],
    ['Hôtels partenaires','-15% dans 2000 hôtels dans le monde','standard'],
    ['Assurance voyage','Couverture mondiale incluse dans votre carte','premium'],
    ['Assistance 24/7','Ligne dédiée avec conseiller personnel','premium'],
    ['Offres partenaires','Réductions exclusives shopping et loisirs','standard'],
  ] as [$nom,$desc,$req]):
    $ok = ($req === 'standard' || $plan === 'premium');
  ?>
  <div class="gtb-card" style="display:flex;align-items:center;gap:14px;padding:14px 20px;<?= !$ok ? 'opacity:.5' : '' ?>">
    <div style="width:40px;height:40px;border-radius:10px;background:var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:700;color:var(--dark)"><?= $nom ?></div>
      <div style="font-size:11px;color:var(--sub)"><?= $desc ?></div>
    </div>
    <?php if ($ok): ?>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--green-light);color:var(--green);flex-shrink:0">Actif</span>
    <?php else: ?>
    <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10px;font-weight:600;background:var(--gold-light);color:var(--gold);flex-shrink:0">Premium</span>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
