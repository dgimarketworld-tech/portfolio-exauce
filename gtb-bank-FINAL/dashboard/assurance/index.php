<?php
require_once __DIR__.'/../../backend/auth_required.php';
$u=$currentUser;
$initials=strtoupper(substr($u['first_name']??'A',0,1).substr($u['last_name']??'M',0,1));
$notif_count=(int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0",['id'=>Session::userId()]);
?><!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB — <?php echo $title??'';?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;600&display=swap');

/* ═══════════════════════════════════════════════
   GTB DASHBOARD — DESIGN SYSTEM
═══════════════════════════════════════════════ */
:root {
  --bnp-green:   #0D1B2A;
  --bnp-dark:    #091520;
  --bnp-deeper:  #050B14;
  --bnp-light:   #F2F4F7;
  --bnp-emerald: #D4AF37;
  --bnp-mint:    #EAD9B5;

  --white:   #FFFFFF;
  --off:     #F2F4F7;
  --gray50:  #F8F9FA;
  --gray100: #E9ECEF;
  --gray200: #DEE2E6;
  --gray300: #CED4DA;
  --gray400: #ADB5BD;
  --gray600: #6C757D;
  --gray800: #343A40;
  --black:   #0D1B2A;

  --gold:  #D4AF37;
  --red:   #E5373A;
  --green: #00C67A;
  --blue:  #1A73E8;
  --purple:#7C3AED;

  --glass:  rgba(255,255,255,0.07);
  --glass2: rgba(255,255,255,0.13);

  --sh-sm: 0 2px 8px rgba(13,27,42,.06);
  --sh-md: 0 8px 32px rgba(13,27,42,.10);
  --sh-lg: 0 24px 64px rgba(13,27,42,.14);
  --sh-xl: 0 40px 100px rgba(13,27,42,.18);

  --r-sm: 6px;
  --r-md: 12px;
  --r-lg: 20px;
  --r-xl: 32px;
  --r-full: 9999px;

  --ease:   cubic-bezier(.25,.46,.45,.94);
  --bounce: cubic-bezier(.34,1.56,.64,1);

  /* Sidebar */
  --sidebar-w: 260px;
  --topbar-h: 64px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; font-size: 16px; }
body {
  font-family: 'DM Sans', sans-serif;
  background: #F0F2F5;
  color: var(--gray800);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--gray200); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: var(--bnp-mint); }

/* ═══ LAYOUT ═══
   FIX: la grille précédente entrait en conflit avec les éléments en
   position:fixed. On garde un layout fluide basé sur le sidebar fixe +
   décalage du contenu, sans grid contradictoire. */
.dash-layout {
  min-height: 100vh;
  position: relative;
}

/* Overlay mobile pour fermer la sidebar */
.sidebar-overlay {
  position: fixed; inset: 0;
  background: rgba(5,11,20,.5);
  backdrop-filter: blur(2px);
  opacity: 0; visibility: hidden;
  transition: opacity .3s var(--ease), visibility .3s var(--ease);
  z-index: 150;
}
.sidebar-overlay.show { opacity: 1; visibility: visible; }

/* ═══ SIDEBAR ═══ */
.sidebar {
  background: var(--bnp-deeper);
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: var(--sidebar-w);
  display: flex; flex-direction: column;
  z-index: 200;
  overflow: hidden;
  transition: transform .35s var(--ease), width .35s var(--ease);
}

.sidebar::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 300px 300px at 50% -50px, rgba(212,175,55,.12), transparent),
    radial-gradient(ellipse 200px 200px at 100% 70%, rgba(0,198,122,.07), transparent);
  pointer-events: none;
}

.sidebar-logo {
  display: flex; align-items: center; gap: .65rem;
  padding: 1.4rem 1.5rem;
  text-decoration: none;
  border-bottom: 1px solid rgba(255,255,255,.05);
  position: relative; z-index: 1;
}

.sidebar-logo-mark {
  width: 38px; height: 38px;
  background: linear-gradient(135deg, var(--bnp-emerald), #B8960C);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Sora',sans-serif; font-weight: 800; font-size: .78rem;
  color: white; letter-spacing: -.02em; flex-shrink: 0;
}

.sidebar-logo-text {
  display: flex; flex-direction: column; line-height: 1;
}
.sidebar-logo-text span:first-child {
  font-family: 'Sora',sans-serif; font-weight: 700; font-size: .88rem; color: white;
}
.sidebar-logo-text span:last-child {
  font-size: .6rem; color: rgba(255,255,255,.35); letter-spacing: .06em; text-transform: uppercase; margin-top: 2px;
}

.sidebar-user {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,.05);
  display: flex; align-items: center; gap: .75rem;
  position: relative; z-index: 1;
}

.sidebar-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: linear-gradient(135deg, var(--bnp-green), var(--bnp-emerald));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Sora',sans-serif; font-weight: 700; font-size: .78rem; color: white;
  flex-shrink: 0;
  border: 2px solid rgba(212,175,55,.3);
}

