<?php
/* header.php — GTB Dashboard Light
 * Variables attendues de la page appelante :
 *   $pageTitle   (string) — titre affiché dans <title>
 *   $navActive   (string) — 'home'|'virement'|'cartes'|'transactions'|'comptes'|'more'
 *   $notif_count (int)    — nombre de notifications non lues
 *   $currentUser (array)  — injecté par auth_required.php
 *
 * Profondeur du dossier :
 *   $depth = 0  → fichier à la racine  (index.php, transactions.php…)
 *   $depth = 1  → sous-dossier         (comptes/, virement/, cartes/…)
 */
if (!isset($depth)) $depth = 0;
$_base = str_repeat('../', $depth);

$_u        = $currentUser ?? [];
$_fn       = e($_u['first_name'] ?? $_u['prenom'] ?? '');
$_ln       = e($_u['last_name']  ?? $_u['nom']    ?? '');
$_initials = strtoupper(substr($_fn,0,1).substr($_ln,0,1)) ?: 'GT';
$_avatar   = !empty($_u['avatar_url']) ? e($_u['avatar_url']) : '';
$_notifs   = (int)($notif_count ?? 0);
$_title    = $pageTitle ?? 'GTB Dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover"/>
<title><?= e($_title) ?> — GTB</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230D1B2A'/%3E%3Ctext x='50%25' y='50%25' font-family='Arial' font-weight='900' font-size='13' fill='%23D4AF37' text-anchor='middle' dominant-baseline='central'%3EGTB%3C/text%3E%3C/svg%3E"/>
<style>
:root{
  --bg:#F0F4F8;
  --card:#FFFFFF;
  --gold:#D4AF37;
  --gold2:#B8960C;
  --dark:#0D1B2A;
  --green:#00C67A;
  --green-light:rgba(0,198,122,.10);
  --red:#E5373A;
  --red-light:rgba(229,55,58,.10);
  --gold-light:rgba(212,175,55,.10);
  --sub:#6B7A8D;
  --sub2:#94A3B8;
  --border:#E2E8F0;
  --border2:#F1F5F9;
  --topbar-h:60px;
  --nav-h:68px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--dark);overflow-x:hidden;-webkit-font-smoothing:antialiased;padding-bottom:calc(var(--nav-h) + env(safe-area-inset-bottom))}
a{text-decoration:none;color:inherit}
button{font-family:inherit;cursor:pointer;border:none;background:none}
img{max-width:100%;display:block}

/* ── TOPBAR ── */
.gtb-topbar{
  position:fixed;top:0;left:0;right:0;z-index:400;
  height:var(--topbar-h);
  background:var(--card);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 16px;
  box-shadow:0 1px 8px rgba(13,27,42,.06);
}
.gtb-topbar-logo{display:flex;align-items:center;gap:10px}
.gtb-topbar-logo-mark{
  width:36px;height:36px;border-radius:10px;
  background:linear-gradient(135deg,var(--dark),#1a3352);
  display:flex;align-items:center;justify-content:center;
  font-weight:900;font-size:11px;color:var(--gold);letter-spacing:.5px;
}
.gtb-topbar-logo-text{line-height:1.2}
.gtb-topbar-logo-text strong{display:block;font-size:13px;font-weight:700;color:var(--dark)}
.gtb-topbar-logo-text span{display:block;font-size:10px;color:var(--sub);letter-spacing:.04em}
.gtb-topbar-right{display:flex;align-items:center;gap:8px}
.gtb-topbar-btn{
  width:36px;height:36px;border-radius:50%;
  background:var(--bg);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  position:relative;flex-shrink:0;transition:background .2s;
}
.gtb-topbar-btn:hover{background:var(--border)}
.gtb-notif-badge{
  position:absolute;top:4px;right:4px;
  min-width:16px;height:16px;border-radius:8px;
  background:var(--red);color:#fff;
  font-size:9px;font-weight:700;line-height:16px;text-align:center;padding:0 3px;
  border:2px solid var(--card);
}
.gtb-avatar{
  width:36px;height:36px;border-radius:50%;
  background:linear-gradient(135deg,var(--dark),var(--gold2));
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:700;color:#fff;
  overflow:hidden;flex-shrink:0;border:2px solid var(--gold-light);
  cursor:pointer;
}
.gtb-avatar img{width:100%;height:100%;object-fit:cover}

/* ── PAGE BODY ── */
.gtb-body{
  padding-top:calc(var(--topbar-h) + 16px);
  padding-left:16px;padding-right:16px;
  min-height:100vh;
}

/* ── CARDS ── */
.gtb-card{background:var(--card);border-radius:16px;border:1px solid var(--border);overflow:hidden}

/* ── HERO CARD ── */
.gtb-hero{
  background:linear-gradient(145deg,var(--dark) 0%,#1a3352 100%);
  border-radius:20px;padding:20px;color:#fff;position:relative;overflow:hidden;
  margin-bottom:16px;
}
.gtb-hero::before{
  content:'';position:absolute;top:-40px;right:-40px;
  width:180px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(212,175,55,.18),transparent 70%);
  pointer-events:none;
}
.gtb-hero::after{
  content:'';position:absolute;bottom:-50px;left:-20px;
  width:160px;height:160px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,198,122,.08),transparent 70%);
  pointer-events:none;
}

