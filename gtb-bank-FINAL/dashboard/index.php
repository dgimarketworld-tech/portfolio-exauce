<?php
require_once __DIR__ . '/../backend/auth_required.php';

$pageTitle  = 'Tableau de bord';
$navActive  = 'home';
$notif_count = DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]) ?: 0;

// Données
$comptes = DB::all("SELECT * FROM comptes WHERE user_id=:id AND statut='actif' ORDER BY type", ['id'=>Session::userId()]);
$solde_total = array_sum(array_column($comptes, 'solde'));

$compte_principal = DB::one("SELECT * FROM comptes WHERE user_id=:id AND type='courant' LIMIT 1", ['id'=>Session::userId()]);

$transactions = DB::all(
    "SELECT t.*, c.numero as compte_numero, c.devise
     FROM transactions t JOIN comptes c ON t.compte_id=c.id
     WHERE c.user_id=:id ORDER BY t.cree_le DESC LIMIT 6",
    ['id'=>Session::userId()]
);

$cartes = DB::all(
    "SELECT ca.*, co.numero FROM cartes ca JOIN comptes co ON ca.compte_id=co.id
     WHERE co.user_id=:id AND ca.statut='active' LIMIT 1",
    ['id'=>Session::userId()]
);

$credits = DB::all("SELECT * FROM credits WHERE user_id=:id AND statut='en_cours' LIMIT 2", ['id'=>Session::userId()]);

$prenom = e($currentUser['first_name'] ?? $currentUser['prenom'] ?? '');

require __DIR__ . '/includes/header.php';
?>

<!-- GREETING -->
<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Bonjour, <?= $prenom ?> 👋</h1>
    <p class="gtb-page-sub"><?= date('l d F Y') ?></p>
  </div>
  <a href="/dashboard/virement/index.php" class="gtb-btn gtb-btn-primary">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
    </svg>
    Virement
  </a>
</div>

<!-- BALANCE CARD -->
<div class="gtb-balance-card">
  <div class="gtb-balance-label">Solde total</div>
  <div class="gtb-balance-amount"><?= format_money($solde_total) ?></div>
  <div class="gtb-balance-row">
    <?php foreach ($comptes as $c): ?>
    <div class="gtb-balance-account">
      <span><?= ucfirst($c['type']) ?></span>
      <strong><?= format_money($c['solde'], $c['devise']) ?></strong>
    </div>
    <?php endforeach; ?>
    <?php if (empty($comptes)): ?>
    <div class="gtb-balance-account"><span>Aucun compte actif</span></div>
    <?php endif; ?>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div class="gtb-qa-grid">
  <a href="/dashboard/virement/index.php" class="gtb-qa-item">
    <div class="gtb-qa-icon" style="background:#EFF6FF">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#1A73E8" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
      </svg>
    </div>
    <span>Virement</span>
  </a>
  <a href="/dashboard/cartes/index.php" class="gtb-qa-item">
    <div class="gtb-qa-icon" style="background:#FFF8E1">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#D4AF37" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
      </svg>
    </div>
    <span>Cartes</span>
  </a>
  <a href="/dashboard/credits/demande.php" class="gtb-qa-item">
    <div class="gtb-qa-icon" style="background:#F0FDF4">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#00C67A" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <span>Crédit</span>
  </a>
  <a href="/dashboard/support/nouveau.php" class="gtb-qa-item">
    <div class="gtb-qa-icon" style="background:#F5F0FF">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
      </svg>
    </div>
    <span>Support</span>
  </a>
</div>

<!-- RECENT TRANSACTIONS -->
<div class="gtb-card" style="margin-top:1.25rem">
  <div class="gtb-card-head">
    <div class="gtb-card-title">Dernières opérations</div>
    <a href="/dashboard/transactions.php" class="gtb-link">Voir tout</a>
  </div>
  <?php if (empty($transactions)): ?>
  <div class="gtb-empty">Aucune transaction pour le moment</div>
  <?php else: ?>
  <ul class="gtb-list">
    <?php foreach ($transactions as $tx):
      $isIn = (strpos($tx['type'],'in') !== false || $tx['type'] === 'depot');
      $label = e($tx['description'] ?? ucfirst(str_replace('_', ' ', $tx['type'])));
    ?>
    <li class="gtb-list-item">
      <div class="gtb-list-icon" style="background:<?= $isIn ? '#F0FDF4' : '#FFF1F2' ?>">
        <?php if ($isIn): ?>
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#00C67A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
        <?php else: ?>
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#E5373A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        <?php endif; ?>
      </div>
      <div class="gtb-list-info">
        <div class="gtb-list-title"><?= $label ?></div>
        <div class="gtb-list-sub"><?= format_datetime($tx['cree_le']) ?></div>
      </div>
      <div class="gtb-list-amount <?= $isIn ? 'pos' : 'neg' ?>">
        <?= ($isIn ? '+' : '-') . format_money($tx['montant']) ?>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>

<!-- CARTE + CREDITS -->
<div class="gtb-two-col" style="margin-top:1.25rem">
  <!-- Carte -->
  <div class="gtb-card">
    <div class="gtb-card-head">
      <div class="gtb-card-title">Ma carte</div>
      <a href="/dashboard/cartes/index.php" class="gtb-link">Gérer</a>
    </div>
    <?php if (!empty($cartes)): $c = $cartes[0]; ?>
    <div class="gtb-bank-card">
      <div class="gtb-bank-card-top">
        <span class="gtb-bank-card-type"><?= ucfirst($c['type']) ?></span>
        <span class="gtb-bank-card-network"><?= strtoupper($c['reseau']) ?></span>
      </div>
      <div class="gtb-bank-card-num"><?= e($c['numero_masque']) ?></div>
      <div class="gtb-bank-card-bottom">
        <span>Expire <?= date('m/y', strtotime($c['expire_le'])) ?></span>
        <span class="gtb-badge gtb-badge-green">● Active</span>
      </div>
    </div>
    <?php else: ?>
    <div class="gtb-empty">
      <p>Aucune carte active</p>
      <a href="/dashboard/cartes/demande.php" class="gtb-btn gtb-btn-primary gtb-btn-sm" style="margin-top:.75rem">Demander une carte</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Crédits -->
  <div class="gtb-card">
    <div class="gtb-card-head">
      <div class="gtb-card-title">Crédits en cours</div>
      <a href="/dashboard/credits/index.php" class="gtb-link">Voir tout</a>
    </div>
    <?php if (empty($credits)): ?>
    <div class="gtb-empty">
      <p>Aucun crédit actif</p>
      <a href="/dashboard/credits/demande.php" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="margin-top:.75rem">Faire une demande</a>
    </div>
    <?php else: ?>
    <ul class="gtb-list">
      <?php foreach ($credits as $cr): ?>
      <li class="gtb-list-item">
        <div class="gtb-list-icon" style="background:#FFF8E1">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#D4AF37" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="gtb-list-info">
          <div class="gtb-list-title"><?= ucfirst($cr['type']) ?></div>
          <div class="gtb-list-sub"><?= format_money($cr['mensualite']) ?>/mois</div>
        </div>
        <div class="gtb-list-amount neg"><?= format_money($cr['solde_restant']) ?></div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
