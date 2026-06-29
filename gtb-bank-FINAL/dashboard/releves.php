<?php
require_once __DIR__ . '/../backend/auth_required.php';

// ── Export PDF (impression) ──────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === '1') {
    $compte_id = (int)($_GET['compte_id'] ?? 0);
    $compte = DB::one("SELECT * FROM comptes WHERE id=:id AND user_id=:uid", ['id'=>$compte_id,'uid'=>Session::userId()]);
    if ($compte) {
        $txs = DB::all("SELECT * FROM transactions WHERE compte_id=:id ORDER BY cree_le DESC LIMIT 50", ['id'=>$compte_id]);
        $nom = htmlspecialchars(
            ($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''),
            ENT_QUOTES, 'UTF-8'
        );
        ?><!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"/>
<title>Relevé — <?= htmlspecialchars($compte['numero'], ENT_QUOTES, 'UTF-8') ?></title>
<style>
@media print{body{margin:0}}
body{font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;padding:2cm}
.head{display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid #D4AF37}
.logo-mark{width:42px;height:42px;background:#D4AF37;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:#fff}
h1{font-size:1.2rem;margin:0;color:#0D1B2A}
.meta{font-size:.82rem;color:#555;margin-bottom:1.5rem}
table{width:100%;border-collapse:collapse}
thead th{background:#0D1B2A;color:#fff;padding:.5rem .75rem;text-align:left;font-size:.78rem}
tbody td{padding:.45rem .75rem;border-bottom:1px solid #eee;font-size:.82rem}
.pos{color:#00855a;font-weight:700}.neg{color:#c0392b;font-weight:700}
.footer{margin-top:2rem;font-size:.72rem;color:#888;border-top:1px solid #ddd;padding-top:.75rem;text-align:center}
</style></head><body>
<div class="head">
  <div class="logo-mark">GTB</div>
  <div><h1>Global Trust Bank</h1><div style="font-size:.75rem;color:#888">Relevé de compte officiel</div></div>
</div>
<div class="meta">
  <strong>Compte :</strong> <?= htmlspecialchars(ucfirst($compte['type']) . ' — ' . $compte['numero'], ENT_QUOTES, 'UTF-8') ?><br>
  <strong>Titulaire :</strong> <?= $nom ?><br>
  <strong>Solde actuel :</strong> <?= number_format((float)$compte['solde'], 2, ',', ' ') . ' ' . $compte['devise'] ?><br>
  <strong>Édité le :</strong> <?= date('d/m/Y H:i') ?> — 50 dernières opérations
</div>
<table>
  <thead><tr><th>Date</th><th>Description</th><th>Référence</th><th>Montant</th><th>Solde après</th></tr></thead>
  <tbody>
  <?php foreach ($txs as $t):
    $pos = in_array($t['type'], ['depot','virement_in','credit'], true);
  ?>
  <tr>
    <td><?= date('d/m/Y H:i', strtotime($t['cree_le'] ?? 'now')) ?></td>
    <td><?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
    <td style="font-family:monospace;font-size:.72rem"><?= htmlspecialchars($t['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
    <td class="<?= $pos ? 'pos' : 'neg' ?>"><?= ($pos ? '+' : '−') . number_format(abs((float)$t['montant']), 2, ',', ' ') . ' ' . $compte['devise'] ?></td>
    <td><?= number_format((float)($t['solde_apres'] ?? 0), 2, ',', ' ') . ' ' . $compte['devise'] ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<div class="footer">Global Trust Bank — Document non contractuel généré automatiquement. <?= date('d/m/Y') ?></div>
<script>window.print();</script>
</body></html><?php
    }
    exit;
}

// ── Page principale ──────────────────────────────────────────────────────────
$pageTitle   = 'Mes relevés';
$navActive   = 'home';
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0", ['id'=>Session::userId()]);
$comptes     = DB::all("SELECT * FROM comptes WHERE user_id=:id ORDER BY type", ['id'=>Session::userId()]);

$mois_noms = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
              '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];

require __DIR__ . '/includes/header.php';
?>

<div class="gtb-section-head">
  <div>
    <h1 class="gtb-page-title">Mes relevés</h1>
    <p class="gtb-page-sub">Téléchargez vos relevés mensuels</p>
  </div>
</div>

<?php if (empty($comptes)): ?>
<div class="gtb-card" style="text-align:center;padding:48px 20px">
  <p style="color:var(--sub);font-size:14px">Aucun compte trouvé.</p>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
  <?php foreach ($comptes as $c):
    $mois = [];
    for ($i = 0; $i < 12; $i++) { $mois[] = date('Y-m', strtotime("-{$i} months")); }
  ?>
  <div class="gtb-card">
    <!-- En-tête compte -->
    <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--border)">
      <div style="width:40px;height:40px;border-radius:10px;background:var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--gold)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <div>
        <div style="font-weight:700;font-size:14px;color:var(--dark)"><?= ucfirst(e($c['type'])) ?></div>
        <div style="font-size:11px;color:var(--sub);font-family:monospace"><?= e(substr($c['numero'], -10)) ?></div>
      </div>
    </div>
    <!-- Liste mois -->
    <div style="padding:8px 0">
      <?php foreach ($mois as $m):
        [$y, $mo] = explode('-', $m);
        $label = ($mois_noms[$mo] ?? $mo) . ' ' . $y;
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 20px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:10px">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--sub2)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <span style="font-size:13px;font-weight:500"><?= $label ?></span>
        </div>
        <a href="?export=1&compte_id=<?= (int)$c['id'] ?>" target="_blank" class="gtb-btn gtb-btn-outline gtb-btn-sm" style="font-size:11px;gap:5px">
          <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          PDF
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
