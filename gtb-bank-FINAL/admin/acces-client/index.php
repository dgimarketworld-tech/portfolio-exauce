<?php
require_once __DIR__ . '/../../backend/admin_required.php';
$adm      = $currentAdmin;
$initials = strtoupper(substr($adm['first_name']??'A',0,1).substr($adm['last_name']??'M',0,1));
$title    = 'Mettre à jour un accès client';
// Compte des virements en attente pour badge
$pending_count = (int) DB::scalar("SELECT COUNT(*) FROM transactions WHERE certification_status='running' AND admin_alerted=0") ?: 0;
?><!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="icon" type="image/png" href="/favicon.png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB Admin — <?= e($title) ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap');
:root{
--admin-bg:#0F172A;--admin-bg-mid:#1E293B;
--accent:#3B82F6;--accent-light:#60A5FA;--accent-deep:#1D4ED8;
--white:#FFFFFF;--off:#F1F5F9;--gray50:#F8FAFC;--gray100:#F1F5F9;--gray200:#E2E8F0;
--gray300:#CBD5E1;--gray400:#94A3B8;--gray500:#64748B;--gray600:#475569;
--gray700:#334155;--gray800:#1E293B;--gray900:#0F172A;
--success:#10B981;--warning:#F59E0B;--danger:#EF4444;--info:#0EA5E9;--purple:#8B5CF6;
--gold:#D4AF37;
--r-sm:6px;--r-md:10px;--r-lg:16px;--r-xl:24px;--r-full:9999px;
--ease:cubic-bezier(.25,.46,.45,.94);
--sidebar-w:248px;--topbar-h:60px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;font-size:15px}
body{font-family:'DM Sans',sans-serif;background:#F1F5F9;color:var(--gray700);overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-thumb{background:var(--gray300);border-radius:99px}
.sidebar{background:var(--admin-bg);position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);display:flex;flex-direction:column;z-index:200;overflow:hidden;border-right:1px solid rgba(255,255,255,.05)}
.sidebar-logo{display:flex;align-items:center;gap:.65rem;padding:1.25rem 1.4rem;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05)}
.sidebar-logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.74rem;color:white;flex-shrink:0}
.sidebar-logo-text .top{font-family:'Sora',sans-serif;font-weight:700;font-size:.84rem;color:white;display:block}
.sidebar-logo-text .bot{font-size:.58rem;color:var(--accent-light);letter-spacing:.1em;text-transform:uppercase;margin-top:3px;font-weight:600;display:block}
.sidebar-user{padding:.85rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:.7rem}
.sidebar-avatar{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0}
.sidebar-user-name{font-size:.8rem;font-weight:600;color:white}
.sidebar-user-role{font-size:.6rem;color:#EF4444;letter-spacing:.08em;text-transform:uppercase;font-weight:700}
.sidebar-nav{flex:1;padding:.75rem 0;overflow-y:auto}
.sidebar-section-label{font-size:.55rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.65rem 1.4rem .25rem}
.sidebar-link{display:flex;align-items:center;gap:.7rem;padding:.55rem 1.4rem;margin:.05rem .6rem;border-radius:8px;text-decoration:none;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;transition:all .2s}
.sidebar-link:hover{color:white;background:rgba(255,255,255,.06)}
.sidebar-link.active{color:white;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25)}
.sidebar-icon{width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;opacity:.7}
.sidebar-badge{margin-left:auto;background:var(--danger);color:white;font-size:.55rem;font-weight:700;padding:.15rem .45rem;border-radius:var(--r-full)}
.sidebar-footer{padding:.85rem 1.4rem;border-top:1px solid rgba(255,255,255,.05)}
.sidebar-footer-link{display:flex;align-items:center;gap:.6rem;font-size:.76rem;color:rgba(255,255,255,.4);text-decoration:none;padding:.4rem 0;transition:color .2s}
.sidebar-footer-link:hover{color:rgba(255,255,255,.85)}
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(255,255,255,.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between;padding:0 1.75rem;z-index:100;gap:1rem;transition:left .35s}
.topbar-page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--gray900)}
.topbar-right{display:flex;align-items:center;gap:.65rem;margin-left:auto}
.topbar-btn{width:36px;height:36px;border-radius:8px;background:var(--gray50);border:1.5px solid var(--gray200);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.95rem;transition:all .2s;color:var(--gray600);position:relative}
.topbar-btn:hover{border-color:var(--accent);color:var(--accent)}
.notif-dot{position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:var(--danger);border:2px solid white}
.dash-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);padding:1.75rem;min-height:calc(100vh - var(--topbar-h))}
.page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:1.4rem;color:var(--gray900);margin-bottom:.25rem}
.page-sub{font-size:.82rem;color:var(--gray400);margin-bottom:1.5rem}

/* ═══ WIZARD CARD ═══ */
.wizard-card{background:white;border-radius:var(--r-xl);border:1.5px solid var(--gray200);overflow:hidden;box-shadow:0 4px 20px rgba(15,23,42,.06)}
.wizard-header{padding:1.25rem 1.75rem;border-bottom:1.5px solid var(--gray100);background:linear-gradient(135deg,var(--admin-bg) 0%,var(--admin-bg-mid) 100%);display:flex;align-items:center;gap:1rem}
.wizard-header-icon{width:44px;height:44px;border-radius:var(--r-md);background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.wizard-header-text h2{font-family:'Sora',sans-serif;font-weight:700;font-size:1rem;color:white}
.wizard-header-text p{font-size:.75rem;color:rgba(255,255,255,.45);margin-top:.1rem}

/* Steps progress bar */
.wizard-steps{display:flex;align-items:center;padding:1.25rem 1.75rem;border-bottom:1.5px solid var(--gray100);gap:0;overflow-x:auto}
.wstep{display:flex;align-items:center;gap:.5rem;flex-shrink:0}
.wstep-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;border:2px solid var(--gray200);color:var(--gray400);transition:all .3s;flex-shrink:0}
.wstep-num.active{background:var(--accent);border-color:var(--accent);color:white;box-shadow:0 4px 12px rgba(59,130,246,.35)}
.wstep-num.done{background:var(--success);border-color:var(--success);color:white}
.wstep-label{font-size:.73rem;color:var(--gray400);font-weight:500;white-space:nowrap}
.wstep-label.active{color:var(--gray800);font-weight:600}
.wstep-label.done{color:var(--success)}
.wstep-line{flex:1;height:2px;background:var(--gray100);min-width:20px;margin:0 .5rem;border-radius:99px;transition:background .3s}
.wstep-line.done{background:var(--success)}

/* Sections du wizard */
.wizard-body{padding:1.75rem}
.wizard-section{display:none}
.wizard-section.visible{display:block}

