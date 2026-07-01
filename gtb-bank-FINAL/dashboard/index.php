<?php
require_once __DIR__ . '/../backend/auth_required.php';
require_once __DIR__ . '/../backend/iban.php';

$pageTitle   = 'Tableau de bord';
$navActive   = 'home';
$depth       = 0;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$u    = $currentUser;
$fn   = e($u['first_name'] ?? $u['prenom'] ?? 'Client');
$plan = $u['plan'] ?? 'standard';

// Compte principal
$compte_principal = DB::one(
    "SELECT * FROM comptes WHERE user_id=:id AND statut='actif' ORDER BY id ASC LIMIT 1",
    ['id'=>Session::userId()]
);
$iban_raw  = $compte_principal['iban'] ?? '';
$iban_fmt  = $iban_raw ? IBAN::format($iban_raw) : '—';
$iban_mask = $iban_raw ? IBAN::mask($iban_raw)   : '—';
$bic       = e($compte_principal['bic'] ?? GTB_BIC);

// Tous les comptes actifs
$comptes     = DB::all("SELECT * FROM comptes WHERE user_id=:id AND statut='actif' ORDER BY type", ['id'=>Session::userId()]);
$solde_total = array_sum(array_column($comptes, 'solde'));

// Dernières transactions
$transactions = DB::all(
    "SELECT t.*, c.numero AS compte_numero FROM transactions t
     JOIN comptes c ON t.compte_id=c.id
     WHERE c.user_id=:id ORDER BY t.cree_le DESC LIMIT 8",
    ['id'=>Session::userId()]
);

// Carte active
$carte = DB::one(
    "SELECT ca.*, co.numero FROM cartes ca
     JOIN comptes co ON ca.compte_id=co.id
     WHERE co.user_id=:id AND ca.statut='active' LIMIT 1",
    ['id'=>Session::userId()]
);

require __DIR__ . '/includes/header.php';
?>

<!-- GREETING -->
<div style="margin-bottom:16px">
  <p style="font-size:13px;color:var(--sub)">Bonjour,</p>
  <h1 style="font-size:22px;font-weight:700;color:var(--dark)"><?= $fn ?><?php if($plan==='premium'): ?> <span style="font-size:13px;color:var(--gold)">✦ Premium</span><?php endif; ?></h1>
  <p style="font-size:12px;color:var(--sub2)"><?= date('l d F Y') ?></p>
</div>

<!-- HERO — SOLDE TOTAL -->
<div class="gtb-hero" style="margin-bottom:16px">
  <div style="position:relative;z-index:1">
    <p style="font-size:11px;font-weight:600;letter-spacing:.08em;color:rgba(255,255,255,.5);text-transform:uppercase;margin-bottom:6px">SOLDE TOTAL</p>
    <div style="font-size:36px;font-weight:800;color:#fff;letter-spacing:-1px;margin-bottom:12px">
      <?= number_format($solde_total, 2, ',', ' ') ?> <span style="font-size:20px">€</span>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <button onclick="toggleIban()" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:99px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);font-size:12px;font-weight:600;cursor:pointer">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Voir mon IBAN
      </button>
      <a href="virement/index.php" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:99px;background:var(--gold);color:#fff;font-size:12px;font-weight:600">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        Virement
      </a>
    </div>
    <div id="iban-box" style="display:none;margin-top:12px;padding:12px;background:rgba(255,255,255,.08);border-radius:12px;border:1px solid rgba(255,255,255,.15)">
      <p style="font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.08em;margin-bottom:4px">IBAN</p>
      <p id="iban-val" style="font-size:13px;font-family:monospace;color:#fff;letter-spacing:.05em;word-break:break-all"><?= $iban_mask ?></p>
      <p style="font-size:10px;color:rgba(255,255,255,.4);margin-top:6px">BIC : <?= $bic ?></p>
      <button onclick="copyIban('<?= $iban_raw ?>')" style="margin-top:8px;font-size:11px;color:var(--gold);background:none;border:none;cursor:pointer;font-weight:600" id="copy-iban-btn">📋 Copier IBAN</button>
    </div>
  </div>
</div>

<!-- MES COMPTES -->
<div class="gtb-section-head">
  <span style="font-size:15px;font-weight:700;color:var(--dark)">Mes comptes</span>
  <a href="comptes/index.php" class="gtb-btn gtb-btn-ghost gtb-btn-sm">Voir tout</a>
