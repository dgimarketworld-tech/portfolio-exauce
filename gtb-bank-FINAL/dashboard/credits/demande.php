<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Demande de crédit';
$navActive   = 'home';
$depth       = 1;
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);

$error = ''; $success = '';
$rates = ['immobilier'=>1.9,'auto'=>3.2,'travaux'=>3.8,'consommation'=>4.5,'professionnel'=>3.5];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['_csrf'] ?? '')) {
    $type    = $_POST['type'] ?? '';
    $montant = (float)($_POST['montant'] ?? 0);
    $duree   = (int)($_POST['duree'] ?? 0);
    $revenus = (float)($_POST['revenus'] ?? 0);
    $charges = (float)($_POST['charges'] ?? 0);
    if (!$type || !isset($rates[$type]) || $montant < 1000 || $duree < 12 || $revenus <= 0) {
        $error = 'Veuillez remplir tous les champs correctement.';
    } else {
        $taux = $rates[$type]; $r = $taux / 100 / 12;
        $mens = $r === 0 ? $montant/$duree : $montant*$r*pow(1+$r,$duree)/(pow(1+$r,$duree)-1);
        $endettement = round(($charges + $mens) / $revenus * 100, 1);
        $statut = $endettement > 45 ? 'refuse' : 'en_etude';
        $ref = generate_reference('CR', 4);
        DB::insertInto('credits', ['user_id'=>Session::userId(),'reference'=>$ref,'type'=>$type,'montant'=>$montant,'mensualite'=>round($mens,2),'taux'=>$taux,'duree_mois'=>$duree,'solde_restant'=>$montant,'statut'=>$statut,'motif_refus'=>$statut==='refuse'?'Taux d\'endettement trop élevé ('.$endettement.'%)':null]);
        notify(Session::userId(), 'Demande de crédit', "Votre dossier #$ref est ".($statut==='refuse'?'refusé':'en cours d\'étude').'.', $statut==='refuse'?'error':'info');
        send_notification_email(Session::userId(), 'Votre demande de crédit — GTB Bank', "Votre dossier crédit <strong>#$ref</strong> est ".($statut==='refuse'?'<span style="color:#dc2626">refusé</span>':'en cours d\'étude. Réponse sous 24h ouvrées').'.');
        $success = $statut === 'en_etude' ? "Dossier #$ref soumis — réponse sous 24h ouvrées." : "Dossier #$ref refusé : taux d'endettement ($endettement%) trop élevé.";
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
    <h1 class="gtb-page-title">Demande de crédit</h1>
  </div>
</div>

<?php if ($success): ?>
<div class="gtb-card" style="text-align:center;padding:40px 20px">
  <?php $ok = strpos($success, 'refusé') === false; ?>
  <div style="width:56px;height:56px;border-radius:50%;background:<?= $ok ? 'var(--green-light)' : 'var(--red-light)' ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="<?= $ok ? 'var(--green)' : 'var(--red)' ?>" stroke-width="2.5">
      <?= $ok ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>' ?>
    </svg>
  </div>
  <div style="font-weight:700;font-size:16px;color:var(--dark);margin-bottom:8px">Dossier <?= $ok ? 'soumis' : 'refusé' ?></div>
  <p style="font-size:13px;color:var(--sub);margin-bottom:20px"><?= e($success) ?></p>
  <div style="display:flex;gap:8px;justify-content:center">
    <a href="index.php" class="gtb-btn gtb-btn-primary gtb-btn-sm">Mes crédits</a>
    <a href="../index.php" class="gtb-btn gtb-btn-outline gtb-btn-sm">Accueil</a>
  </div>
</div>
<?php else: ?>
<?php if ($error): ?>
<div style="background:var(--red-light);border:1px solid rgba(220,38,38,.2);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--red)"><?= e($error) ?></div>
<?php endif; ?>

<div class="gtb-card" style="padding:20px">
  <form method="POST" id="creditForm">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"/>

    <div style="margin-bottom:16px">
      <label class="gtb-label">Type de crédit</label>
      <select name="type" class="gtb-input" required onchange="updateRate(this.value)">
        <option value="">Sélectionnez…</option>
        <option value="immobilier">Immobilier — à partir de 1,9%</option>
        <option value="auto">Auto — à partir de 3,2%</option>
        <option value="travaux">Travaux — à partir de 3,8%</option>
        <option value="consommation">Consommation — à partir de 4,5%</option>
        <option value="professionnel">Professionnel — à partir de 3,5%</option>
      </select>
    </div>

    <div style="margin-bottom:16px">
      <label class="gtb-label">Montant souhaité (€)</label>
      <input class="gtb-input" name="montant" id="f_montant" type="number" min="1000" step="100" placeholder="25 000" required oninput="calcSim()"/>
    </div>

    <div style="margin-bottom:16px">
      <label class="gtb-label">Durée : <strong id="durLbl">60 mois</strong></label>
      <input type="range" name="duree" id="f_duree" min="12" max="300" value="60" style="width:100%;margin-top:8px;accent-color:var(--gold)" oninput="document.getElementById('durLbl').textContent=this.value+' mois';calcSim()"/>
    </div>

    <!-- Simulateur -->
    <div style="background:var(--gold-light);border-radius:12px;padding:16px;margin-bottom:20px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center">
      <div><div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Mensualité</div><div style="font-weight:800;font-size:1rem;color:var(--dark)" id="simMens">—</div></div>
      <div><div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Taux TEG</div><div style="font-weight:700;color:var(--gold)" id="simTaux">—</div></div>
      <div><div style="font-size:10px;color:var(--sub);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Coût total</div><div style="font-weight:700;color:var(--dark)" id="simTotal">—</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div>
        <label class="gtb-label">Revenus nets mensuels (€)</label>
        <input class="gtb-input" name="revenus" type="number" step="100" placeholder="3 000" required/>
      </div>
      <div>
        <label class="gtb-label">Charges mensuelles (€)</label>
        <input class="gtb-input" name="charges" type="number" step="10" placeholder="800"/>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <label class="gtb-label">Situation professionnelle</label>
      <select name="situation" class="gtb-input">
        <option>CDI</option><option>CDD</option><option>Fonctionnaire</option><option>Indépendant</option><option>Retraité</option>
      </select>
    </div>

    <button type="submit" class="gtb-btn gtb-btn-primary" style="width:100%">Envoyer ma demande</button>
    <p style="text-align:center;font-size:11px;color:var(--sub);margin-top:12px">Réponse de principe sous 24h. Sous réserve d'acceptation de dossier.</p>
  </form>
</div>
<?php endif; ?>

<script>
const rates = <?= json_encode($rates) ?>;
let currentRate = 0;
function updateRate(type) { currentRate = rates[type] || 0; calcSim(); }
function calcSim() {
  const m = parseFloat(document.getElementById('f_montant')?.value) || 0;
  const d = parseInt(document.getElementById('f_duree')?.value) || 60;
  if (!m || !currentRate) { ['simMens','simTaux','simTotal'].forEach(id => document.getElementById(id).textContent = '—'); return; }
  const r = currentRate / 100 / 12;
  const mens = r === 0 ? m/d : m*r*Math.pow(1+r,d)/(Math.pow(1+r,d)-1);
  document.getElementById('simMens').textContent = mens.toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
  document.getElementById('simTaux').textContent = currentRate + '%';
  document.getElementById('simTotal').textContent = (mens*d).toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