/* Search client */
.client-search-wrap{position:relative;margin-bottom:1rem}
.client-search-input{width:100%;padding:.85rem 1rem .85rem 2.75rem;border:1.5px solid var(--gray200);border-radius:var(--r-lg);font-size:.9rem;color:var(--gray800);background:var(--gray50);outline:none;transition:all .25s;font-family:'DM Sans',sans-serif}
.client-search-input:focus{border-color:var(--accent);background:white;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.client-search-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);font-size:1rem;pointer-events:none}
.client-results{border:1.5px solid var(--gray200);border-radius:var(--r-lg);overflow:hidden;max-height:320px;overflow-y:auto;display:none}
.client-result-item{padding:.8rem 1rem;cursor:pointer;transition:background .15s;border-bottom:1px solid var(--gray100);display:flex;align-items:center;gap:.75rem}
.client-result-item:last-child{border-bottom:none}
.client-result-item:hover{background:var(--gray50)}
.client-result-avatar{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0}
.client-result-info{flex:1;min-width:0}
.client-result-name{font-weight:600;font-size:.85rem;color:var(--gray800)}
.client-result-meta{font-size:.72rem;color:var(--gray400);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.client-result-status{font-size:.62rem;font-weight:700;padding:.2rem .5rem;border-radius:var(--r-full)}
.status-active{background:rgba(16,185,129,.1);color:var(--success)}
.status-blocked{background:rgba(239,68,68,.1);color:var(--danger)}
.status-suspended{background:rgba(245,158,11,.1);color:var(--warning)}

/* Client sélectionné */
.client-selected-card{display:none;background:var(--gray50);border:1.5px solid var(--gray200);border-radius:var(--r-lg);padding:1rem 1.25rem;margin-bottom:1.25rem;align-items:center;gap:.9rem}
.client-selected-card.visible{display:flex}
.client-selected-iban{font-family:'JetBrains Mono',monospace;font-size:.72rem;color:var(--gray500);margin-top:.15rem}

/* Actions groupées */
.actions-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem;margin-bottom:1.25rem}
.action-group{border:1.5px solid var(--gray200);border-radius:var(--r-lg);overflow:hidden}
.action-group-header{padding:.65rem 1rem;background:var(--gray50);font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:var(--gray500);letter-spacing:.06em;text-transform:uppercase;border-bottom:1px solid var(--gray200)}
.action-item{display:flex;align-items:center;gap:.6rem;padding:.6rem 1rem;cursor:pointer;transition:background .15s;border-bottom:1px solid var(--gray100)}
.action-item:last-child{border-bottom:none}
.action-item:hover{background:rgba(59,130,246,.04)}
.action-item.selected{background:rgba(59,130,246,.08);border-left:3px solid var(--accent)}
.action-item-icon{font-size:.95rem;flex-shrink:0;width:22px;text-align:center}
.action-item-label{font-size:.8rem;color:var(--gray700);font-weight:500}

/* Formulaire dynamique */
.dyn-form{background:var(--gray50);border:1.5px solid var(--gray200);border-radius:var(--r-lg);padding:1.25rem;margin-bottom:1rem}
.dyn-form-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.85rem;color:var(--gray800);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.form-group{margin-bottom:.9rem}
.form-label{display:block;font-size:.72rem;font-weight:600;color:var(--gray500);letter-spacing:.05em;text-transform:uppercase;margin-bottom:.4rem}
.form-control{width:100%;padding:.75rem 1rem;border:1.5px solid var(--gray200);border-radius:var(--r-md);font-size:.88rem;color:var(--gray800);background:white;outline:none;transition:all .25s;font-family:'DM Sans',sans-serif}
.form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.form-hint{font-size:.7rem;color:var(--gray400);margin-top:.3rem}
select.form-control option{font-size:.88rem}

/* Barre de progression certification */
.cert-controls{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem}
.cert-pct-wrap{grid-column:1/-1}
.cert-pct-row{display:flex;align-items:center;gap:1rem}
.cert-pct-input{width:80px;padding:.55rem .75rem;border:1.5px solid var(--gray200);border-radius:var(--r-md);font-size:1rem;font-weight:700;font-family:'JetBrains Mono',monospace;text-align:center;color:var(--gray800)}
.cert-pct-bar-wrap{flex:1;height:10px;background:var(--gray200);border-radius:99px;overflow:hidden}
.cert-pct-bar{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--accent),var(--success));transition:width .4s}
.cert-quick-btns{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem}
.cert-quick-btn{padding:.35rem .75rem;border:1.5px solid var(--gray200);border-radius:var(--r-full);font-size:.72rem;font-weight:600;cursor:pointer;background:white;transition:all .2s;color:var(--gray600)}
.cert-quick-btn:hover{border-color:var(--accent);color:var(--accent)}