/* ── SECTION ── */
.gtb-section-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:12px;
}
.gtb-page-title{font-size:20px;font-weight:700;color:var(--dark)}
.gtb-page-sub{font-size:13px;color:var(--sub);margin-top:2px}

/* ── BUTTONS ── */
.gtb-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:6px;
  border-radius:99px;font-weight:600;font-size:13px;
  padding:8px 18px;transition:all .2s;white-space:nowrap;cursor:pointer;
}
.gtb-btn-primary{background:var(--gold);color:#fff;box-shadow:0 4px 14px rgba(212,175,55,.3)}
.gtb-btn-primary:hover{background:var(--gold2)}
.gtb-btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--dark)}
.gtb-btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.gtb-btn-dark{background:var(--dark);color:#fff}
.gtb-btn-sm{padding:6px 14px;font-size:12px}
.gtb-btn-ghost{background:transparent;color:var(--sub);padding:6px 10px}
.gtb-btn-ghost:hover{color:var(--dark)}
.gtb-btn-danger{background:var(--red-light);color:var(--red);border:1.5px solid var(--red)}

/* ── FORMS ── */
.gtb-label{display:block;font-size:11px;font-weight:600;color:var(--sub);letter-spacing:.05em;text-transform:uppercase;margin-bottom:6px}
.gtb-input{
  width:100%;padding:11px 14px;border-radius:12px;
  border:1.5px solid var(--border);background:var(--bg);
  font-size:14px;color:var(--dark);outline:none;
  transition:border-color .2s,background .2s;
}
.gtb-input:focus{border-color:var(--gold);background:var(--card);box-shadow:0 0 0 3px rgba(212,175,55,.1)}
.gtb-input::placeholder{color:var(--sub2)}
select.gtb-input{cursor:pointer}

/* ── LIST ITEMS ── */
.gtb-list-item{
  display:flex;align-items:center;gap:12px;
  padding:14px 16px;border-bottom:1px solid var(--border2);
}
.gtb-list-item:last-child{border-bottom:none}
.gtb-list-icon{
  width:40px;height:40px;border-radius:12px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.gtb-list-info{flex:1;min-width:0}
.gtb-list-title{font-size:14px;font-weight:600;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gtb-list-sub{font-size:12px;color:var(--sub);margin-top:2px}

/* ── BADGE ── */
.gtb-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.gtb-badge-green{background:var(--green-light);color:var(--green)}
.gtb-badge-red{background:var(--red-light);color:var(--red)}
.gtb-badge-gold{background:var(--gold-light);color:var(--gold2)}
.gtb-badge-gray{background:var(--bg);color:var(--sub);border:1px solid var(--border)}

/* ── PROGRESS ── */
.gtb-progress{height:6px;background:var(--border);border-radius:99px;overflow:hidden}
.gtb-progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--dark),var(--gold));transition:width 1s ease}