.sidebar-user-info { flex: 1; overflow: hidden; }
.sidebar-user-name { font-size: .83rem; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-user-plan {
  font-size: .62rem; color: var(--bnp-emerald); letter-spacing: .06em;
  text-transform: uppercase; font-weight: 600;
}

.sidebar-nav {
  flex: 1; padding: 1rem 0; overflow-y: auto; position: relative; z-index: 1;
  -webkit-overflow-scrolling: touch;
}

.sidebar-section-label {
  font-size: .58rem; font-weight: 700; letter-spacing: .12em;
  text-transform: uppercase; color: rgba(255,255,255,.2);
  padding: .6rem 1.5rem .3rem;
}

.sidebar-link {
  display: flex; align-items: center; gap: .75rem;
  padding: .65rem 1.5rem; margin: .1rem .75rem;
  border-radius: var(--r-md);
  text-decoration: none; color: rgba(255,255,255,.45);
  font-size: .83rem; font-weight: 500;
  transition: all .22s var(--ease);
  position: relative;
}

.sidebar-link:hover {
  color: white; background: rgba(255,255,255,.07);
}

.sidebar-link.active {
  color: white; background: rgba(212,175,55,.15);
  border: 1px solid rgba(212,175,55,.2);
}

.sidebar-link.active::before {
  content: '';
  position: absolute; left: 0; top: 50%; transform: translateY(-50%);
  width: 3px; height: 60%; background: var(--bnp-emerald);
  border-radius: 0 3px 3px 0;
}

.sidebar-icon {
  width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0; opacity: .7;
  transition: opacity .2s;
}
.sidebar-link:hover .sidebar-icon,
.sidebar-link.active .sidebar-icon { opacity: 1; }

.sidebar-badge {
  margin-left: auto; background: var(--bnp-emerald);
  color: var(--bnp-dark); font-size: .58rem; font-weight: 700;
  padding: .2rem .5rem; border-radius: var(--r-full);
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255,255,255,.05);
  position: relative; z-index: 1;
}

.sidebar-footer-link {
  display: flex; align-items: center; gap: .65rem;
  font-size: .8rem; color: rgba(255,255,255,.35);
  text-decoration: none; padding: .5rem 0;
  transition: color .2s;
}
.sidebar-footer-link:hover { color: rgba(255,255,255,.7); }

/* ═══ TOPBAR ═══ */
.topbar {
  position: fixed;
  top: 0; left: var(--sidebar-w); right: 0;
  height: var(--topbar-h);
  background: rgba(240,242,245,.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(0,0,0,.06);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 2rem;
  z-index: 100;
  gap: 1rem;
  transition: left .35s var(--ease);
}

.topbar-left { display: flex; align-items: center; gap: 1rem; min-width: 0; }

.topbar-page-title {
  font-family: 'Sora',sans-serif; font-weight: 700;
  font-size: 1rem; color: var(--bnp-dark);
  white-space: nowrap;
}

.topbar-breadcrumb {
  font-size: .75rem; color: var(--gray400);
  white-space: nowrap;
}

.topbar-search {
  display: flex; align-items: center; gap: .6rem;
  background: white; border: 1.5px solid var(--gray100);
  border-radius: var(--r-full); padding: .45rem 1rem;
  transition: all .2s;
  flex: 1; max-width: 420px;
}
.topbar-search:focus-within {
  border-color: var(--bnp-emerald);
  box-shadow: 0 0 0 3px rgba(212,175,55,.1);
}
.topbar-search input {
  border: none; outline: none; background: none;
  font-family: 'DM Sans',sans-serif; font-size: .83rem;
  color: var(--gray800); width: 100%;
}
.topbar-search input::placeholder { color: var(--gray400); }

.topbar-right { display: flex; align-items: center; gap: .75rem; flex-shrink: 0; }

.topbar-btn {
  width: 38px; height: 38px; border-radius: var(--r-md);
  background: white; border: 1.5px solid var(--gray100);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; position: relative; font-size: 1rem;
  transition: all .2s; text-decoration: none; color: var(--gray600);
  flex-shrink: 0;
}
.topbar-btn:hover { border-color: var(--bnp-emerald); color: var(--bnp-emerald); }

.topbar-notif-dot {
  position: absolute; top: 6px; right: 6px;
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--red); border: 2px solid var(--off);
}

.topbar-user {
  display: flex; align-items: center; gap: .6rem;
  background: white; border: 1.5px solid var(--gray100);
  border-radius: var(--r-full); padding: .35rem .9rem .35rem .35rem;
  cursor: pointer; transition: all .2s;
}
.topbar-user:hover { border-color: var(--bnp-emerald); }
.topbar-user-av {
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg, var(--bnp-green), var(--bnp-emerald));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Sora',sans-serif; font-weight: 700; font-size: .65rem; color: white;
  flex-shrink: 0;
}
.topbar-user-name { font-size: .78rem; font-weight: 600; color: var(--gray800); }

/* ═══ MAIN CONTENT ═══ */
.dash-main {
  margin-left: var(--sidebar-w);
  margin-top: var(--topbar-h);
  padding: 2rem;
  min-height: calc(100vh - var(--topbar-h));
  transition: margin-left .35s var(--ease);
}

/* ═══ CARDS ═══ */
.card {
  background: white;
  border: 1px solid var(--gray100);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-sm);
  transition: box-shadow .3s var(--ease), transform .3s var(--ease);
}
.card:hover { box-shadow: var(--sh-md); }
.card-pad { padding: 1.5rem; }
.card-pad-lg { padding: 2rem; }

