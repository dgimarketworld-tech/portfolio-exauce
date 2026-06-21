<?php
require_once __DIR__ . '/../backend/admin_required.php';
$adm      = $currentAdmin;
$initials = strtoupper(substr($adm['first_name'] ?? 'A', 0, 1) . substr($adm['last_name'] ?? 'M', 0, 1));
$title    = 'Dashboard Admin';

$total_users      = (int) DB::scalar("SELECT COUNT(*) FROM users WHERE role='user'");
$users_active     = (int) DB::scalar("SELECT COUNT(*) FROM users WHERE role='user' AND status='active'");
$total_comptes    = (int) DB::scalar("SELECT COUNT(*) FROM comptes");
$solde_total      = DB::scalar("SELECT SUM(solde) FROM comptes WHERE statut='actif'") ?? 0;
$txs_today        = (int) DB::scalar("SELECT COUNT(*) FROM transactions WHERE DATE(cree_le)=CURDATE()");
$tickets_ouverts  = (int) DB::scalar("SELECT COUNT(*) FROM tickets WHERE statut IN('ouvert','en_cours')");
$credits_en_etude = (int) DB::scalar("SELECT COUNT(*) FROM credits WHERE statut='en_etude'");
$recent_users     = DB::all("SELECT id,first_name,last_name,email,plan,status,created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 8");
$recent_txs       = DB::all("SELECT t.*,c.numero,u.first_name,u.last_name FROM transactions t JOIN comptes c ON t.compte_id=c.id JOIN users u ON c.user_id=u.id ORDER BY t.cree_le DESC LIMIT 8");
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB Admin — <?= e($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap');
:root{
--admin-bg:#0F172A;--admin-bg-deeper:#020617;--admin-bg-mid:#1E293B;
--accent:#3B82F6;--accent-light:#60A5FA;--accent-deep:#1D4ED8;
--white:#FFFFFF;--off:#F1F5F9;--gray50:#F8FAFC;--gray100:#F1F5F9;--gray200:#E2E8F0;--gray300:#CBD5E1;--gray400:#94A3B8;--gray500:#64748B;--gray600:#475569;--gray700:#334155;--gray800:#1E293B;--gray900:#0F172A;
--success:#10B981;--warning:#F59E0B;--danger:#EF4444;--info:#0EA5E9;--purple:#8B5CF6;
--sh-sm:0 1px 3px rgba(15,23,42,.06);--sh-md:0 6px 24px rgba(15,23,42,.08);--sh-lg:0 20px 50px rgba(15,23,42,.12);--sh-xl:0 30px 70px rgba(15,23,42,.16);
--r-sm:6px;--r-md:10px;--r-lg:16px;--r-xl:24px;--r-full:9999px;
--ease:cubic-bezier(.25,.46,.45,.94);--bounce:cubic-bezier(.34,1.56,.64,1);
--sidebar-w:248px;--topbar-h:60px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;font-size:15px;}
body{font-family:'DM Sans',sans-serif;background:#F1F5F9;color:var(--gray700);overflow-x:hidden;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:6px;height:6px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--gray300);border-radius:99px;}
::-webkit-scrollbar-thumb:hover{background:var(--gray400);}
.dash-layout{min-height:100vh;position:relative;}
.sidebar-overlay{position:fixed;inset:0;background:rgba(2,6,23,.5);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .3s var(--ease),visibility .3s var(--ease);z-index:150;}
.sidebar-overlay.show{opacity:1;visibility:visible;}
.sidebar{background:var(--admin-bg);position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);display:flex;flex-direction:column;z-index:200;overflow:hidden;transition:transform .35s var(--ease);border-right:1px solid var(--gray800);}
.sidebar-logo{display:flex;align-items:center;gap:.65rem;padding:1.25rem 1.4rem;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);}
.sidebar-logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.74rem;color:white;flex-shrink:0;letter-spacing:-.02em;}
.sidebar-logo-text{display:flex;flex-direction:column;line-height:1;}
.sidebar-logo-text .top{font-family:'Sora',sans-serif;font-weight:700;font-size:.84rem;color:white;}
.sidebar-logo-text .bot{font-size:.58rem;color:var(--accent-light);letter-spacing:.1em;text-transform:uppercase;margin-top:3px;font-weight:600;}
.sidebar-user{padding:.85rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:.7rem;}
.sidebar-avatar{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0;}
.sidebar-user-info{flex:1;overflow:hidden;}
.sidebar-user-name{font-size:.8rem;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sidebar-user-role{font-size:.6rem;color:#EF4444;letter-spacing:.08em;text-transform:uppercase;font-weight:700;}
.sidebar-nav{flex:1;padding:.75rem 0;overflow-y:auto;-webkit-overflow-scrolling:touch;}
.sidebar-section-label{font-size:.55rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.65rem 1.4rem .25rem;}
.sidebar-link{display:flex;align-items:center;gap:.7rem;padding:.55rem 1.4rem;margin:.05rem .6rem;border-radius:8px;text-decoration:none;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;transition:all .2s var(--ease);position:relative;}
.sidebar-link:hover{color:white;background:rgba(255,255,255,.06);}
.sidebar-link.active{color:white;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25);}
.sidebar-link.active::before{content:'';position:absolute;left:-.6rem;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--accent);border-radius:0 3px 3px 0;}
.sidebar-icon{width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;opacity:.7;transition:opacity .2s;}
.sidebar-link:hover .sidebar-icon,.sidebar-link.active .sidebar-icon{opacity:1;}
.sidebar-badge{margin-left:auto;background:var(--danger);color:white;font-size:.55rem;font-weight:700;padding:.15rem .45rem;border-radius:var(--r-full);}
.sidebar-footer{padding:.85rem 1.4rem;border-top:1px solid rgba(255,255,255,.05);}
.sidebar-footer-link{display:flex;align-items:center;gap:.6rem;font-size:.76rem;color:rgba(255,255,255,.4);text-decoration:none;padding:.4rem 0;transition:color .2s;}
.sidebar-footer-link:hover{color:rgba(255,255,255,.85);}
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(255,255,255,.92);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between;padding:0 1.75rem;z-index:100;gap:1rem;transition:left .35s var(--ease);}
.topbar-left{display:flex;align-items:center;gap:1rem;min-width:0;}
.topbar-page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--gray900);white-space:nowrap;}
.topbar-breadcrumb{font-size:.72rem;color:var(--gray400);white-space:nowrap;}
.topbar-right{display:flex;align-items:center;gap:.65rem;flex-shrink:0;margin-left:auto;}
.topbar-btn{width:36px;height:36px;border-radius:8px;background:var(--gray50);border:1.5px solid var(--gray200);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.95rem;transition:all .2s;color:var(--gray600);flex-shrink:0;}
.topbar-btn:hover{border-color:var(--accent);color:var(--accent);background:white;}
.env-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border-radius:var(--r-full);background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.2);}
.dash-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);padding:1.75rem;min-height:calc(100vh - var(--topbar-h));transition:margin-left .35s var(--ease);}
.page-header{margin-bottom:1.5rem;}
.page-header-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:clamp(1.25rem,2.2vw,1.55rem);color:var(--gray900);line-height:1.2;letter-spacing:-.01em;}
.page-sub{font-size:.82rem;color:var(--gray500);margin-top:.3rem;line-height:1.5;}
.card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);box-shadow:var(--sh-sm);}
.card-pad{padding:1.25rem;}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;gap:1rem;flex-wrap:wrap;}
.card-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.88rem;color:var(--gray900);}
.card-sub{font-size:.72rem;color:var(--gray400);margin-top:.1rem;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.78rem;border:none;cursor:pointer;text-decoration:none;border-radius:8px;padding:.55rem 1.1rem;transition:all .2s var(--ease);white-space:nowrap;}
.btn-ghost{background:transparent;color:var(--gray500);}
.btn-ghost:hover{color:var(--gray900);background:var(--gray100);}
.btn-xs{padding:.25rem .65rem;font-size:.68rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.66rem;font-weight:600;letter-spacing:.02em;padding:.18rem .55rem;border-radius:var(--r-full);}
.badge-blue{background:rgba(59,130,246,.1);color:var(--accent);}
.badge-success{background:rgba(16,185,129,.1);color:var(--success);}
.badge-warning{background:rgba(245,158,11,.12);color:#B45309;}
.badge-danger{background:rgba(239,68,68,.1);color:var(--danger);}
.badge-gray{background:var(--gray100);color:var(--gray600);}
.badge-purple{background:rgba(139,92,246,.1);color:var(--purple);}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);padding:1.1rem 1.25rem;box-shadow:var(--sh-sm);transition:all .25s var(--ease);}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--sh-md);}
.stat-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;}
.stat-info{flex:1;min-width:0;}
.stat-label{font-size:.66rem;font-weight:600;color:var(--gray400);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;}
.stat-value{font-family:'Sora',sans-serif;font-weight:800;font-size:1.45rem;color:var(--gray900);line-height:1;margin-bottom:.35rem;}
.stat-trend{display:inline-flex;align-items:center;gap:.25rem;font-size:.66rem;font-weight:600;padding:.15rem .45rem;border-radius:var(--r-full);}
.stat-trend.up{background:rgba(16,185,129,.1);color:var(--success);}
.stat-trend.down{background:rgba(239,68,68,.1);color:var(--danger);}
.stat-trend.neutral{background:var(--gray100);color:var(--gray500);}
.stat-icon-box{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;}
.stat-icon-box.blue{background:rgba(59,130,246,.1);color:var(--accent);}
.stat-icon-box.green{background:rgba(16,185,129,.1);color:var(--success);}
.stat-icon-box.amber{background:rgba(245,158,11,.12);color:#B45309;}
.stat-icon-box.red{background:rgba(239,68,68,.1);color:var(--danger);}
.stat-icon-box.gray{background:var(--gray100);color:var(--gray600);}
.adm-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.adm-table{width:100%;border-collapse:collapse;min-width:480px;}
.adm-table th{font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gray400);padding:.65rem 1rem;text-align:left;border-bottom:1px solid var(--gray200);background:var(--gray50);}
.adm-table td{padding:.8rem 1rem;font-size:.8rem;color:var(--gray700);border-bottom:1px solid var(--gray100);vertical-align:middle;}
.adm-table tbody tr{transition:background .15s;cursor:pointer;}
.adm-table tbody tr:hover{background:var(--gray50);}
.adm-table tbody tr:last-child td{border-bottom:none;}
.cell-name{font-weight:600;color:var(--gray900);}
.cell-amount{font-family:'Sora',sans-serif;font-weight:700;}
.cell-amount.pos{color:var(--success);}
.cell-amount.neg{color:var(--danger);}
.toast-container{position:fixed;bottom:1.25rem;right:1.25rem;z-index:2000;display:flex;flex-direction:column;gap:.5rem;}
.toast{background:white;border-radius:10px;box-shadow:var(--sh-lg);padding:.8rem 1.1rem;display:flex;align-items:center;gap:.65rem;font-size:.8rem;font-weight:500;color:var(--gray800);border-left:3px solid var(--accent);transform:translateX(120%);opacity:0;transition:all .4s var(--bounce);min-width:240px;max-width:340px;}
.toast.show{transform:translateX(0);opacity:1;}
.toast.success{border-left-color:var(--success);}
.toast.error{border-left-color:var(--danger);}
.toast-icon{font-size:1.1rem;flex-shrink:0;}
.sidebar-toggle{display:none;cursor:pointer;width:36px;height:36px;border-radius:8px;background:var(--gray50);border:1.5px solid var(--gray200);align-items:center;justify-content:center;font-size:1rem;color:var(--gray600);transition:all .2s;flex-shrink:0;}
.sidebar-toggle:hover{border-color:var(--accent);color:var(--accent);}
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.anim-up{animation:fadeInUp .45s var(--ease) both;}
.d1{animation-delay:.04s;}.d2{animation-delay:.08s;}.d3{animation-delay:.12s;}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){
  .sidebar{transform:translateX(-100%);width:260px;}.sidebar.open{transform:translateX(0);}
  .topbar{left:0;padding:0 1rem;}.dash-main{margin-left:0;padding:1rem;}
  .sidebar-toggle{display:flex;}
}
@media(max-width:560px){.stats-grid{grid-template-columns:1fr;}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important;}}
</style>
<link rel="stylesheet" href="mobile.css">
</head>
<body>
<div class="dash-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
  <a href="index.php" class="sidebar-logo">
    <div class="sidebar-logo-mark">GTB</div>
    <div class="sidebar-logo-text">
      <span class="top">Console Admin</span>
      <span class="bot">Backoffice</span>
    </div>
  </a>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= e($initials) ?></div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?= e(($adm['first_name'] ?? '') . ' ' . ($adm['last_name'] ?? '')) ?></div>
      <div class="sidebar-user-role">● <?= ucfirst($adm['role'] ?? 'admin') ?></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="index.php" class="sidebar-link active"><span class="sidebar-icon">⊞</span> Dashboard</a>
    <a href="utilisateurs/index.php" class="sidebar-link"><span class="sidebar-icon">👥</span> Utilisateurs</a>
    <a href="kyc/index.php" class="sidebar-link"><span class="sidebar-icon">🪪</span> KYC / Vérification</a>
    <a href="comptes/index.php" class="sidebar-link"><span class="sidebar-icon">🏦</span> Comptes</a>
    <a href="cartes/index.php" class="sidebar-link"><span class="sidebar-icon">💳</span> Cartes</a>
    <a href="transactions/index.php" class="sidebar-link"><span class="sidebar-icon">≡</span> Transactions</a>
    <a href="virements/index.php" class="sidebar-link"><span class="sidebar-icon">⇄</span> Virements</a>
    <a href="credits/index.php" class="sidebar-link"><span class="sidebar-icon">📋</span> Crédits</a>
    <a href="assurances/index.php" class="sidebar-link"><span class="sidebar-icon">🛡️</span> Assurances</a>
    <div class="sidebar-section-label">Contrôle</div>
    <a href="fraude/index.php" class="sidebar-link"><span class="sidebar-icon">🚨</span> Fraude / Alertes</a>
    <a href="support/index.php" class="sidebar-link"><span class="sidebar-icon">❓</span> Support Client</a>
    <div class="sidebar-section-label">Équipe &amp; Config</div>
    <a href="equipe/index.php" class="sidebar-link"><span class="sidebar-icon">👤</span> Équipe</a>
    <a href="notifications/index.php" class="sidebar-link"><span class="sidebar-icon">🔔</span> Notifications</a>
    <a href="logs/index.php" class="sidebar-link"><span class="sidebar-icon">📜</span> Audit Logs</a>
    <a href="parametres/index.php" class="sidebar-link"><span class="sidebar-icon">⚙</span> Paramètres</a>
  </nav>
  <div class="sidebar-footer">
    <a href="../authentification/api/logout.php" class="sidebar-footer-link" onclick="return confirm('Se déconnecter ?')">
      <span>🔒</span> Déconnexion sécurisée
    </a>
  </div>