</div>
<div class="gtb-card" style="margin-bottom:16px">
  <?php foreach($comptes as $c): ?>
  <a href="comptes/detail.php?id=<?= (int)$c['id'] ?>" class="gtb-list-item" style="display:flex;align-items:center;text-decoration:none">
    <div class="gtb-list-icon" style="background:<?= $c['type']==='epargne'?'var(--green-light)':'var(--gold-light)' ?>">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="<?= $c['type']==='epargne'?'var(--green)':'var(--gold2)' ?>" stroke-width="2">
        <?php if($c['type']==='epargne'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/>
        <?php else: ?>
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        <?php endif; ?>
      </svg>
    </div>
    <div class="gtb-list-info">
      <div class="gtb-list-title"><?= ucfirst(e($c['type'])) ?></div>
      <div class="gtb-list-sub">•• <?= e(substr($c['numero'],-4)) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0;margin-left:12px">
      <div style="font-size:15px;font-weight:700;color:var(--dark)"><?= number_format((float)$c['solde'],2,',',' ') ?> €</div>
      <?php if(!empty($c['taux_interet']) && (float)$c['taux_interet'] > 0): ?>
      <div style="font-size:11px;color:var(--green)">+<?= $c['taux_interet'] ?>%/an</div>
      <?php endif; ?>
    </div>
    <svg style="margin-left:8px;flex-shrink:0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>
  <?php endforeach; ?>
  <?php if(empty($comptes)): ?>
  <div style="padding:32px;text-align:center;color:var(--sub);font-size:13px">Aucun compte actif</div>
  <?php endif; ?>
  <?php if(!empty($comptes)): ?>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:12px 16px;border-top:1px solid var(--border2)">
    <a href="comptes/index.php" class="gtb-btn gtb-btn-outline" style="border-radius:12px">Détails</a>
    <a href="virement/index.php" class="gtb-btn gtb-btn-dark" style="border-radius:12px">Virement</a>
  </div>
  <?php endif; ?>
</div>

<!-- ACTIONS RAPIDES -->
<div class="gtb-section-head">
  <span style="font-size:15px;font-weight:700;color:var(--dark)">Actions rapides</span>
</div>
<div class="gtb-actions-grid" style="margin-bottom:16px">
  <a href="virement/index.php" class="gtb-action-item">
    <div class="gtb-action-icon" style="background:var(--gold-light)">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--gold2)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
    </div>
    <span class="gtb-action-label">Virement</span>
  </a>
  <a href="cartes/index.php" class="gtb-action-item">
    <div class="gtb-action-icon" style="background:#EEF2FF">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#4F46E5" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
    </div>
    <span class="gtb-action-label">Cartes</span>
  </a>
  <a href="credits/demande.php" class="gtb-action-item">
    <div class="gtb-action-icon" style="background:var(--green-light)">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"/></svg>
    </div>
    <span class="gtb-action-label">Crédit</span>
  </a>
  <a href="support/nouveau.php" class="gtb-action-item">
    <div class="gtb-action-icon" style="background:var(--red-light)">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--red)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </div>
    <span class="gtb-action-label">Support</span>
  </a>
</div>

<!-- DERNIÈRES TRANSACTIONS -->
<div class="gtb-section-head">
  <span style="font-size:15px;font-weight:700;color:var(--dark)">Dernières opérations</span>
  <a href="transactions.php" class="gtb-btn gtb-btn-ghost gtb-btn-sm">Voir tout</a>
</div>
<div class="gtb-card" style="margin-bottom:16px">
  <?php if(empty($transactions)): ?>
  <div style="padding:32px;text-align:center;color:var(--sub);font-size:13px">Aucune transaction récente</div>
  <?php else: ?>
  <?php foreach($transactions as $tx):
    $in = strpos($tx['type'],'_in')!==false || $tx['type']==='depot' || $tx['type']==='credit';
  ?>
  <div class="gtb-list-item">
    <div class="gtb-list-icon" style="background:<?= $in?'var(--green-light)':'var(--red-light)' ?>">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="<?= $in?'var(--green)':'var(--red)' ?>" stroke-width="2.5">
        <?= $in ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>' ?>
      </svg>
    </div>
    <div class="gtb-list-info">
      <div class="gtb-list-title"><?= e($tx['description'] ?? ucfirst(str_replace('_',' ',$tx['type']))) ?></div>
      <div class="gtb-list-sub"><?= date('d/m/Y', strtotime($tx['cree_le'])) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-size:14px;font-weight:700;color:<?= $in?'var(--green)':'var(--red)' ?>">
        <?= ($in?'+':'−').number_format(abs((float)$tx['montant']),2,',',' ') ?> €
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- CARTE BANCAIRE -->
<?php if($carte): ?>
<div class="gtb-section-head">
  <span style="font-size:15px;font-weight:700;color:var(--dark)">Ma carte</span>
  <a href="cartes/index.php" class="gtb-btn gtb-btn-ghost gtb-btn-sm">Gérer</a>
</div>
<div style="background:linear-gradient(135deg,var(--dark) 0%,#1a3352 100%);border-radius:20px;padding:20px;color:#fff;margin-bottom:16px;position:relative;overflow:hidden">
  <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(212,175,55,.1);pointer-events:none"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;position:relative;z-index:1">
    <span style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em"><?= ucfirst(e($carte['type'])) ?></span>
    <span style="font-size:16px;font-weight:800;font-style:italic;color:rgba(255,255,255,.7)"><?= strtoupper(e($carte['reseau'])) ?></span>
  </div>
  <div style="font-family:monospace;font-size:16px;letter-spacing:.2em;color:rgba(255,255,255,.9);margin-bottom:20px;position:relative;z-index:1"><?= e($carte['numero_masque']) ?></div>
  <div style="display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1">
    <div>
      <p style="font-size:9px;color:rgba(255,255,255,.4);letter-spacing:.08em;text-transform:uppercase">Expire</p>
      <p style="font-size:13px;font-weight:600"><?= date('m/y', strtotime($carte['expire_le'])) ?></p>
    </div>
    <span class="gtb-badge gtb-badge-green">● Active</span>
  </div>
</div>
<?php endif; ?>

<script>
let ibanShown = false;
const ibanRaw = '<?= addslashes($iban_raw) ?>';
const ibanFmt = '<?= addslashes($iban_fmt) ?>';
const ibanMask = '<?= addslashes($iban_mask) ?>';

function toggleIban(){
  const box = document.getElementById('iban-box');
  const val = document.getElementById('iban-val');
  ibanShown = !ibanShown;
  box.style.display = ibanShown ? 'block' : 'none';
  if(ibanShown) val.textContent = ibanFmt;
}
function copyIban(iban){
  navigator.clipboard.writeText(iban).then(()=>{
    const btn = document.getElementById('copy-iban-btn');
    btn.textContent = '✓ Copié !';
    setTimeout(()=>{ btn.textContent = '📋 Copier IBAN'; }, 2000);
  });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