/* Toggle email */
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.1rem;background:var(--gray50);border:1.5px solid var(--gray200);border-radius:var(--r-lg);margin-bottom:1rem}
.toggle-info{flex:1}
.toggle-label{font-weight:600;font-size:.85rem;color:var(--gray800)}
.toggle-sub{font-size:.73rem;color:var(--gray400);margin-top:.1rem}
.toggle-switch{position:relative;width:44px;height:24px;flex-shrink:0;cursor:pointer}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-track{position:absolute;inset:0;background:var(--gray300);border-radius:99px;transition:background .3s}
.toggle-switch input:checked+.toggle-track{background:var(--success)}
.toggle-knob{position:absolute;top:3px;left:3px;width:18px;height:18px;background:white;border-radius:50%;transition:transform .3s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked~.toggle-knob{transform:translateX(20px)}

/* Bouton approuver */
.btn-approve{width:100%;padding:1.1rem;background:linear-gradient(135deg,var(--success),#059669);color:white;border:none;border-radius:var(--r-full);font-family:'Sora',sans-serif;font-weight:700;font-size:1rem;cursor:pointer;transition:all .3s;box-shadow:0 6px 20px rgba(16,185,129,.35);display:flex;align-items:center;justify-content:center;gap:.5rem}
.btn-approve:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(16,185,129,.45)}
.btn-approve:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-nav{padding:.65rem 1.25rem;border-radius:var(--r-full);font-weight:600;font-size:.82rem;cursor:pointer;border:1.5px solid var(--gray200);background:white;color:var(--gray700);transition:all .2s}
.btn-nav:hover{border-color:var(--gray400)}
.btn-nav-primary{background:var(--accent);color:white;border-color:var(--accent)}
.btn-nav-primary:hover{background:var(--accent-deep);border-color:var(--accent-deep)}
.wizard-nav{display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1.5px solid var(--gray100)}

/* Alert temps réel */
.alert-banner{display:none;background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.2);border-radius:var(--r-lg);padding:.85rem 1.1rem;margin-bottom:1rem;align-items:center;gap:.75rem;cursor:pointer}
.alert-banner.show{display:flex}
.alert-banner-icon{font-size:1.2rem;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.alert-banner-text{flex:1;font-size:.82rem;color:var(--danger);font-weight:500}

/* Toast */
.toast{position:fixed;bottom:1.5rem;right:1.5rem;background:#1e293b;color:white;padding:.9rem 1.4rem;border-radius:var(--r-lg);font-size:.85rem;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.25);z-index:9999;transform:translateY(100px);opacity:0;transition:all .35s var(--ease);max-width:360px}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{border-left:4px solid var(--success)}
.toast.error{border-left:4px solid var(--danger)}

/* Résultat action */
.result-card{display:none;background:rgba(16,185,129,.06);border:1.5px solid rgba(16,185,129,.2);border-radius:var(--r-xl);padding:2rem;text-align:center;margin-bottom:1.5rem}
.result-card.show{display:block}
.result-card.error{background:rgba(239,68,68,.06);border-color:rgba(239,68,68,.2)}

/* Responsive */
@media(max-width:768px){
  .sidebar{transform:translateX(-100%);transition:transform .3s}
  .sidebar.open{transform:translateX(0)}
  .topbar{left:0}
  .dash-main{margin-left:0;padding:1rem}
  .actions-grid{grid-template-columns:1fr}
  .form-row-2{grid-template-columns:1fr}
  .cert-controls{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <a href="../index.php" class="sidebar-logo">
    <div class="sidebar-logo-mark">GTB</div>
    <div class="sidebar-logo-text">
      <span class="top">Global Trust Bank</span>
      <span class="bot">Administration</span>
    </div>
  </a>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= e($initials) ?></div>
    <div>
      <div class="sidebar-user-name"><?= e($adm['first_name'].' '.$adm['last_name']) ?></div>
      <div class="sidebar-user-role"><?= e($adm['role']) ?></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">📊</span>Dashboard</a>
    <a href="../utilisateurs/index.php" class="sidebar-link"><span class="sidebar-icon">👥</span>Utilisateurs</a>
    <a href="../transactions/index.php" class="sidebar-link"><span class="sidebar-icon">💸</span>Transactions</a>
    <a href="../virements/index.php" class="sidebar-link"><span class="sidebar-icon">🔄</span>Virements<?php if($pending_count>0): ?><span class="sidebar-badge"><?= $pending_count ?></span><?php endif; ?></a>
    <div class="sidebar-section-label">Contrôle</div>
    <a href="index.php" class="sidebar-link active"><span class="sidebar-icon">🎛️</span>Accès clients</a>
    <a href="../kyc/index.php" class="sidebar-link"><span class="sidebar-icon">🪪</span>KYC</a>
    <a href="../notifications/index.php" class="sidebar-link"><span class="sidebar-icon">🔔</span>Notifications</a>
    <a href="../fraude/index.php" class="sidebar-link"><span class="sidebar-icon">🛡️</span>Fraude</a>
    <div class="sidebar-section-label">Outils</div>
    <a href="../comptes/index.php" class="sidebar-link"><span class="sidebar-icon">🏦</span>Comptes</a>
    <a href="../cartes/index.php" class="sidebar-link"><span class="sidebar-icon">💳</span>Cartes</a>
    <a href="../credits/index.php" class="sidebar-link"><span class="sidebar-icon">📋</span>Crédits</a>
    <a href="../logs/index.php" class="sidebar-link"><span class="sidebar-icon">📜</span>Logs</a>
    <a href="../parametres/index.php" class="sidebar-link"><span class="sidebar-icon">⚙️</span>Paramètres</a>
  </nav>
  <div class="sidebar-footer">
    <a href="../login.php?action=logout" class="sidebar-footer-link">🚪 Déconnexion</a>
  </div>
</div>

<div class="topbar">
  <div style="display:flex;align-items:center;gap:1rem">
    <button class="topbar-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
    <span class="topbar-page-title">🎛️ <?= e($title) ?></span>
  </div>
  <div class="topbar-right">
    <div class="topbar-btn" id="btn-pending" title="Virements en attente" onclick="scrollToPending()">
      🔔<?php if($pending_count>0):?><span class="notif-dot"></span><?php endif;?>
    </div>
    <a href="../index.php" class="topbar-btn" title="Retour dashboard">🏠</a>
  </div>
</div>

<main class="dash-main">
  <div class="page-title">🎛️ Mettre à jour un accès client</div>
  <div class="page-sub">Sélectionnez un client et choisissez une action à effectuer sur son compte.</div>

  <!-- Alerte virements en attente -->
  <div class="alert-banner <?= $pending_count>0?'show':'' ?>" id="alert-pending" onclick="filterByPending()">
    <span class="alert-banner-icon">🔴</span>
    <div class="alert-banner-text">
      <strong><?= $pending_count ?> virement(s) en attente</strong> — Des clients attendent votre validation.
    </div>
    <span style="font-size:.75rem;color:var(--danger);font-weight:600">Gérer →</span>
  </div>

  <div class="wizard-card">
    <div class="wizard-header">
      <div class="wizard-header-icon">🎛️</div>
      <div class="wizard-header-text">
        <h2>Tour de contrôle client</h2>
        <p>Effectuez toute action administrative sur le compte d'un client</p>
      </div>
    </div>

    <!-- Steps -->
    <div class="wizard-steps" id="wizard-steps">
      <div class="wstep"><div class="wstep-num active" id="ws-1">1</div><span class="wstep-label active" id="wl-1">Client</span></div>
      <div class="wstep-line" id="wline-1"></div>
      <div class="wstep"><div class="wstep-num" id="ws-2">2</div><span class="wstep-label" id="wl-2">Action</span></div>
      <div class="wstep-line" id="wline-2"></div>
      <div class="wstep"><div class="wstep-num" id="ws-3">3</div><span class="wstep-label" id="wl-3">Détails</span></div>
      <div class="wstep-line" id="wline-3"></div>
      <div class="wstep"><div class="wstep-num" id="ws-4">4</div><span class="wstep-label" id="wl-4">Date</span></div>
      <div class="wstep-line" id="wline-4"></div>
      <div class="wstep"><div class="wstep-num" id="ws-5">5</div><span class="wstep-label" id="wl-5">Notification</span></div>
      <div class="wstep-line" id="wline-5"></div>
      <div class="wstep"><div class="wstep-num" id="ws-6">✓</div><span class="wstep-label" id="wl-6">Valider</span></div>
    </div>

    <div class="wizard-body">

      <!-- Résultat -->
      <div class="result-card" id="result-card">
        <div style="font-size:2.5rem;margin-bottom:.75rem" id="result-icon">✅</div>
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:1.1rem;color:var(--gray800);margin-bottom:.5rem" id="result-title">Action effectuée !</div>
        <div style="font-size:.85rem;color:var(--gray500);margin-bottom:1.5rem" id="result-msg"></div>
        <button class="btn-nav btn-nav-primary" onclick="resetWizard()">Nouvelle action</button>
      </div>

      <!-- STEP 1 : Choisir le client -->
      <div class="wizard-section visible" id="step-1">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:1rem">Étape 1 — Sélectionner le compte client</div>

        <div class="client-selected-card" id="selected-card">
          <div class="client-result-avatar" id="sel-avatar">??</div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:.9rem;color:var(--gray800)" id="sel-name">—</div>
            <div style="font-size:.73rem;color:var(--gray400)" id="sel-email">—</div>
            <div class="client-selected-iban" id="sel-iban">—</div>
          </div>
          <div id="sel-status-badge"></div>
          <button onclick="clearClient()" style="background:none;border:none;cursor:pointer;color:var(--gray400);font-size:1.1rem" title="Changer de client">✕</button>
        </div>

        <div class="client-search-wrap" id="search-wrap">
          <span class="client-search-icon">🔍</span>
          <input type="text" class="client-search-input" id="client-search" placeholder="Rechercher par nom, email, IBAN ou numéro client..." oninput="searchClients(this.value)" autocomplete="off"/>
        </div>
        <div class="client-results" id="client-results"></div>

        <div class="wizard-nav">
          <span></span>
          <button class="btn-nav btn-nav-primary" onclick="goStep(2)">Suivant →</button>
        </div>
      </div>

      <!-- STEP 2 : Choisir l'action -->
      <div class="wizard-section" id="step-2">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:1rem">Étape 2 — Choisir une action</div>

        <div class="actions-grid">
          <!-- Transactions -->
          <div class="action-group">
            <div class="action-group-header">💸 Transactions & Mouvements</div>
            <div class="action-item" data-action="virement_entrant" onclick="selectAction(this)"><span class="action-item-icon">📥</span><span class="action-item-label">Émettre un virement entrant</span></div>
            <div class="action-item" data-action="virement_sortant" onclick="selectAction(this)"><span class="action-item-icon">📤</span><span class="action-item-label">Émettre un virement sortant</span></div>
            <div class="action-item" data-action="remboursement" onclick="selectAction(this)"><span class="action-item-icon">↩️</span><span class="action-item-label">Émettre un remboursement</span></div>
            <div class="action-item" data-action="annuler_transaction" onclick="selectAction(this)"><span class="action-item-icon">❌</span><span class="action-item-label">Annuler une transaction</span></div>
            <div class="action-item" data-action="bloquer_transaction" onclick="selectAction(this)"><span class="action-item-icon">🚫</span><span class="action-item-label">Bloquer une transaction en attente</span></div>
            <div class="action-item" data-action="stop_virement_pct" onclick="selectAction(this)"><span class="action-item-icon">⛔</span><span class="action-item-label">Pourcentage d'arrêt virement</span></div>
          </div>

          <!-- Barre certification -->
          <div class="action-group">
            <div class="action-group-header">📊 Barre de certification virement</div>
            <div class="action-item" data-action="cert_bloquer" onclick="selectAction(this)"><span class="action-item-icon">⏸️</span><span class="action-item-label">Bloquer la barre à X%</span></div>
            <div class="action-item" data-action="cert_debloquer" onclick="selectAction(this)"><span class="action-item-icon">▶️</span><span class="action-item-label">Débloquer la barre</span></div>
            <div class="action-item" data-action="cert_reset" onclick="selectAction(this)"><span class="action-item-icon">🔄</span><span class="action-item-label">Remettre à 0%</span></div>
            <div class="action-item" data-action="cert_forcer_100" onclick="selectAction(this)"><span class="action-item-icon">✅</span><span class="action-item-label">Forcer à 100% (valider)</span></div>
            <div class="action-item" data-action="cert_geler" onclick="selectAction(this)"><span class="action-item-icon">🧊</span><span class="action-item-label">Geler la barre</span></div>
            <div class="action-item" data-action="cert_vitesse" onclick="selectAction(this)"><span class="action-item-icon">⚡</span><span class="action-item-label">Définir la vitesse</span></div>
            <div class="action-item" data-action="cert_message" onclick="selectAction(this)"><span class="action-item-icon">💬</span><span class="action-item-label">Message de blocage</span></div>
            <div class="action-item" data-action="cert_rejeter" onclick="selectAction(this)"><span class="action-item-icon">🚫</span><span class="action-item-label">Rejeter le virement</span></div>
            <div class="action-item" data-action="cert_valider" onclick="selectAction(this)"><span class="action-item-icon">🟢</span><span class="action-item-label">Valider manuellement</span></div>
          </div>

          <!-- Gestion compte -->
          <div class="action-group">
            <div class="action-group-header">🏦 Gestion du Compte</div>
            <div class="action-item" data-action="bloquer_acces" onclick="selectAction(this)"><span class="action-item-icon">🔒</span><span class="action-item-label">Bloquer l'accès client</span></div>
            <div class="action-item" data-action="debloquer_acces" onclick="selectAction(this)"><span class="action-item-icon">🔓</span><span class="action-item-label">Débloquer l'accès client</span></div>
            <div class="action-item" data-action="suspendre_compte" onclick="selectAction(this)"><span class="action-item-icon">⏸️</span><span class="action-item-label">Suspendre le compte</span></div>
            <div class="action-item" data-action="fermer_compte" onclick="selectAction(this)"><span class="action-item-icon">🗑️</span><span class="action-item-label">Fermer le compte</span></div>
            <div class="action-item" data-action="modifier_solde" onclick="selectAction(this)"><span class="action-item-icon">💰</span><span class="action-item-label">Modifier le solde</span></div>
            <div class="action-item" data-action="modifier_decouvert" onclick="selectAction(this)"><span class="action-item-icon">📉</span><span class="action-item-label">Modifier le découvert autorisé</span></div>
            <div class="action-item" data-action="changer_type_compte" onclick="selectAction(this)"><span class="action-item-icon">🔀</span><span class="action-item-label">Changer le type de compte</span></div>
          </div>

          <!-- Carte bancaire -->
          <div class="action-group">
            <div class="action-group-header">💳 Carte Bancaire</div>
            <div class="action-item" data-action="bloquer_carte" onclick="selectAction(this)"><span class="action-item-icon">🚫</span><span class="action-item-label">Bloquer la carte</span></div>
            <div class="action-item" data-action="debloquer_carte" onclick="selectAction(this)"><span class="action-item-icon">✅</span><span class="action-item-label">Débloquer la carte</span></div>
            <div class="action-item" data-action="modifier_infos_carte" onclick="selectAction(this)"><span class="action-item-icon">✏️</span><span class="action-item-label">Modifier les infos de la carte</span></div>
            <div class="action-item" data-action="renouveler_carte" onclick="selectAction(this)"><span class="action-item-icon">🔄</span><span class="action-item-label">Renouveler la carte</span></div>
            <div class="action-item" data-action="modifier_plafond_carte" onclick="selectAction(this)"><span class="action-item-icon">💸</span><span class="action-item-label">Modifier le plafond de la carte</span></div>
            <div class="action-item" data-action="toggle_paiement_en_ligne" onclick="selectAction(this)"><span class="action-item-icon">🌐</span><span class="action-item-label">Activer/Désactiver paiements en ligne</span></div>
            <div class="action-item" data-action="toggle_paiement_etranger" onclick="selectAction(this)"><span class="action-item-icon">✈️</span><span class="action-item-label">Activer/Désactiver paiements étranger</span></div>
          </div>

          <!-- Coordonnées bancaires -->
          <div class="action-group">
            <div class="action-group-header">🏧 Coordonnées Bancaires</div>
            <div class="action-item" data-action="modifier_iban_bic" onclick="selectAction(this)"><span class="action-item-icon">🔢</span><span class="action-item-label">Modifier l'IBAN et le BIC</span></div>
            <div class="action-item" data-action="modifier_rib" onclick="selectAction(this)"><span class="action-item-icon">📄</span><span class="action-item-label">Modifier le RIB</span></div>
          </div>

          <!-- Sécurité -->
          <div class="action-group">
            <div class="action-group-header">🔐 Sécurité</div>
            <div class="action-item" data-action="reset_password" onclick="selectAction(this)"><span class="action-item-icon">🔑</span><span class="action-item-label">Réinitialiser le mot de passe</span></div>
            <div class="action-item" data-action="toggle_2fa" onclick="selectAction(this)"><span class="action-item-icon">📱</span><span class="action-item-label">Activer/Désactiver 2FA</span></div>
            <div class="action-item" data-action="forcer_deconnexion" onclick="selectAction(this)"><span class="action-item-icon">🚪</span><span class="action-item-label">Forcer la déconnexion</span></div>
            <div class="action-item" data-action="modifier_niveau_securite" onclick="selectAction(this)"><span class="action-item-icon">🛡️</span><span class="action-item-label">Modifier le niveau de sécurité</span></div>
          </div>

          <!-- KYC -->
          <div class="action-group">
            <div class="action-group-header">🪪 Vérification / KYC</div>
            <div class="action-item" data-action="valider_kyc" onclick="selectAction(this)"><span class="action-item-icon">✅</span><span class="action-item-label">Valider le KYC</span></div>
            <div class="action-item" data-action="refuser_kyc" onclick="selectAction(this)"><span class="action-item-icon">❌</span><span class="action-item-label">Refuser le KYC</span></div>
            <div class="action-item" data-action="changer_statut_kyc" onclick="selectAction(this)"><span class="action-item-icon">🔄</span><span class="action-item-label">Changer le statut de vérification</span></div>
            <div class="action-item" data-action="demander_documents" onclick="selectAction(this)"><span class="action-item-icon">📂</span><span class="action-item-label">Demander des documents</span></div>
          </div>

          <!-- Limites -->
          <div class="action-group">
            <div class="action-group-header">📊 Limites & Plafonds</div>
            <div class="action-item" data-action="plafond_retrait" onclick="selectAction(this)"><span class="action-item-icon">🏧</span><span class="action-item-label">Plafond de retrait journalier</span></div>
            <div class="action-item" data-action="plafond_virement" onclick="selectAction(this)"><span class="action-item-icon">💸</span><span class="action-item-label">Plafond de virement</span></div>
            <div class="action-item" data-action="plafond_paiement" onclick="selectAction(this)"><span class="action-item-icon">💳</span><span class="action-item-label">Plafond de paiement</span></div>
          </div>

          <!-- Interface -->
          <div class="action-group">
            <div class="action-group-header">🎨 Interface & Communication</div>
            <div class="action-item" data-action="changer_couleur" onclick="selectAction(this)"><span class="action-item-icon">🎨</span><span class="action-item-label">Changer la couleur de l'interface</span></div>
            <div class="action-item" data-action="changer_langue" onclick="selectAction(this)"><span class="action-item-icon">🌍</span><span class="action-item-label">Changer la langue d'affichage</span></div>
            <div class="action-item" data-action="envoyer_notification" onclick="selectAction(this)"><span class="action-item-icon">🔔</span><span class="action-item-label">Envoyer une notification in-app</span></div>
            <div class="action-item" data-action="envoyer_message" onclick="selectAction(this)"><span class="action-item-icon">💬</span><span class="action-item-label">Envoyer un message/alerte</span></div>
            <div class="action-item" data-action="toggle_alertes_email" onclick="selectAction(this)"><span class="action-item-icon">📧</span><span class="action-item-label">Activer/Désactiver alertes email</span></div>
          </div>
        </div>

        <div class="wizard-nav">
          <button class="btn-nav" onclick="goStep(1)">← Retour</button>
          <button class="btn-nav btn-nav-primary" onclick="goStep(3)">Suivant →</button>
        </div>
      </div>

      <!-- STEP 3 : Formulaire dynamique -->
      <div class="wizard-section" id="step-3">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:1rem">Étape 3 — Détails de l'action</div>
        <div class="dyn-form" id="dyn-form">
          <div class="dyn-form-title" id="dyn-form-title">⚙️ Paramètres</div>
          <div id="dyn-form-body"></div>
        </div>
        <div class="wizard-nav">
          <button class="btn-nav" onclick="goStep(2)">← Retour</button>
          <button class="btn-nav btn-nav-primary" onclick="goStep(4)">Suivant →</button>
        </div>
      </div>

      <!-- STEP 4 : Date passée (facultatif) -->
      <div class="wizard-section" id="step-4">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:.5rem">Étape 4 — Date (facultatif)</div>
        <div style="font-size:.78rem;color:var(--gray400);margin-bottom:1rem">Choisissez une date dans le passé si cette action doit être antidatée.</div>
        <div class="form-group">
          <label class="form-label">Date de l'opération (passée)</label>
          <input type="datetime-local" class="form-control" id="field-date" max="<?= date('Y-m-d\TH:i') ?>"/>
          <div class="form-hint">Laissez vide pour utiliser la date et l'heure actuelles.</div>
        </div>
        <div class="wizard-nav">
          <button class="btn-nav" onclick="goStep(3)">← Retour</button>
          <button class="btn-nav btn-nav-primary" onclick="goStep(5)">Suivant →</button>
        </div>
      </div>

      <!-- STEP 5 : Notification -->
      <div class="wizard-section" id="step-5">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:.5rem">Étape 5 — Notification client</div>
        <div style="font-size:.78rem;color:var(--gray400);margin-bottom:1rem">Rédigez un message à envoyer au client suite à cette action.</div>
        <div class="form-group">
          <label class="form-label">Titre de la notification</label>
          <input type="text" class="form-control" id="field-notif-title" placeholder="Ex : Mise à jour de votre compte"/>
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea class="form-control" id="field-notif-msg" rows="4" placeholder="Rédigez votre message ici..."></textarea>
        </div>

        <div class="toggle-row">
          <div class="toggle-info">
            <div class="toggle-label">📧 Envoyer par email</div>
            <div class="toggle-sub">Le client recevra aussi ce message par email</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="field-send-email" checked/>
            <div class="toggle-track"></div>
            <div class="toggle-knob"></div>
          </label>
        </div>

        <div class="wizard-nav">
          <button class="btn-nav" onclick="goStep(4)">← Retour</button>
          <button class="btn-nav btn-nav-primary" onclick="goStep(6)">Suivant →</button>
        </div>
      </div>

      <!-- STEP 6 : Validation finale -->
      <div class="wizard-section" id="step-6">
        <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:var(--gray700);margin-bottom:1rem">Étape 6 — Récapitulatif & Approbation</div>

        <div style="background:var(--gray50);border:1.5px solid var(--gray200);border-radius:var(--r-lg);padding:1.25rem;margin-bottom:1.25rem">
          <div style="font-size:.72rem;font-weight:700;color:var(--gray400);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.85rem">Récapitulatif de l'action</div>
          <div style="display:grid;gap:.5rem">
            <div style="display:flex;justify-content:space-between;font-size:.85rem"><span style="color:var(--gray500)">Client</span><strong id="recap-client" style="color:var(--gray800)">—</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem"><span style="color:var(--gray500)">Action</span><strong id="recap-action" style="color:var(--accent)">—</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem"><span style="color:var(--gray500)">Date</span><strong id="recap-date" style="color:var(--gray800)">Maintenant</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem"><span style="color:var(--gray500)">Notification</span><strong id="recap-notif" style="color:var(--gray800)">—</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem"><span style="color:var(--gray500)">Email client</span><strong id="recap-email" style="color:var(--gray800)">—</strong></div>
          </div>
        </div>

        <button class="btn-approve" id="btn-approve" onclick="approuver()">
          ✅ Approuver et appliquer la mise à jour
        </button>

        <div class="wizard-nav" style="border-top:none;padding-top:.5rem">
          <button class="btn-nav" onclick="goStep(5)">← Retour</button>
          <span></span>
        </div>
      </div>

    </div><!-- /wizard-body -->
  </div><!-- /wizard-card -->
</main>

<div class="toast" id="toast"></div>

<script>
const CSRF = '<?= Security::csrfToken() ?>';
let selectedClient = null;
let selectedAction = null;
let currentStep = 1;
let searchTimer = null;

const ACTION_LABELS = {
  virement_entrant:'Virement entrant',virement_sortant:'Virement sortant',
  remboursement:'Remboursement',annuler_transaction:'Annuler transaction',
  bloquer_transaction:'Bloquer transaction',stop_virement_pct:'Arrêt virement %',
  cert_bloquer:'Bloquer barre à X%',cert_debloquer:'Débloquer barre',
  cert_reset:'Reset barre à 0%',cert_forcer_100:'Forcer 100%',cert_geler:'Geler barre',
  cert_vitesse:'Vitesse barre',cert_message:'Message blocage',cert_rejeter:'Rejeter virement',
  cert_valider:'Valider virement',
  bloquer_acces:'Bloquer accès client',debloquer_acces:'Débloquer accès client',
  suspendre_compte:'Suspendre compte',fermer_compte:'Fermer compte',
  modifier_solde:'Modifier solde',modifier_decouvert:'Modifier découvert',
  changer_type_compte:'Changer type compte',
  bloquer_carte:'Bloquer carte',debloquer_carte:'Débloquer carte',
  modifier_infos_carte:'Modifier infos carte',renouveler_carte:'Renouveler carte',
  modifier_plafond_carte:'Plafond carte',toggle_paiement_en_ligne:'Paiements en ligne',
  toggle_paiement_etranger:'Paiements étranger',
  modifier_iban_bic:'Modifier IBAN/BIC',modifier_rib:'Modifier RIB',
  reset_password:'Reset mot de passe',toggle_2fa:'Toggle 2FA',
  forcer_deconnexion:'Forcer déconnexion',modifier_niveau_securite:'Niveau sécurité',
  valider_kyc:'Valider KYC',refuser_kyc:'Refuser KYC',
  changer_statut_kyc:'Statut KYC',demander_documents:'Demander documents',
  plafond_retrait:'Plafond retrait',plafond_virement:'Plafond virement',plafond_paiement:'Plafond paiement',
  changer_couleur:'Couleur interface',changer_langue:'Langue affichage',
  envoyer_notification:'Notification in-app',envoyer_message:'Message/Alerte',
  toggle_alertes_email:'Alertes email',
};

// ── Navigation étapes ──
function goStep(n) {
  if (n === 2 && !selectedClient) { showToast('Veuillez d\'abord sélectionner un client.', 'error'); return; }
  if (n === 3 && !selectedAction) { showToast('Veuillez sélectionner une action.', 'error'); return; }
  if (n === 3) buildDynForm();
  if (n === 6) buildRecap();

  document.getElementById('step-'+currentStep).classList.remove('visible');
  document.getElementById('step-'+n).classList.add('visible');
  currentStep = n;
  updateSteps(n);
  window.scrollTo({top:0,behavior:'smooth'});
}

function updateSteps(n) {
  for (let i=1; i<=6; i++) {
    const num   = document.getElementById('ws-'+i);
    const label = document.getElementById('wl-'+i);
    const line  = document.getElementById('wline-'+i);
    if (!num) continue;
    num.className = 'wstep-num' + (i<n?' done':i===n?' active':'');
    if (num.className.includes('done')) num.textContent = '✓';
    else num.textContent = i===6?'✓':i;
    if (label) label.className = 'wstep-label'+(i<n?' done':i===n?' active':'');
    if (line)  line.className  = 'wstep-line'+(i<n?' done':'');
  }
}

// ── Recherche client ──
function searchClients(q) {
  clearTimeout(searchTimer);
  const res = document.getElementById('client-results');
  if (!q || q.length < 2) { res.style.display='none'; return; }
  searchTimer = setTimeout(async () => {
    const r = await fetch('api/clients.php?q='+encodeURIComponent(q)+'&csrf='+CSRF);
    const d = await r.json();
    if (!d.success) return;
    res.style.display = d.clients.length ? 'block' : 'none';
    res.innerHTML = d.clients.map(c => `
      <div class="client-result-item" onclick='pickClient(${JSON.stringify(c)})'>
        <div class="client-result-avatar">${c.initials}</div>
        <div class="client-result-info">
          <div class="client-result-name">${c.name}</div>
          <div class="client-result-meta">${c.email} · IBAN : ${c.iban_mask||'—'}</div>
        </div>
        <span class="client-result-status status-${c.status}">${c.status}</span>
      </div>`).join('');
  }, 300);
}

function pickClient(c) {
  selectedClient = c;
  document.getElementById('client-results').style.display='none';
  document.getElementById('client-search').value='';
  document.getElementById('search-wrap').style.display='none';
  const card = document.getElementById('selected-card');
  card.classList.add('visible');
  document.getElementById('sel-avatar').textContent = c.initials;
  document.getElementById('sel-name').textContent   = c.name;
  document.getElementById('sel-email').textContent  = c.email;
  document.getElementById('sel-iban').textContent   = c.iban_fmt ? 'IBAN : '+c.iban_fmt : '';
  document.getElementById('sel-status-badge').innerHTML = `<span class="client-result-status status-${c.status}">${c.status}</span>`;
}

function clearClient() {
  selectedClient = null;
  document.getElementById('selected-card').classList.remove('visible');
  document.getElementById('search-wrap').style.display='';
}

// ── Sélection action ──
function selectAction(el) {
  document.querySelectorAll('.action-item').forEach(i=>i.classList.remove('selected'));
  el.classList.add('selected');
  selectedAction = el.dataset.action;
}

// ── Formulaire dynamique ──
function buildDynForm() {
  const title = document.getElementById('dyn-form-title');
  const body  = document.getElementById('dyn-form-body');
  const label = ACTION_LABELS[selectedAction] || selectedAction;
  title.textContent = '⚙️ ' + label;

  const FORMS = {
    virement_entrant: `
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">Montant net *</label><input type="number" class="form-control" id="f-montant" min="0.01" step="0.01" placeholder="0.00" required/></div>
        <div class="form-group"><label class="form-label">Devise</label><select class="form-control" id="f-devise"><option value="EUR">EUR €</option><option value="USD">USD $</option><option value="GBP">GBP £</option><option value="MXN">MXN</option><option value="BRL">BRL</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Reçu de (provenance) *</label><input type="text" class="form-control" id="f-source" placeholder="Ex: Jean Dupont, Société ABC..." required/></div>
      <div class="form-group"><label class="form-label">IBAN émetteur (fictif)</label><input type="text" class="form-control" id="f-iban-src" placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX"/><div class="form-hint">Chiffres fictifs uniquement — aucun vrai argent transféré</div></div>
      <div class="form-group"><label class="form-label">Référence</label><input type="text" class="form-control" id="f-ref" placeholder="VIR-2026-XXXXXX"/></div>
      <div class="form-group"><label class="form-label">Motif</label><input type="text" class="form-control" id="f-motif" placeholder="Ex : Remboursement prêt, Loyer..."/></div>`,
    virement_sortant: `
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">Montant net *</label><input type="number" class="form-control" id="f-montant" min="0.01" step="0.01" placeholder="0.00" required/></div>
        <div class="form-group"><label class="form-label">Devise</label><select class="form-control" id="f-devise"><option value="EUR">EUR €</option><option value="USD">USD $</option><option value="GBP">GBP £</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Envoyé à (bénéficiaire) *</label><input type="text" class="form-control" id="f-dest" placeholder="Nom du bénéficiaire" required/></div>
      <div class="form-group"><label class="form-label">IBAN bénéficiaire (fictif)</label><input type="text" class="form-control" id="f-iban-dest" placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX"/></div>
      <div class="form-group"><label class="form-label">Transférer vers autre banque ?</label><select class="form-control" id="f-autre-banque"><option value="0">Non — même réseau GTB</option><option value="1">Oui — autre établissement</option></select></div>
      <div class="form-group"><label class="form-label">Nom banque destinataire</label><input type="text" class="form-control" id="f-banque-dest" placeholder="Ex : BNP Paribas, Société Générale..."/></div>
      <div class="form-group"><label class="form-label">Motif</label><input type="text" class="form-control" id="f-motif" placeholder="Ex : Règlement facture..."/></div>`,
    remboursement: `
      <div class="form-group"><label class="form-label">Montant à rembourser *</label><input type="number" class="form-control" id="f-montant" min="0.01" step="0.01" placeholder="0.00" required/></div>
      <div class="form-group"><label class="form-label">Motif du remboursement *</label><input type="text" class="form-control" id="f-motif" placeholder="Ex : Erreur de débit, annulation commande..." required/></div>
      <div class="form-group"><label class="form-label">Référence transaction d'origine</label><input type="text" class="form-control" id="f-ref" placeholder="TRX-XXXXXXXX"/></div>`,
    stop_virement_pct: `
      <div class="form-group">
        <label class="form-label">Pourcentage d'arrêt *</label>
        <div class="cert-pct-row">
          <input type="number" class="cert-pct-input" id="f-pct" min="0" max="100" value="0" oninput="document.getElementById('pct-bar').style.width=this.value+'%'"/>
          <div class="cert-pct-bar-wrap"><div class="cert-pct-bar" id="pct-bar" style="width:0%"></div></div>
        </div>
        <div class="cert-quick-btns">
          <button class="cert-quick-btn" type="button" onclick="setPct(0)">0%</button>
          <button class="cert-quick-btn" type="button" onclick="setPct(25)">25%</button>
          <button class="cert-quick-btn" type="button" onclick="setPct(50)">50%</button>
          <button class="cert-quick-btn" type="button" onclick="setPct(75)">75%</button>
          <button class="cert-quick-btn" type="button" onclick="setPct(100)">100%</button>
        </div>
        <div class="form-hint">0% = aucun arrêt, 100% = tous virements bloqués</div>
      </div>`,
    cert_bloquer: `
      <div class="form-group"><label class="form-label">Bloquer à quel pourcentage ? *</label>
        <div class="cert-pct-row"><input type="number" class="cert-pct-input" id="f-pct" min="0" max="99" value="50" oninput="document.getElementById('pct-bar').style.width=this.value+'%'"/>
        <div class="cert-pct-bar-wrap"><div class="cert-pct-bar" id="pct-bar" style="width:50%"></div></div></div>
        <div class="cert-quick-btns"><button class="cert-quick-btn" type="button" onclick="setPct(25)">25%</button><button class="cert-quick-btn" type="button" onclick="setPct(50)">50%</button><button class="cert-quick-btn" type="button" onclick="setPct(67)">67%</button><button class="cert-quick-btn" type="button" onclick="setPct(85)">85%</button></div>
      </div>
      <div class="form-group"><label class="form-label">Message affiché au client</label><input type="text" class="form-control" id="f-cert-msg" placeholder="Ex : Vérification de sécurité en cours..."/></div>`,
    cert_vitesse: `
      <div class="form-group"><label class="form-label">Vitesse de progression *</label>
        <select class="form-control" id="f-vitesse">
          <option value="slow">🐢 Lente — très ralentie</option>
          <option value="normal" selected>🚶 Normale</option>
          <option value="fast">🚀 Rapide</option>
        </select></div>`,
    cert_message: `
      <div class="form-group"><label class="form-label">Message de blocage visible par le client *</label>
        <textarea class="form-control" id="f-cert-msg" rows="3" placeholder="Ex : Votre virement est en cours de vérification par notre équipe de conformité. Merci de votre patience." required></textarea></div>`,
    bloquer_acces: `
      <div class="form-group"><label class="form-label">Motif du blocage *</label><textarea class="form-control" id="f-motif" rows="3" placeholder="Ex : Activité suspecte détectée, vérification requise..." required></textarea></div>
      <div class="form-group"><label class="form-label">Type de blocage *</label>
        <select class="form-control" id="f-block-type" onchange="toggleBlockDate(this.value)">
          <option value="permanent">Permanent</option>
          <option value="temporary">Temporaire</option>
        </select></div>
      <div class="form-group" id="block-date-wrap" style="display:none"><label class="form-label">Déblocage automatique le</label><input type="datetime-local" class="form-control" id="f-block-until"/></div>`,
    modifier_solde: `
      <div class="form-group"><label class="form-label">Nouveau solde *</label><input type="number" class="form-control" id="f-montant" step="0.01" placeholder="0.00" required/></div>
      <div class="form-group"><label class="form-label">Motif de modification *</label><input type="text" class="form-control" id="f-motif" placeholder="Ex : Correction comptable, ajustement..." required/></div>`,
    modifier_decouvert: `
      <div class="form-group"><label class="form-label">Nouveau découvert autorisé *</label><input type="number" class="form-control" id="f-montant" step="0.01" placeholder="500.00" required/></div>`,
    changer_type_compte: `
      <div class="form-group"><label class="form-label">Nouveau type de compte *</label>
        <select class="form-control" id="f-type-compte">
          <option value="courant">Courant</option>
          <option value="epargne">Épargne</option>
          <option value="business">Business</option>
          <option value="premium">Premium</option>
        </select></div>`,
    modifier_infos_carte: `
      <div class="form-group"><label class="form-label">Numéro de carte (fictif)</label><input type="text" class="form-control" id="f-card-num" placeholder="XXXX XXXX XXXX XXXX" maxlength="19"/></div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">CVV (fictif)</label><input type="text" class="form-control" id="f-cvv" placeholder="XXX" maxlength="4"/></div>
        <div class="form-group"><label class="form-label">Date d'expiration</label><input type="month" class="form-control" id="f-expire"/></div>
      </div>`,
    modifier_plafond_carte: `
      <div class="form-group"><label class="form-label">Nouveau plafond *</label><input type="number" class="form-control" id="f-montant" min="0" step="0.01" placeholder="3000.00" required/></div>`,
    modifier_iban_bic: `
      <div class="form-group"><label class="form-label">Nouvel IBAN (fictif) *</label><input type="text" class="form-control" id="f-iban" placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX" required/></div>
      <div class="form-group"><label class="form-label">Nouveau BIC *</label><input type="text" class="form-control" id="f-bic" placeholder="GTBKFRPPXXX" required/></div>`,
    modifier_rib: `
      <div class="form-group"><label class="form-label">Nouveau numéro de compte *</label><input type="text" class="form-control" id="f-rib" placeholder="XXXXXXXXXXX" maxlength="11" required/></div>`,
    modifier_niveau_securite: `
      <div class="form-group"><label class="form-label">Niveau de sécurité *</label>
        <select class="form-control" id="f-sec-level">
          <option value="standard">Standard</option>
          <option value="renforce">Renforcé</option>
          <option value="maximum">Maximum</option>
        </select></div>`,
    changer_statut_kyc: `
      <div class="form-group"><label class="form-label">Nouveau statut KYC *</label>
        <select class="form-control" id="f-kyc-status">
          <option value="pending">En attente</option>
          <option value="verified">Vérifié</option>
          <option value="rejected">Rejeté</option>
          <option value="under_review">En examen</option>
        </select></div>`,
    demander_documents: `
      <div class="form-group"><label class="form-label">Documents demandés *</label>
        <textarea class="form-control" id="f-docs" rows="3" placeholder="Ex : Justificatif de domicile récent, Carte nationale d'identité recto-verso..." required></textarea></div>`,
    plafond_retrait: `
      <div class="form-group"><label class="form-label">Nouveau plafond de retrait journalier *</label><input type="number" class="form-control" id="f-montant" min="0" step="0.01" placeholder="10000.00" required/></div>`,
    plafond_virement: `
      <div class="form-group"><label class="form-label">Nouveau plafond de virement *</label><input type="number" class="form-control" id="f-montant" min="0" step="0.01" placeholder="50000.00" required/></div>`,
    plafond_paiement: `
      <div class="form-group"><label class="form-label">Nouveau plafond de paiement *</label><input type="number" class="form-control" id="f-montant" min="0" step="0.01" placeholder="5000.00" required/></div>`,
    changer_couleur: `
      <div class="form-group"><label class="form-label">Thème de l'interface *</label>
        <select class="form-control" id="f-couleur">
          <option value="default">🌑 Sombre — défaut GTB</option>
          <option value="light">☀️ Clair</option>
          <option value="blue">🔵 Bleu</option>
          <option value="green">🟢 Vert</option>
          <option value="gold">🟡 Or Premium</option>
          <option value="purple">🟣 Violet</option>
        </select></div>`,
    changer_langue: `
      <div class="form-group"><label class="form-label">Nouvelle langue d'affichage *</label>
        <select class="form-control" id="f-langue">
          <option value="fr">🇫🇷 Français</option>
          <option value="en">🇬🇧 English</option>
          <option value="es">🇪🇸 Español</option>
          <option value="pt">🇵🇹 Português</option>
          <option value="de">🇩🇪 Deutsch</option>
          <option value="it">🇮🇹 Italiano</option>
        </select></div>`,
    envoyer_notification: `
      <div class="form-group"><label class="form-label">Titre *</label><input type="text" class="form-control" id="f-notif-t" placeholder="Titre de la notification" required/></div>
      <div class="form-group"><label class="form-label">Message *</label><textarea class="form-control" id="f-notif-m" rows="3" required></textarea></div>
      <div class="form-group"><label class="form-label">Type</label>
        <select class="form-control" id="f-notif-type">
          <option value="info">ℹ️ Information</option>
          <option value="success">✅ Succès</option>
          <option value="warning">⚠️ Avertissement</option>
          <option value="danger">🚨 Urgent</option>
        </select></div>`,
    envoyer_message: `
      <div class="form-group"><label class="form-label">Message *</label><textarea class="form-control" id="f-message" rows="4" placeholder="Rédigez votre message..." required></textarea></div>`,
  };

  // Cas sans formulaire spécifique (actions simples)
  const simple = ['debloquer_acces','suspendre_compte','fermer_compte','annuler_transaction',
    'bloquer_transaction','cert_debloquer','cert_reset','cert_forcer_100','cert_geler',
    'cert_rejeter','cert_valider','bloquer_carte','debloquer_carte','renouveler_carte',
    'toggle_paiement_en_ligne','toggle_paiement_etranger','reset_password','toggle_2fa',
    'forcer_deconnexion','valider_kyc','refuser_kyc','toggle_alertes_email'];

  if (FORMS[selectedAction]) {
    body.innerHTML = FORMS[selectedAction];
  } else if (simple.includes(selectedAction)) {
    body.innerHTML = `<div style="color:var(--gray500);font-size:.85rem;text-align:center;padding:1rem">
      ✅ Aucun paramètre supplémentaire requis pour cette action.<br>
      <span style="color:var(--gray400);font-size:.75rem">Passez à l'étape suivante pour confirmer.</span></div>`;
  } else {
    body.innerHTML = `<div class="form-group"><label class="form-label">Valeur *</label><input type="text" class="form-control" id="f-valeur" required/></div>`;
  }
}

function setPct(v) {
  const el = document.getElementById('f-pct');
  if (el) { el.value = v; document.getElementById('pct-bar').style.width=v+'%'; }
}
function toggleBlockDate(v) {
  document.getElementById('block-date-wrap').style.display = v==='temporary'?'block':'none';
}

// ── Récapitulatif ──
function buildRecap() {
  document.getElementById('recap-client').textContent = selectedClient?.name || '—';
  document.getElementById('recap-action').textContent = ACTION_LABELS[selectedAction] || selectedAction;
  const d = document.getElementById('field-date')?.value;
  document.getElementById('recap-date').textContent = d ? new Date(d).toLocaleString('fr-FR') : 'Maintenant';
  const t = document.getElementById('field-notif-title')?.value;
  document.getElementById('recap-notif').textContent = t || 'Aucune';
  document.getElementById('recap-email').textContent = document.getElementById('field-send-email')?.checked ? '✅ Oui' : '❌ Non';
}

// ── Approbation ──
async function approuver() {
  const btn = document.getElementById('btn-approve');
  btn.disabled = true; btn.textContent = '⏳ Application en cours...';

  // Collecte des données du formulaire dynamique
  const formData = {};
  ['f-montant','f-devise','f-source','f-dest','f-iban-src','f-iban-dest','f-ref','f-motif',
   'f-pct','f-vitesse','f-cert-msg','f-block-type','f-block-until','f-type-compte',
   'f-card-num','f-cvv','f-expire','f-iban','f-bic','f-rib','f-sec-level','f-kyc-status',
   'f-docs','f-couleur','f-langue','f-notif-t','f-notif-m','f-notif-type','f-message',
   'f-autre-banque','f-banque-dest','f-valeur'].forEach(id => {
    const el = document.getElementById(id);
    if (el) formData[id.replace('f-','')] = el.value;
  });

  const payload = {
    csrf_token:   CSRF,
    client_id:    selectedClient?.id,
    action:       selectedAction,
    form_data:    formData,
    backdated_at: document.getElementById('field-date')?.value || null,
    notif_title:  document.getElementById('field-notif-title')?.value || null,
    notif_msg:    document.getElementById('field-notif-msg')?.value   || null,
    send_email:   document.getElementById('field-send-email')?.checked || false,
  };

  try {
    const r = await fetch('api/action.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
    const d = await r.json();
    if (d.success) {
      document.getElementById('step-6').classList.remove('visible');
      const rc = document.getElementById('result-card');
      rc.classList.add('show');
      document.getElementById('result-title').textContent = 'Action effectuée avec succès !';
      document.getElementById('result-msg').textContent   = d.message || 'La mise à jour a été appliquée.';
      showToast('✅ '+( d.message || 'Action appliquée.'), 'success');
    } else {
      showToast('❌ '+(d.error||'Erreur lors de l\'application.'), 'error');
    }
  } catch(e) {
    showToast('❌ Erreur réseau. Vérifiez votre connexion.', 'error');
  }
  btn.disabled = false; btn.textContent = '✅ Approuver et appliquer la mise à jour';
}

function resetWizard() {
  selectedClient = null; selectedAction = null; currentStep = 1;
  document.querySelectorAll('.wizard-section').forEach(s=>s.classList.remove('visible'));
  document.getElementById('step-1').classList.add('visible');
  document.getElementById('result-card').classList.remove('show');
  document.getElementById('selected-card').classList.remove('visible');
  document.getElementById('search-wrap').style.display='';
  document.querySelectorAll('.action-item').forEach(i=>i.classList.remove('selected'));
  updateSteps(1);
}

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast '+type+' show';
  setTimeout(() => t.classList.remove('show'), 4000);
}

function scrollToPending() { window.location.href='../virements/index.php'; }
function filterByPending() { window.location.href='../virements/index.php?status=running'; }

// Polling alertes en temps réel (toutes les 10s)
setInterval(async () => {
  const r = await fetch('api/pending.php').catch(()=>null);
  if (!r) return;
  const d = await r.json().catch(()=>null);
  if (!d) return;
  const banner = document.getElementById('alert-pending');
  if (d.count > 0) {
    banner.classList.add('show');
    banner.querySelector('.alert-banner-text').innerHTML = `<strong>${d.count} virement(s) en attente</strong> — Des clients attendent votre validation.`;
  } else {
    banner.classList.remove('show');
  }
}, 10000);
</script>
</body>
</html>
