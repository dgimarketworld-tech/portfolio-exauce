<?php
require_once __DIR__ . '/../../backend/auth_required.php';
$pageTitle   = 'Simulateur de crédit';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
require __DIR__ . '/../includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Simulateur de crédit</h1>
    <p class="gtb-page-sub">Estimez vos mensualités en temps réel</p>
  </div>
</div>

<div class="gtb-card" style="padding:20px;margin-bottom:16px">
  <div style="margin-bottom:16px">
    <label class="gtb-label">Type de crédit</label>
    <select id="s_type" class="gtb-input" onchange="calcSim()">
      <option value="1.9">Immobilier (1,9%)</option>
      <option value="3.2">Auto (3,2%)</option>
      <option value="3.8">Travaux (3,8%)</option>
      <option value="4.5">Consommation (4,5%)</option>
      <option value="3.5">Professionnel (3,5%)</option>
    </select>
  </div>
  <div style="margin-bottom:16px">
    <label class="gtb-label">Montant : <strong id="montantLbl">10 000 €</strong></label>
    <input type="range" id="s_montant" min="1000" max="500000" step="1000" value="10000" style="width:100%;margin-top:8px;accent-color:var(--gold)" oninput="document.getElementById('montantLbl').textContent=parseInt(this.value).toLocaleString('fr-FR')+' €';calcSim()"/>
  </div>
  <div style="margin-bottom:20px">
    <label class="gtb-label">Durée : <strong id="dureeLbl">60 mois</strong></label>
    <input type="range" id="s_duree" min="12" max="300" step="6" value="60" style="width:100%;margin-top:8px;accent-color:var(--gold)" oninput="document.getElementById('dureeLbl').textContent=this.value+' mois';calcSim()"/>
  </div>
</div>

<div class="gtb-balance-card" style="margin-bottom:16px">
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;text-align:center">
    <div>
      <div style="font-size:11px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Mensualité</div>
      <div style="font-size:1.4rem;font-weight:800" id="r_mens">—</div>
    </div>
    <div>
      <div style="font-size:11px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Intérêts</div>
      <div style="font-size:1.4rem;font-weight:800" id="r_int">—</div>
    </div>
    <div>
      <div style="font-size:11px;opacity:.7;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Coût total</div>
      <div style="font-size:1.4rem;font-weight:800" id="r_total">—</div>
    </div>
  </div>
</div>

<div style="text-align:center">
  <a href="demande.php" class="gtb-btn gtb-btn-primary">Faire une demande</a>
</div>

<script>
function calcSim() {
  const taux = parseFloat(document.getElementById('s_type').value);
  const m = parseInt(document.getElementById('s_montant').value);
  const d = parseInt(document.getElementById('s_duree').value);
  const r = taux / 100 / 12;
  const mens = r === 0 ? m/d : m*r*Math.pow(1+r,d)/(Math.pow(1+r,d)-1);
  const total = mens * d;
  const fmt = v => v.toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
  document.getElementById('r_mens').textContent = fmt(mens);
  document.getElementById('r_int').textContent = fmt(total - m);
  document.getElementById('r_total').textContent = fmt(total);
}
calcSim();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