/* ── ALERTS ── */
.gtb-alert{padding:12px 14px;border-radius:12px;font-size:13px;margin-bottom:12px}
.gtb-alert-success{background:var(--green-light);color:#047857}
.gtb-alert-error{background:var(--red-light);color:#b91c1c}
.gtb-alert-info{background:var(--gold-light);color:#92400e}

/* ── QUICK ACTIONS ── */
.gtb-actions-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px}
.gtb-action-item{
  display:flex;flex-direction:column;align-items:center;gap:6px;
  padding:14px 8px;background:var(--card);border-radius:14px;
  border:1px solid var(--border);cursor:pointer;transition:all .2s;
  text-align:center;
}
.gtb-action-item:hover{border-color:var(--gold);box-shadow:0 4px 12px rgba(212,175,55,.15)}
.gtb-action-icon{
  width:44px;height:44px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;
}
.gtb-action-label{font-size:11px;font-weight:600;color:var(--sub)}

/* ── BALANCE CARD (comptes) ── */
.gtb-balance-card{
  background:linear-gradient(145deg,var(--dark) 0%,#1a3352 100%);
  border-radius:20px;padding:22px;color:#fff;position:relative;overflow:hidden;
}
.gtb-balance-card::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(212,175,55,.18),transparent 70%);pointer-events:none}

/* ── CARD TITLE ── */
.gtb-card-title{font-size:13px;font-weight:700;color:var(--dark);padding:16px 20px 12px;border-bottom:1px solid var(--border)}

/* ── FORMS ── */
.gtb-form-group{margin-bottom:14px}
.gtb-select{
  width:100%;padding:11px 14px;border-radius:12px;
  border:1.5px solid var(--border);background:var(--bg);
  font-size:14px;color:var(--dark);outline:none;cursor:pointer;
  transition:border-color .2s;appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236B7A8D' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 14px center;padding-right:36px;
}
.gtb-select:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(212,175,55,.1)}
.gtb-two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.gtb-btn-full{width:100%;justify-content:center}

/* ── TOGGLE SWITCH ── */
.gtb-toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.gtb-toggle input{opacity:0;width:0;height:0;position:absolute}
.gtb-toggle-slider{position:absolute;inset:0;background:var(--border);border-radius:99px;cursor:pointer;transition:.3s}
.gtb-toggle-slider::before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.3s}
.gtb-toggle input:checked+.gtb-toggle-slider{background:var(--green)}
.gtb-toggle input:checked+.gtb-toggle-slider::before{transform:translateX(20px)}

/* ── RESPONSIVE ── */
@media(min-width:768px){
  .gtb-body{max-width:640px;margin:0 auto;padding-left:24px;padding-right:24px}
  .gtb-topbar{max-width:640px;left:50%;transform:translateX(-50%);border-radius:0}
  .gtb-bottom-nav{max-width:640px;left:50%;transform:translateX(-50%)}
}
@media(min-width:1024px){
  .gtb-body{max-width:720px}
  .gtb-topbar{max-width:720px}
  .gtb-bottom-nav{max-width:720px}
}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="gtb-topbar">
  <a href="<?= $_base ?>index.php" class="gtb-topbar-logo">
    <div class="gtb-topbar-logo-mark">GTB</div>
    <div class="gtb-topbar-logo-text">
      <strong>Global Trust Bank</strong>
      <span>Espace client</span>
    </div>
  </a>
  <div class="gtb-topbar-right">
    <a href="<?= $_base ?>notifications.php" class="gtb-topbar-btn" title="Notifications">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--sub)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
      <?php if($_notifs > 0): ?><span class="gtb-notif-badge"><?= $_notifs > 9 ? '9+' : $_notifs ?></span><?php endif; ?>
    </a>
    <a href="<?= $_base ?>profil/index.php" class="gtb-avatar" title="Mon profil">
      <?php if($_avatar): ?><img src="<?= $_avatar ?>" alt="avatar"/><?php else: ?><?= $_initials ?><?php endif; ?>
    </a>
  </div>
</header>

<!-- PAGE BODY -->
<div class="gtb-body">