.card-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.25rem; gap: 1rem;
}
.card-title {
  font-family: 'Sora',sans-serif; font-weight: 700;
  font-size: .9rem; color: var(--bnp-dark);
}
.card-sub { font-size: .75rem; color: var(--gray400); margin-top: .15rem; }

/* ═══ STAT CARDS ═══ */
.stat-card {
  background: white; border: 1px solid var(--gray100);
  border-radius: var(--r-lg); padding: 1.4rem 1.6rem;
  box-shadow: var(--sh-sm);
  transition: all .3s var(--ease);
  position: relative; overflow: hidden;
}
.stat-card::after {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--bnp-emerald), transparent);
  opacity: 0; transition: opacity .3s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--sh-md); }
.stat-card:hover::after { opacity: 1; }

.stat-icon {
  width: 44px; height: 44px; border-radius: var(--r-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; margin-bottom: 1rem;
  background: var(--bnp-light);
}
.stat-label { font-size: .72rem; font-weight: 600; color: var(--gray400); letter-spacing: .06em; text-transform: uppercase; margin-bottom: .35rem; }
.stat-value { font-family: 'Sora',sans-serif; font-weight: 800; font-size: 1.6rem; color: var(--bnp-dark); line-height: 1; margin-bottom: .35rem; }
.stat-delta {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: .72rem; font-weight: 600; padding: .2rem .55rem;
  border-radius: var(--r-full);
}
.stat-delta.up { background: rgba(0,198,122,.1); color: var(--green); }
.stat-delta.down { background: rgba(229,55,58,.1); color: var(--red); }

/* ═══ BUTTONS ═══ */
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
  font-family: 'DM Sans',sans-serif; font-weight: 600; font-size: .82rem;
  border: none; cursor: pointer; text-decoration: none;
  border-radius: var(--r-full); padding: .65rem 1.5rem;
  transition: all .25s var(--ease); white-space: nowrap;
}
.btn-primary { background: linear-gradient(135deg, var(--bnp-emerald), #B8960C); color: white; box-shadow: 0 4px 14px rgba(212,175,55,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(212,175,55,.4); }
.btn-dark { background: var(--bnp-dark); color: white; }
.btn-dark:hover { background: var(--bnp-deeper); transform: translateY(-1px); }
.btn-outline { background: transparent; color: var(--bnp-dark); border: 1.5px solid var(--gray200); }
.btn-outline:hover { border-color: var(--bnp-emerald); color: var(--bnp-emerald); }
.btn-ghost { background: transparent; color: var(--gray600); }
.btn-ghost:hover { color: var(--bnp-dark); background: var(--bnp-light); }
.btn-danger { background: rgba(229,55,58,.1); color: var(--red); }
.btn-danger:hover { background: var(--red); color: white; }
.btn-sm { padding: .4rem 1rem; font-size: .75rem; }
.btn-lg { padding: .85rem 2rem; font-size: .9rem; }
.btn-icon { padding: .5rem; border-radius: var(--r-md); aspect-ratio: 1; }

/* ═══ BADGE ═══ */
.badge {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: .68rem; font-weight: 600; letter-spacing: .04em;
  padding: .22rem .7rem; border-radius: var(--r-full);
}
.badge-gold   { background: rgba(212,175,55,.12); color: var(--gold); }
.badge-green  { background: rgba(0,198,122,.12); color: var(--green); }
.badge-red    { background: rgba(229,55,58,.12); color: var(--red); }
.badge-blue   { background: rgba(26,115,232,.12); color: var(--blue); }
.badge-gray   { background: var(--gray100); color: var(--gray600); }
.badge-purple { background: rgba(124,58,237,.12); color: var(--purple); }

/* ═══ PROGRESS BAR ═══ */
.progress-wrap { background: var(--gray100); border-radius: 99px; overflow: hidden; height: 6px; }
.progress-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--bnp-green), var(--bnp-emerald)); transition: width 1.2s var(--ease); }
.progress-fill.green { background: linear-gradient(90deg, #00A86B, #00C67A); }
.progress-fill.red { background: linear-gradient(90deg, #C0392B, #E5373A); }
.progress-fill.blue { background: linear-gradient(90deg, #1557B0, #1A73E8); }

/* ═══ ANIMATIONS ═══ */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
@keyframes scaleIn {
  from { opacity: 0; transform: scale(.95); }
  to   { opacity: 1; transform: scale(1); }
}

.anim-up  { animation: fadeInUp .5s var(--ease) both; }
.anim-in  { animation: fadeIn .4s var(--ease) both; }
.anim-scale { animation: scaleIn .4s var(--bounce) both; }
.d1 { animation-delay: .05s; }
.d2 { animation-delay: .10s; }
.d3 { animation-delay: .15s; }
.d4 { animation-delay: .20s; }
.d5 { animation-delay: .25s; }
.d6 { animation-delay: .30s; }

/* ═══ TOAST ═══ */
.toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 2000; display: flex; flex-direction: column; gap: .6rem; }
.toast {
  background: white; border-radius: var(--r-lg);
  box-shadow: var(--sh-lg); padding: .9rem 1.2rem;
  display: flex; align-items: center; gap: .75rem;
  font-size: .83rem; font-weight: 500; color: var(--gray800);
  border-left: 3px solid var(--bnp-emerald);
  transform: translateX(120%); opacity: 0;
  transition: all .4s var(--bounce);
  min-width: 260px; max-width: 340px;
}
.toast.show { transform: translateX(0); opacity: 1; }
.toast.success { border-left-color: var(--green); }
.toast.error { border-left-color: var(--red); }
.toast.info { border-left-color: var(--blue); }
.toast-icon { font-size: 1.2rem; flex-shrink: 0; }

/* ═══ SIDEBAR TOGGLE MOBILE ═══ */
.sidebar-toggle {
  display: none; cursor: pointer;
  width: 38px; height: 38px; border-radius: var(--r-md);
  background: white; border: 1.5px solid var(--gray100);
  align-items: center; justify-content: center; font-size: 1.1rem;
  color: var(--gray600); transition: all .2s; flex-shrink: 0;
}
.sidebar-toggle:hover { border-color: var(--bnp-emerald); color: var(--bnp-emerald); }

/* ── DASHBOARD SPECIFIC GRIDS ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}
.middle-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
  align-items: start;
}
.middle-col {
  display: flex; flex-direction: column; gap: 1.25rem;
}
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.25rem;
  align-items: start;
}
.bottom-col {
  display: flex; flex-direction: column; gap: 1.25rem;
}

/* Balance Hero Card */
.balance-hero {
  background: linear-gradient(145deg, var(--bnp-deeper) 0%, #0a1f2e 60%, #05140f 100%);
  border-radius: var(--r-xl);
  padding: 2rem 2.2rem;
  position: relative; overflow: hidden;
  color: white;
  box-shadow: var(--sh-xl);
  grid-column: span 4;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 2rem;
  align-items: center;
}
.balance-hero::before {
  content:'';
  position:absolute; inset:0;
  background:
    radial-gradient(ellipse 400px 300px at 0% 0%, rgba(212,175,55,.15), transparent),
    radial-gradient(ellipse 300px 300px at 100% 100%, rgba(0,198,122,.08), transparent);
  pointer-events:none;
}
.balance-hero-grid {
  position:absolute; inset:0; opacity:.03;
  background-image:
    linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
  background-size:32px 32px;
}
.bh-block { position: relative; z-index: 1; }
.bh-label { font-size:.68rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.4); margin-bottom:.4rem; }
.bh-value { font-family:'Sora',sans-serif; font-weight:800; font-size:2.4rem; color:white; line-height:1; }
.bh-delta {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:.72rem; font-weight:600; margin-top:.4rem;
  background:rgba(0,198,122,.15); color:#00C67A;
  padding:.2rem .65rem; border-radius:99px;
}
.bh-sub { font-size:.78rem; color:rgba(255,255,255,.45); margin-top:.3rem; }
.bh-divider { width:1px; height:70px; background:rgba(255,255,255,.1); justify-self:center; }

/* Mini sparkline inline */
.sparkline-wrap { display:flex; align-items:flex-end; gap:3px; height:36px; margin-top:.75rem; }
.spark-bar {
  flex:1; border-radius:3px 3px 0 0;
  background:rgba(212,175,55,.3);
  transition: background .2s;
}
.spark-bar.active { background:var(--bnp-emerald); }
.spark-bar:hover { background:var(--bnp-emerald); }

/* Chart (SVG area) */
.area-chart-wrap { padding: 1.5rem; }
.chart-tabs { display:flex; gap:.35rem; margin-bottom:1.2rem; flex-wrap:wrap; }
.chart-tab {
  padding:.35rem .9rem; border-radius:99px; font-size:.75rem; font-weight:600;
  background:transparent; border:none; cursor:pointer; color:var(--gray400);
  transition:all .2s;
}
.chart-tab.active { background:var(--bnp-dark); color:white; }

/* Quick actions */
.quick-actions { display:flex; gap:1rem; flex-wrap:wrap; }
.quick-action {
  flex:1; min-width:110px;
  display:flex; flex-direction:column; align-items:center; gap:.6rem;
  padding:1.2rem 1rem; border-radius:var(--r-lg);
  background:var(--gray50); border:1.5px solid var(--gray100);
  cursor:pointer; text-decoration:none; color:var(--gray800);
  transition:all .25s var(--ease);
  text-align:center;
}
.quick-action:hover {
  background:white; border-color:var(--bnp-emerald);
  transform:translateY(-3px); box-shadow:var(--sh-md);
  color:var(--bnp-dark);
}
.qa-icon {
  width:46px; height:46px; border-radius:var(--r-md);
  display:flex; align-items:center; justify-content:center;
  font-size:1.3rem;
  background:white; box-shadow:var(--sh-sm);
  transition: transform .25s var(--bounce);
}
.quick-action:hover .qa-icon { transform:scale(1.1) rotate(-4deg); }
.qa-label { font-size:.75rem; font-weight:600; }

/* Transactions */
.tx-row {
  display:flex; align-items:center; gap:.85rem;
  padding:.85rem 0; border-bottom:1px solid var(--gray50);
}
.tx-row:last-child { border-bottom:none; }
.tx-icon-wrap {
  width:40px; height:40px; border-radius:var(--r-md); flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.tx-info { flex:1; min-width:0; }
.tx-name { font-size:.83rem; font-weight:600; color:var(--bnp-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tx-date { font-size:.7rem; color:var(--gray400); margin-top:.1rem; }
.tx-amount { font-family:'Sora',sans-serif; font-weight:700; font-size:.88rem; white-space:nowrap; }
.tx-amount.pos { color:var(--green); }
.tx-amount.neg { color:var(--red); }

/* Budget ring progress */
.budget-ring-wrap { display:flex; flex-direction:column; gap:.85rem; }
.budget-item-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.4rem; }
.budget-cat { font-size:.8rem; font-weight:600; color:var(--gray800); display:flex; align-items:center; gap:.5rem; }
.budget-cat-dot { width:8px; height:8px; border-radius:50%; }
.budget-vals { font-size:.75rem; color:var(--gray400); }
.budget-vals strong { color:var(--bnp-dark); font-family:'Sora',sans-serif; }

/* Card widget */
.card-widget {
  background:linear-gradient(135deg, #0D1B2A 0%, #1a2e40 100%);
  border-radius: var(--r-xl); padding:1.5rem;
  color:white; position:relative; overflow:hidden;
}
.card-widget::before {
  content:'';
  position:absolute; top:-40px; right:-40px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(212,175,55,.1);
}
.card-widget::after {
  content:'';
  position:absolute; bottom:-30px; left:30px;
  width:100px; height:100px; border-radius:50%;
  background:rgba(0,198,122,.06);
}
.cw-chip-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; position:relative; z-index:1; }
.cw-chip { width:36px; height:28px; border-radius:5px; background:linear-gradient(135deg,var(--bnp-emerald),#B8960C); }
.cw-network { font-size:1.2rem; opacity:.7; font-weight:700; font-style:italic; }
.cw-number { font-family:'JetBrains Mono',monospace; font-size:.95rem; letter-spacing:.12em; color:rgba(255,255,255,.8); margin-bottom:1.2rem; position:relative; z-index:1; }
.cw-footer { display:flex; justify-content:space-between; align-items:flex-end; position:relative; z-index:1; gap:1rem; }
.cw-holder-label { font-size:.6rem; color:rgba(255,255,255,.4); letter-spacing:.08em; text-transform:uppercase; }
.cw-holder-name { font-family:'Sora',sans-serif; font-weight:700; font-size:.88rem; margin-top:.15rem; }
.cw-expiry-label { font-size:.6rem; color:rgba(255,255,255,.4); letter-spacing:.08em; text-transform:uppercase; }
.cw-expiry-val { font-family:'Sora',sans-serif; font-weight:700; font-size:.88rem; margin-top:.15rem; text-align:right; }

/* Savings goals */
.goal-item { display:flex; align-items:center; gap:.9rem; padding:.75rem 0; border-bottom:1px solid var(--gray50); }
.goal-item:last-child { border-bottom:none; }
.goal-icon { width:42px; height:42px; border-radius:var(--r-md); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; background:var(--bnp-light); }
.goal-info { flex:1; min-width:0; }
.goal-name { font-size:.82rem; font-weight:600; color:var(--bnp-dark); }
.goal-bar-wrap { display:flex; align-items:center; gap:.6rem; margin-top:.4rem; }
.goal-bar-track { flex:1; height:5px; background:var(--gray100); border-radius:99px; overflow:hidden; }
.goal-bar-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,var(--bnp-green),var(--bnp-emerald)); transition: width 1.2s var(--ease); }
.goal-pct { font-size:.68rem; font-weight:700; color:var(--bnp-emerald); font-family:'Sora',sans-serif; }
.goal-amount { text-align:right; font-size:.75rem; color:var(--gray400); flex-shrink:0; }
.goal-amount strong { display:block; font-family:'Sora',sans-serif; font-weight:700; font-size:.85rem; color:var(--bnp-dark); }

/* Notif items */
.notif-item { display:flex; align-items:flex-start; gap:.75rem; padding:.8rem 0; border-bottom:1px solid var(--gray50); }
.notif-item:last-child { border-bottom:none; }
.notif-dot { width:8px; height:8px; border-radius:50%; background:var(--bnp-emerald); flex-shrink:0; margin-top:.35rem; }
.notif-dot.read { background:var(--gray200); }
.notif-text { font-size:.8rem; color:var(--gray800); line-height:1.5; }
.notif-time { font-size:.68rem; color:var(--gray400); margin-top:.2rem; }

/* Donut */
.donut-wrap-center { display:flex; justify-content:center; margin-bottom:1.25rem; }
.donut-box { position:relative; width:130px; height:130px; }
.donut-box svg { transform:rotate(-90deg); }
.donut-box-center { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.donut-box-val { font-family:'Sora',sans-serif; font-weight:800; font-size:1.3rem; color:var(--bnp-dark); }
.donut-box-label { font-size:.62rem; color:var(--gray400); }

/* ═══════════════════════════════════════════════
   RESPONSIVE — corrigé & complet
═══════════════════════════════════════════════ */

/* ≤ 1200px : on resserre les grilles larges */
@media (max-width: 1200px) {
  .middle-grid { grid-template-columns: 1.4fr 1fr; }
  .bottom-grid { grid-template-columns: 1fr 1fr; }
}

/* ≤ 1024px : tablette paysage */
@media (max-width: 1024px) {
  :root { --sidebar-w: 220px; }
  .topbar-search { display: none; }
  .stats-grid { grid-template-columns: repeat(2,1fr); }
  .balance-hero { grid-template-columns: 1fr; gap: 1.5rem; padding: 1.75rem; }
  .bh-divider { display: none; }
  .bh-value { font-size: 2rem; }
  .middle-grid { grid-template-columns: 1fr; }
  .bottom-grid { grid-template-columns: 1fr 1fr; }
}

/* ≤ 768px : tablette portrait / grand mobile */
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); width: 270px; }
  .sidebar.open { transform: translateX(0); }
  .topbar { left: 0; padding: 0 1rem; }
  .dash-main { margin-left: 0; padding: 1.2rem; }
  .sidebar-toggle { display: flex; }
  .topbar-search { display: none; }
  .topbar-user-name { display: none; }
  .bottom-grid { grid-template-columns: 1fr; }
  .topbar-page-title { font-size: .92rem; }
}

/* ≤ 560px : mobile */
@media (max-width: 560px) {
  .stats-grid { grid-template-columns: 1fr 1fr; gap: .85rem; }
  .quick-action { min-width: calc(50% - .5rem); flex: 0 0 calc(50% - .5rem); }
  .bh-value { font-size: 1.9rem; }
}

/* ≤ 420px : petit mobile */
@media (max-width: 420px) {
  .dash-main { padding: 1rem .8rem; }
  .stats-grid { grid-template-columns: 1fr; }
  .balance-hero { padding: 1.4rem; }
  .bh-value { font-size: 1.75rem; }
  .area-chart-wrap { padding: 1.1rem; }
  .toast-container { left: 1rem; right: 1rem; bottom: 1rem; }
  .toast { min-width: 0; max-width: none; }
  .topbar-breadcrumb { display: none; }
}

/* Accessibilité : respect des préférences de mouvement réduit */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: .01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: .01ms !important;
    scroll-behavior: auto !important;
  }
}
.modal-overlay{position:fixed;inset:0;background:rgba(5,11,20,.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:600;opacity:0;visibility:hidden;transition:.3s;padding:1rem}.modal-overlay.open{opacity:1;visibility:visible}.modal-box{background:#fff;border-radius:var(--r-xl);width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 40px 100px rgba(13,27,42,.2);transform:scale(.95) translateY(20px);transition:.35s var(--bounce)}.modal-overlay.open .modal-box{transform:none}.modal-head{padding:1.4rem 1.5rem 0;display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}.modal-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.98rem;color:var(--bnp-dark)}.modal-close{width:32px;height:32px;border-radius:50%;background:var(--gray50);border:1px solid var(--gray100);cursor:pointer;font-size:.95rem;color:var(--gray600);display:flex;align-items:center;justify-content:center;transition:.2s}.modal-body{padding:0 1.5rem 1.25rem}.modal-foot{padding:.25rem 1.5rem 1.5rem;display:flex;gap:.5rem}.modal-foot .btn{flex:1}.form-group{margin-bottom:.85rem}.form-label{display:block;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--gray600);margin-bottom:.35rem}.form-input,.form-select,.form-textarea{width:100%;padding:.7rem 1rem;font-family:'DM Sans',sans-serif;font-size:.84rem;color:var(--gray800);background:var(--gray50);border:1.5px solid var(--gray100);border-radius:var(--r-md);outline:none;transition:.2s}.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--bnp-emerald);background:#fff;box-shadow:0 0 0 3px rgba(212,175,55,.12)}.form-textarea{resize:vertical;min-height:90px}.form-hint{font-size:.7rem;color:var(--gray400);margin-top:.2rem}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}.tbl-wrap{overflow-x:auto;border-radius:var(--r-lg);border:1px solid var(--gray100)}.tbl{width:100%;border-collapse:collapse;min-width:540px}.tbl thead th{padding:.65rem 1rem;font-size:.63rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gray400);text-align:left;background:var(--gray50);border-bottom:1.5px solid var(--gray100);white-space:nowrap}.tbl tbody td{padding:.82rem 1rem;font-size:.82rem;color:var(--gray800);border-bottom:1px solid var(--gray50)}.tbl tbody tr:hover{background:rgba(212,175,55,.03)}.tbl tbody tr:last-child td{border-bottom:none}.chips{display:flex;gap:.4rem;flex-wrap:wrap}.chip{padding:.36rem .82rem;border-radius:999px;font-size:.74rem;font-weight:600;cursor:pointer;background:#fff;border:1.5px solid var(--gray100);color:var(--gray600);transition:.2s}.chip.active{background:var(--bnp-dark);border-color:var(--bnp-dark);color:#fff}.toggle{width:42px;height:24px;border-radius:12px;background:var(--gray200);position:relative;cursor:pointer;transition:background .25s;flex-shrink:0;border:none}.toggle.on{background:var(--green)}.toggle::after{content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;top:3px;left:3px;transition:transform .25s var(--bounce);box-shadow:0 1px 4px rgba(0,0,0,.15)}.toggle.on::after{transform:translateX(18px)}.sec-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}.pg-title{font-family:'Sora',sans-serif;font-weight:800;font-size:clamp(1.1rem,2.5vw,1.4rem);color:var(--bnp-dark)}.pg-sub{font-size:.82rem;color:var(--gray400);margin-top:.3rem}.row-item{display:flex;align-items:center;gap:.85rem;padding:.85rem 0;border-bottom:1px solid var(--gray50)}.row-item:last-child{border-bottom:none}.row-icon{width:42px;height:42px;border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}.row-info{flex:1;min-width:0}.row-title{font-size:.85rem;font-weight:600;color:var(--bnp-dark)}.row-sub{font-size:.72rem;color:var(--gray400);margin-top:.12rem}.info-box{background:rgba(26,115,232,.06);border:1px solid rgba(26,115,232,.2);border-radius:var(--r-md);padding:.85rem 1rem;font-size:.8rem;color:var(--blue);line-height:1.55}@media(max-width:900px){.form-row{grid-template-columns:1fr}}</style><link rel="stylesheet" href="../mobile.css">
</head>
<body><div class="dash-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <a href="../index.php" class="sidebar-logo"><div class="sidebar-logo-mark">GTB</div><div class="sidebar-logo-text"><span>Global Trust Bank</span><span>Espace client</span></div></a>
  <div class="sidebar-user"><div class="sidebar-avatar"><?php echo $initials;?></div><div class="sidebar-user-info"><div class="sidebar-user-name"><?php echo e(($u['first_name']??'').' '.($u['last_name']??''));?></div><div class="sidebar-user-plan"><?php echo ($u['plan']??'')==='premium'?'✦ Premium':'Standard';?></div></div></div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">⌂</span> Tableau de bord </a>
    <a href="../comptes/index.php" class="sidebar-link"><span class="sidebar-icon">⊞</span> Mes Comptes </a>
    <a href="../cartes/index.php" class="sidebar-link"><span class="sidebar-icon">▣</span> Cartes </a>
    <a href="../virement/index.php" class="sidebar-link"><span class="sidebar-icon">⇄</span> Virements </a>
    <div class="sidebar-section-label">Épargne &amp; Invest.</div>
    <a href="../investissement/index.php" class="sidebar-link"><span class="sidebar-icon">◈</span> Investissements </a>
    <a href="../credits/index.php" class="sidebar-link"><span class="sidebar-icon">◎</span> Crédits </a>
    <a href="../assurance/index.php" class="sidebar-link active"><span class="sidebar-icon">◉</span> Assurances </a>
    <div class="sidebar-section-label">Autres</div>
    <a href="../avantage/index.php" class="sidebar-link"><span class="sidebar-icon">✦</span> Avantages </a>
    <a href="../sms-banking/index.php" class="sidebar-link"><span class="sidebar-icon">✉</span> SMS Banking </a>
    <a href="../profil/index.php" class="sidebar-link"><span class="sidebar-icon">👤</span> Mon Profil </a>
    <a href="../parametres/index.php" class="sidebar-link"><span class="sidebar-icon">⚙</span> Paramètres </a>
    <a href="../support/index.php" class="sidebar-link"><span class="sidebar-icon">❓</span> Support </a>

  </nav>
  <div class="sidebar-footer"><a href="../../authentification/api/logout.php" class="sidebar-footer-link" onclick="return confirm('Se déconnecter ?')"><span>🔒</span> Déconnexion</a></div>
</aside>
<header class="topbar"><div class="topbar-left"><button class="sidebar-toggle" id="sidebarToggle">☰</button><div><div class="topbar-page-title"><?php echo $title??'';?></div><div class="topbar-breadcrumb"><?php echo $breadcrumb??'';?></div></div></div>
<div class="topbar-right"><a href="../notifications.php" class="topbar-btn">🔔<?php if($notif_count>0):?><span class="topbar-notif-dot"></span><?php endif;?></a><div class="topbar-user" onclick="location.href='../profil/index.php'" style="cursor:pointer"><div class="topbar-user-av"><?php echo $initials;?></div><span class="topbar-user-name"><?php echo e($u['first_name']??'');?></span></div></div></header>
<main class="dash-main">
<?php
$title='Assurances'; $breadcrumb='Tableau de bord · Assurances';
?>
<div class="sec-head"><div><h1 class="pg-title">Mes Assurances</h1><p class="pg-sub">Protégez ce qui compte</p></div>
<a href="souscription.php" class="btn btn-primary btn-sm">+ Souscrire</a></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;margin-bottom:1.5rem">
  <?php
  $produits=[
    ['🏠','Assurance Habitation','Couverture complète multi-risques','À partir de 12€/mois','habitation'],
    ['🚗','Assurance Auto','Tous risques ou tiers','À partir de 45€/mois','auto'],
    ['💼','Assurance Vie','Épargne + protection','Rendement 2,8%/an','vie'],
    ['❤️','Assurance Santé','Complémentaire santé GTB','À partir de 28€/mois','sante'],
    ['📱','Assurance Mobile','Casse, vol, oxydation','3,99€/mois','mobile'],
    ['✈️','Assurance Voyage','Couverture internationale','8€/voyage','voyage'],
  ];
  foreach($produits as $p): ?>
  <div class="card card-pad" style="cursor:pointer" onclick="window.location='souscription.php?type=<?php echo $p[4];?>'">
    <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:.85rem">
      <div style="font-size:1.5rem"><?php echo $p[0];?></div>
      <div><div style="font-family:'Sora',sans-serif;font-weight:700;color:var(--bnp-dark)"><?php echo $p[1];?></div><div style="font-size:.72rem;color:var(--bnp-emerald);font-weight:600"><?php echo $p[3];?></div></div>
    </div>
    <div style="font-size:.8rem;color:var(--gray600)"><?php echo $p[2];?></div>
    <button class="btn btn-outline btn-sm" style="margin-top:1rem" onclick="event.stopPropagation();window.location='souscription.php?type=<?php echo $p[4];?>'">Souscrire</button>
  </div>
  <?php endforeach;?>
</div>
<div class="card card-pad">
  <div class="card-header"><div class="card-title">Mes contrats actifs</div></div>
  <div style="text-align:center;padding:2rem;color:var(--gray400)">Aucun contrat actif — <a href="souscription.php" style="color:var(--bnp-emerald)">Souscrire maintenant</a></div>
</div>
</main></div><div class="toast-container" id="toastContainer"></div><script>
(function(){'use strict';
const sb=document.getElementById('sidebar'),tgl=document.getElementById('sidebarToggle'),ov=document.getElementById('sidebarOverlay');
function openSb(){sb?.classList.add('open');ov?.classList.add('show');document.body.style.overflow='hidden';}
function closeSb(){sb?.classList.remove('open');ov?.classList.remove('show');document.body.style.overflow='';}
tgl?.addEventListener('click',()=>sb?.classList.contains('open')?closeSb():openSb());
ov?.addEventListener('click',closeSb);
sb?.querySelectorAll('.sidebar-link').forEach(l=>l.addEventListener('click',()=>{if(window.innerWidth<=768)closeSb();}));
window.addEventListener('resize',()=>{if(window.innerWidth>768)closeSb();});
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeSb();});
window.showToast=function(m,t,i){const c=document.getElementById('toastContainer');if(!c)return;const ic={success:'✓',error:'✕',info:'ℹ',warning:'⚠'};const x=document.createElement('div');x.className='toast '+(t||'success');x.innerHTML=`<span class="toast-icon">${i||ic[t||'success']||'•'}</span><span>${m}</span>`;c.appendChild(x);requestAnimationFrame(()=>setTimeout(()=>x.classList.add('show'),25));setTimeout(()=>{x.classList.remove('show');setTimeout(()=>x.remove(),400);},3600);};
window.openModal=function(id){const m=document.getElementById(id);if(m){m.classList.add('open');document.body.style.overflow='hidden';}};
window.closeModal=function(id){const m=document.getElementById(id);if(m){m.classList.remove('open');document.body.style.overflow='';}};
document.querySelectorAll('.modal-overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o){o.classList.remove('open');document.body.style.overflow='';}}));
window.confirmDlg=function(msg,onOk,type){const clr={danger:'var(--red)',warning:'#B45309',success:'var(--green)'};const t2=type||'danger';const d=document.createElement('div');d.style.cssText='position:fixed;inset:0;background:rgba(5,11,20,.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:9000;padding:1rem';d.innerHTML=`<div style="background:#fff;border-radius:var(--r-xl);width:100%;max-width:360px;overflow:hidden;box-shadow:0 40px 100px rgba(13,27,42,.25)"><div style="padding:1.8rem 1.5rem;text-align:center"><div style="font-size:2.2rem;margin-bottom:.75rem">${t2==='danger'?'⚠️':'✅'}</div><div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--bnp-dark);margin-bottom:.5rem">Confirmation</div><p style="font-size:.82rem;color:var(--gray600);line-height:1.6">${msg}</p></div><div style="display:flex;gap:.5rem;padding:1rem 1.25rem;border-top:1px solid var(--gray100)"><button style="flex:1;padding:.68rem;border-radius:999px;border:1.5px solid var(--gray200);background:#fff;font-family:'DM Sans',sans-serif;font-weight:600;cursor:pointer" id="_cc">Annuler</button><button style="flex:1;padding:.68rem;border-radius:999px;background:${clr[t2]};color:#fff;border:none;font-family:'DM Sans',sans-serif;font-weight:700;cursor:pointer" id="_co">Confirmer</button></div></div>`;document.body.appendChild(d);d.querySelector('#_cc').onclick=()=>d.remove();d.querySelector('#_co').onclick=()=>{d.remove();onOk?.();};d.addEventListener('click',e=>{if(e.target===d)d.remove();});};
document.querySelectorAll('.toggle').forEach(t=>{t.addEventListener('click',()=>{t.classList.toggle('on');showToast((t.dataset.label||'Option')+(t.classList.contains('on')?' activé':' désactivé'),t.classList.contains('on')?'success':'info');});});
})();
</script></body></html>