</aside>

<header class="topbar">
  <div class="topbar-left">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">☰</button>
    <div>
      <div class="topbar-page-title"><?= e($title) ?></div>
      <div class="topbar-breadcrumb">Tableau de bord</div>
    </div>
  </div>
  <div class="topbar-right">
    <span style="font-size:.8rem;color:var(--gray500)"><?= e(($adm['first_name'] ?? '') . ' ' . ($adm['last_name'] ?? '')) ?></span>
    <span class="badge badge-blue"><?= ucfirst($adm['role'] ?? 'admin') ?></span>
    <span class="env-badge">● <?= GTB_ENV === 'production' ? 'Production' : 'Dev' ?></span>
  </div>
</header>

<main class="dash-main">
  <div class="page-header anim-up">
    <div class="page-header-top">
      <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-sub">Vue d'ensemble · activité en temps réel</p>
      </div>
    </div>
  </div>

  <div class="stats-grid anim-up d1">
    <div class="stat-card">
      <div class="stat-row">
        <div class="stat-info">
          <div class="stat-label">Clients</div>
          <div class="stat-value"><?= number_format($total_users) ?></div>
          <span class="stat-trend up">↑ <?= $users_active ?> actifs</span>
        </div>
        <div class="stat-icon-box blue">👥</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-row">
        <div class="stat-info">
          <div class="stat-label">Comptes</div>
          <div class="stat-value"><?= number_format($total_comptes) ?></div>
          <span class="stat-trend neutral">Solde : <?= format_money($solde_total) ?></span>
        </div>
        <div class="stat-icon-box green">🏦</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-row">
        <div class="stat-info">
          <div class="stat-label">Transactions today</div>
          <div class="stat-value"><?= number_format($txs_today) ?></div>
          <span class="stat-trend neutral">Aujourd'hui</span>
        </div>
        <div class="stat-icon-box amber">≡</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-row">
        <div class="stat-info">
          <div class="stat-label">Tickets ouverts</div>
          <div class="stat-value"><?= $tickets_ouverts ?></div>
          <span class="stat-trend <?= $tickets_ouverts > 10 ? 'down' : 'up' ?>"><?= $tickets_ouverts > 0 ? '⚠ En attente' : '✓ OK' ?></span>
        </div>
        <div class="stat-icon-box <?= $tickets_ouverts > 10 ? 'red' : 'gray' ?>">❓</div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:.25rem">

    <div class="card card-pad anim-up d2">
      <div class="card-header">
        <div class="card-title">Derniers clients inscrits</div>
        <a href="utilisateurs/index.php" class="btn btn-ghost btn-xs">Voir tout →</a>
      </div>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead><tr><th>Nom</th><th>Email</th><th>Plan</th><th>Statut</th></tr></thead>
          <tbody>
          <?php foreach ($recent_users as $u): ?>
          <tr onclick="location.href='utilisateurs/index.php'">
            <td class="cell-name"><?= e(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?></td>
            <td style="font-size:.78rem;color:var(--gray500)"><?= e($u['email']) ?></td>
            <td><span class="badge badge-<?= ($u['plan'] ?? '') === 'premium' ? 'purple' : 'blue' ?>"><?= ucfirst($u['plan'] ?? '') ?></span></td>
            <td><span class="badge badge-<?= ($u['status'] ?? '') === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($u['status'] ?? 'active') ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_users)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--gray400);padding:1rem">Aucun client</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card card-pad anim-up d3">
      <div class="card-header">
        <div class="card-title">Dernières transactions</div>
        <a href="transactions/index.php" class="btn btn-ghost btn-xs">Voir tout →</a>
      </div>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead><tr><th>Client</th><th>Type</th><th>Montant</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($recent_txs as $tx):
            $in = strpos($tx['type'], 'in') !== false || $tx['type'] === 'depot';
          ?>
          <tr>
            <td style="font-size:.8rem"><?= e(($tx['first_name'] ?? '') . ' ' . ($tx['last_name'] ?? '')) ?></td>
            <td><span class="badge badge-<?= $in ? 'success' : 'blue' ?>"><?= str_replace('_', ' ', $tx['type']) ?></span></td>
            <td class="cell-amount <?= $in ? 'pos' : 'neg' ?>"><?= ($in ? '+' : '-') . format_money($tx['montant']) ?></td>
            <td style="font-size:.75rem;color:var(--gray400)"><?= time_ago($tx['cree_le']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>
</div>

<div class="toast-container" id="toastContainer"></div>
<script>
(function () {
  'use strict';
  const sb  = document.getElementById('sidebar');
  const tgl = document.getElementById('sidebarToggle');
  const ov  = document.getElementById('sidebarOverlay');
  const openSb  = () => { sb?.classList.add('open');    ov?.classList.add('show');    };
  const closeSb = () => { sb?.classList.remove('open'); ov?.classList.remove('show'); };
  tgl?.addEventListener('click', () => sb?.classList.contains('open') ? closeSb() : openSb());
  ov?.addEventListener('click', closeSb);
  sb?.querySelectorAll('.sidebar-link').forEach(l => l.addEventListener('click', () => {
    if (matchMedia('(max-width:768px)').matches) closeSb();
  }));
  addEventListener('resize', () => { if (innerWidth > 768) closeSb(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSb(); });
  window.showToast = function (m, t) {
    const c = document.getElementById('toastContainer'); if (!c) return;
    const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
    const x = document.createElement('div');
    x.className = 'toast ' + (t || 'success');
    x.innerHTML = `<span class="toast-icon">${icons[t] || '•'}</span><span>${m}</span>`;
    c.appendChild(x);
    requestAnimationFrame(() => setTimeout(() => x.classList.add('show'), 25));
    setTimeout(() => { x.classList.remove('show'); setTimeout(() => x.remove(), 400); }, 3600);
  };
})();
</script>
</body>
</html>
