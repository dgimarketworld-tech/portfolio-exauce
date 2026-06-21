<?php
require_once __DIR__ . '/../../backend/auth_required.php';
require_once __DIR__ . '/../../backend/helpers.php';

$u           = $currentUser;
$userId      = Session::userId();
$initials    = strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1));
$notif_count = (int)DB::scalar("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0",['id'=>$userId]);
$csrf        = Security::csrfToken();

// ── Création table si absente ────────────────────────────────────
DB::run("CREATE TABLE IF NOT EXISTS sms_banking (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  telephone   VARCHAR(20) NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 0,
  pin         VARCHAR(10) NOT NULL DEFAULT '0000',
  alert_debit TINYINT(1) DEFAULT 1,
  alert_credit TINYINT(1) DEFAULT 1,
  alert_min   DECIMAL(12,2) DEFAULT 0.00,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user (user_id)
)");

DB::run("CREATE TABLE IF NOT EXISTS sms_banking_logs (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  direction   ENUM('IN','OUT') NOT NULL,
  telephone   VARCHAR(20),
  contenu     TEXT,
  commande    VARCHAR(50),
  statut      ENUM('success','error','pending') DEFAULT 'pending',
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Config SMS de l'utilisateur ──────────────────────────────────
$sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);

// Si pas de config, on récupère le tél du profil
if (!$sms) {
    $tel = preg_replace('/\D/','', $u['phone']??'');
    if ($tel) {
        DB::insertInto('sms_banking',['user_id'=>$userId,'telephone'=>$tel,'is_active'=>0,'pin'=>'0000']);
        $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);
    }
}

// ── Historique SMS ───────────────────────────────────────────────
$logs = DB::all("SELECT * FROM sms_banking_logs WHERE user_id=:id ORDER BY created_at DESC LIMIT 30",['id'=>$userId]);

$success = $error = '';

// ── POST actions ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && Security::csrfCheck($_POST['_csrf']??'')) {
    $action = $_POST['_action']??'';

    if ($action==='activate') {
        $tel = preg_replace('/\D/','', $_POST['telephone']??'');
        $pin = preg_replace('/\D/','', $_POST['pin']??'');
        if (strlen($tel)<8)        { $error='Numéro de téléphone invalide.'; }
        elseif (strlen($pin)!==4)  { $error='Le code PIN doit avoir exactement 4 chiffres.'; }
        else {
            if ($sms) {
                DB::update("UPDATE sms_banking SET telephone=:t,pin=:p,is_active=1,updated_at=NOW() WHERE user_id=:id",['t'=>$tel,'p'=>$pin,'id'=>$userId]);
            } else {
                DB::insertInto('sms_banking',['user_id'=>$userId,'telephone'=>$tel,'pin'=>$pin,'is_active'=>1]);
            }
            $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);
            // Notification
            DB::insertInto('notifications',['user_id'=>$userId,'type'=>'info','titre'=>'SMS Banking activé','message'=>'Votre service SMS Banking est maintenant actif. Envoyez AIDE au '.SMS_BANKING_NUMBER.' pour la liste des commandes.']);
            send_email($u['email'],$u['first_name'].' '.$u['last_name'],'SMS Banking activé — Global Trust Bank',
                '<p>Bonjour '.$u['first_name'].',</p><p>Votre service <strong>SMS Banking</strong> a été activé avec succès.</p><p>Numéro enregistré : <strong>+'.$tel.'</strong></p><p>Pour utiliser le service, envoyez vos commandes au numéro <strong>'.SMS_BANKING_NUMBER.'</strong></p><p>Commandes disponibles :<br>• <b>SOLDE</b> — Consulter votre solde<br>• <b>HIST</b> — 5 dernières transactions<br>• <b>AIDE</b> — Liste des commandes</p><p style="margin-top:16px;color:#6c757d;font-size:12px">Global Trust Bank — Ne communiquez jamais votre PIN SMS à personne.</p>');
            $success='SMS Banking activé sur le +'.$tel;
        }
    }

    elseif ($action==='deactivate') {
        DB::update("UPDATE sms_banking SET is_active=0,updated_at=NOW() WHERE user_id=:id",['id'=>$userId]);
        $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);
        $success='Service SMS Banking désactivé.';
    }

    elseif ($action==='update_alerts') {
        $ad = isset($_POST['alert_debit'])  ? 1 : 0;
        $ac = isset($_POST['alert_credit']) ? 1 : 0;
        $am = max(0, (float)($_POST['alert_min']??0));
        DB::update("UPDATE sms_banking SET alert_debit=:d,alert_credit=:c,alert_min=:m,updated_at=NOW() WHERE user_id=:id",['d'=>$ad,'c'=>$ac,'m'=>$am,'id'=>$userId]);
        $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);
        $success='Alertes SMS mises à jour.';
    }

    elseif ($action==='change_pin') {
        $p1 = preg_replace('/\D/','', $_POST['new_pin']??'');
        $p2 = preg_replace('/\D/','', $_POST['confirm_pin']??'');
        if (strlen($p1)!==4)  { $error='Le PIN doit contenir 4 chiffres.'; }
        elseif ($p1!==$p2)    { $error='Les deux PIN ne correspondent pas.'; }
        else {
            DB::update("UPDATE sms_banking SET pin=:p,updated_at=NOW() WHERE user_id=:id",['p'=>$p1,'id'=>$userId]);
            $sms = DB::one("SELECT * FROM sms_banking WHERE user_id=:id",['id'=>$userId]);
            $success='Code PIN SMS mis à jour.';
        }
    }

    // Recharger logs
    $logs = DB::all("SELECT * FROM sms_banking_logs WHERE user_id=:id ORDER BY created_at DESC LIMIT 30",['id'=>$userId]);
}

if (!defined('SMS_BANKING_NUMBER')) define('SMS_BANKING_NUMBER', '+33 7 57 00 0001');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB — SMS Banking</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;600&display=swap');
:root {
  --bnp-green:#0D1B2A;--bnp-dark:#091520;--bnp-deeper:#050B14;--bnp-light:#F2F4F7;--bnp-emerald:#D4AF37;--bnp-mint:#EAD9B5;
  --white:#FFFFFF;--off:#F2F4F7;--gray50:#F8F9FA;--gray100:#E9ECEF;--gray200:#DEE2E6;--gray300:#CED4DA;--gray400:#ADB5BD;--gray500:#8F96A3;--gray600:#6C757D;--gray800:#343A40;--black:#0D1B2A;
  --gold:#D4AF37;--red:#E5373A;--green:#00C67A;--blue:#1A73E8;--purple:#7C3AED;
  --success:#10B981;--danger:#EF4444;--warning:#F59E0B;--accent:#3B82F6;
  --glass:rgba(255,255,255,.07);--glass2:rgba(255,255,255,.13);
  --sh-sm:0 2px 8px rgba(13,27,42,.06);--sh-md:0 8px 32px rgba(13,27,42,.10);--sh-lg:0 24px 64px rgba(13,27,42,.14);
  --r-sm:6px;--r-md:12px;--r-lg:20px;--r-xl:32px;--r-full:9999px;
  --ease:cubic-bezier(.25,.46,.45,.94);--bounce:cubic-bezier(.34,1.56,.64,1);
  --sidebar-w:260px;--topbar-h:64px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;font-size:16px}
body{font-family:'DM Sans',sans-serif;background:#F0F2F5;color:var(--gray800);overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--gray200);border-radius:99px}

/* ═══ LAYOUT ═══ */
.sidebar-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:998;opacity:0;visibility:hidden;transition:all .3s var(--ease)}
.sidebar-overlay.show{opacity:1;visibility:visible}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:linear-gradient(180deg,var(--bnp-green) 0%,var(--bnp-deeper) 100%);z-index:999;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;transition:transform .3s var(--ease)}
.sidebar::before{content:'';position:absolute;top:0;right:0;width:1px;height:100%;background:linear-gradient(180deg,transparent,rgba(212,175,55,.3),transparent)}
.sidebar-logo{display:flex;align-items:center;gap:12px;padding:24px 20px 20px;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.07)}
.sidebar-logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--gold),#B8860B);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.75rem;color:var(--bnp-dark);letter-spacing:-.5px;flex-shrink:0}
.sidebar-logo-text span:first-child{display:block;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;color:#fff;line-height:1.2}
.sidebar-logo-text span:last-child{font-size:.62rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px}
.sidebar-user{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07)}
.sidebar-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--gold),#B8860B);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:var(--bnp-dark);flex-shrink:0}
.sidebar-user-name{font-size:.83rem;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar-user-plan{font-size:.68rem;color:var(--gold);margin-top:2px}
.sidebar-nav{flex:1;padding:16px 12px;overflow-y:auto}
.sidebar-section-label{font-size:.62rem;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:12px 8px 6px}
.sidebar-link{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--r-sm);color:rgba(255,255,255,.55);font-size:.82rem;font-weight:500;text-decoration:none;transition:all .2s var(--ease);position:relative;margin-bottom:2px}
.sidebar-link:hover{color:rgba(255,255,255,.9);background:var(--glass2)}
.sidebar-link.active{color:#fff;background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(212,175,55,.08));border:1px solid rgba(212,175,55,.2)}
.sidebar-link.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--gold);border-radius:0 2px 2px 0}
.sidebar-icon{width:18px;text-align:center;opacity:.6}
.sidebar-link:hover .sidebar-icon,.sidebar-link.active .sidebar-icon{opacity:1}
.sidebar-badge{background:var(--red);color:#fff;font-size:.6rem;font-weight:700;padding:1px 5px;border-radius:9px;margin-left:auto}
.sidebar-footer{padding:12px;border-top:1px solid rgba(255,255,255,.07);margin-top:auto}
.sidebar-footer-link{display:flex;align-items:center;gap:8px;padding:8px 12px;color:rgba(255,255,255,.4);font-size:.75rem;text-decoration:none;border-radius:var(--r-sm);transition:all .2s}
.sidebar-footer-link:hover{color:rgba(255,255,255,.7)}
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(255,255,255,.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--gray100);z-index:100;display:flex;align-items:center;padding:0 28px;gap:16px;box-shadow:var(--sh-sm)}
.topbar-title{font-family:'Sora',sans-serif;font-weight:700;font-size:1rem;color:var(--black);flex:1}
.topbar-title small{font-size:.7rem;font-weight:400;color:var(--gray400);display:block;margin-top:1px}
.topbar-btn{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--gray200);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1rem;color:var(--gray600);transition:all .2s;text-decoration:none;position:relative}
.topbar-btn:hover{border-color:var(--gold);color:var(--gold)}
.notif-dot{position:absolute;top:4px;right:4px;width:8px;height:8px;background:var(--red);border-radius:50%;border:2px solid #fff}
.main{margin-left:var(--sidebar-w);padding-top:var(--topbar-h);min-height:100vh}
.main-inner{padding:28px}
.sidebar-toggle{display:none;width:38px;height:38px;border-radius:10px;border:1.5px solid var(--gray200);background:#fff;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--gray600)}
@media(max-width:900px){:root{--sidebar-w:220px}.main-inner{padding:20px}}
@media(max-width:768px){.sidebar{transform:translateX(-100%);width:270px}.sidebar.open{transform:translateX(0)}.sidebar-toggle{display:flex}.main{margin-left:0}}

/* ═══ COMPONENTS ═══ */
.page-header{margin-bottom:1.5rem}
.page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:1.35rem;color:var(--black)}
.page-sub{color:var(--gray500);font-size:.82rem;margin-top:.25rem}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
@media(max-width:900px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
.kpi-card{background:#fff;border-radius:var(--r-lg);padding:1.25rem;box-shadow:var(--sh-sm);display:flex;align-items:center;gap:1rem;border:1px solid var(--gray100)}
.kpi-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.kpi-label{font-size:.72rem;color:var(--gray500);font-weight:500;text-transform:uppercase;letter-spacing:.5px}
.kpi-value{font-family:'Sora',sans-serif;font-weight:700;font-size:1.3rem;color:var(--black);line-height:1.2;margin-top:.1rem}
.card{background:#fff;border-radius:var(--r-lg);border:1px solid var(--gray100);box-shadow:var(--sh-sm);overflow:hidden}
.card-head{padding:1.1rem 1.25rem;border-bottom:1px solid var(--gray100);display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.card-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.88rem;color:var(--black)}
.card-body{padding:1.25rem}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
@media(max-width:900px){.grid-2{grid-template-columns:1fr}}

/* Forms */
.form-group{margin-bottom:1rem}
.form-label{display:block;font-size:.78rem;font-weight:600;color:var(--gray800);margin-bottom:.4rem}
.form-input{width:100%;padding:.65rem .9rem;border:1.5px solid var(--gray200);border-radius:var(--r-md);font-size:.83rem;font-family:inherit;color:var(--gray800);background:#fff;transition:border-color .2s,box-shadow .2s;outline:none}
.form-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(212,175,55,.1)}
.form-hint{font-size:.72rem;color:var(--gray400);margin-top:.3rem}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.2rem;border-radius:var(--r-md);font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,var(--gold),#B8860B);color:var(--bnp-dark)}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-danger{background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:var(--danger);color:#fff}
.btn-outline{background:#fff;color:var(--gray800);border:1.5px solid var(--gray200)}
.btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.toggle-wrap{display:flex;align-items:center;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--gray100)}
.toggle-wrap:last-child{border-bottom:none}
.toggle-info{font-size:.82rem;font-weight:500;color:var(--gray800)}
.toggle-info small{display:block;font-size:.72rem;color:var(--gray400);font-weight:400;margin-top:1px}
.toggle{position:relative;width:40px;height:22px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:var(--gray200);border-radius:99px;cursor:pointer;transition:.2s}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;left:2px;top:2px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle input:checked+.toggle-slider{background:var(--success)}
.toggle input:checked+.toggle-slider::before{transform:translateX(18px)}

/* Commands table */
.cmd-table{width:100%;border-collapse:collapse;font-size:.82rem}
.cmd-table th{text-align:left;padding:.5rem .75rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--gray400);font-weight:600;border-bottom:2px solid var(--gray100)}
.cmd-table td{padding:.65rem .75rem;border-bottom:1px solid var(--gray100);color:var(--gray800)}
.cmd-table tr:last-child td{border-bottom:none}
.cmd-table tr:hover td{background:var(--gray50)}
.cmd-code{font-family:'JetBrains Mono',monospace;font-size:.78rem;font-weight:600;color:var(--bnp-dark);background:var(--gray100);padding:2px 7px;border-radius:4px}
.cmd-example{font-family:'JetBrains Mono',monospace;font-size:.72rem;color:var(--gray400);margin-top:2px}

/* Status badge */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:600}
.badge-green{background:rgba(16,185,129,.1);color:var(--success)}
.badge-red{background:rgba(239,68,68,.1);color:var(--danger)}
.badge-gray{background:var(--gray100);color:var(--gray500)}

/* SMS log */
.sms-log-item{display:flex;gap:.75rem;padding:.8rem 0;border-bottom:1px solid var(--gray100)}
.sms-log-item:last-child{border-bottom:none}
.sms-bubble{max-width:75%;padding:.6rem .9rem;border-radius:12px;font-size:.8rem;line-height:1.4}
.sms-in{margin-right:auto}
.sms-in .sms-bubble{background:var(--gray100);color:var(--gray800);border-bottom-left-radius:4px}
.sms-out{margin-left:auto;flex-direction:row-reverse}
.sms-out .sms-bubble{background:linear-gradient(135deg,var(--bnp-green),var(--bnp-dark));color:#fff;border-bottom-right-radius:4px}
.sms-meta{font-size:.65rem;color:var(--gray400);margin-top:3px}

/* Alert */
.alert{border-radius:var(--r-md);padding:.85rem 1rem;margin-bottom:1.25rem;font-size:.82rem}
.alert-success{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:var(--success)}
.alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--danger)}

/* Status hero */
.status-hero{background:linear-gradient(135deg,var(--bnp-green),var(--bnp-deeper));border-radius:var(--r-xl);padding:2rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
.status-hero::before{content:'';position:absolute;top:-30px;right:-30px;width:180px;height:180px;background:radial-gradient(circle,rgba(212,175,55,.15),transparent 70%);pointer-events:none}
.status-hero-title{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;color:#fff;margin-bottom:.4rem}
.status-hero-sub{font-size:.82rem;color:rgba(255,255,255,.5);margin-bottom:1.25rem}
.status-num{font-family:'JetBrains Mono',monospace;font-size:1.5rem;font-weight:600;color:var(--gold);letter-spacing:2px}
.status-num-label{font-size:.68rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin-top:2px}

/* Modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:2000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-bg.open{display:flex}
.modal{background:#fff;border-radius:var(--r-xl);width:100%;max-width:440px;overflow:hidden;animation:popIn .25s var(--bounce)}
@keyframes popIn{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}
.modal-head{padding:1.25rem 1.5rem;border-bottom:1px solid var(--gray100);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--black)}
.modal-close{width:30px;height:30px;border-radius:50%;border:none;background:var(--gray100);cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;color:var(--gray600);transition:all .2s}
.modal-close:hover{background:var(--gray200)}
.modal-body{padding:1.5rem}
.modal-foot{padding:1rem 1.5rem;border-top:1px solid var(--gray100);display:flex;gap:.75rem;justify-content:flex-end}
</style>
<link rel="stylesheet" href="../mobile.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <a href="../../dashboard/index.php" class="sidebar-logo">
    <div class="sidebar-logo-mark">GTB</div>
    <div class="sidebar-logo-text"><span>Global Trust Bank</span><span>Espace client</span></div>
  </a>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?=$initials?></div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?=e(($u['first_name']??'').' '.($u['last_name']??''))?></div>
      <div class="sidebar-user-plan"><?=($u['plan']??'')==='premium'?'✦ Premium':'Standard'?></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">⌂</span> Tableau de bord</a>
    <a href="../comptes/index.php" class="sidebar-link"><span class="sidebar-icon">⊞</span> Mes Comptes</a>
    <a href="../cartes/index.php" class="sidebar-link"><span class="sidebar-icon">▣</span> Cartes</a>
    <a href="../virement/index.php" class="sidebar-link"><span class="sidebar-icon">⇄</span> Virements</a>
    <div class="sidebar-section-label">Épargne &amp; Invest.</div>
    <a href="../investissement/index.php" class="sidebar-link"><span class="sidebar-icon">◈</span> Investissements</a>
    <a href="../credits/index.php" class="sidebar-link"><span class="sidebar-icon">◎</span> Crédits</a>
    <a href="../assurance/index.php" class="sidebar-link"><span class="sidebar-icon">◉</span> Assurances</a>
    <div class="sidebar-section-label">Autres</div>
    <a href="../avantage/index.php" class="sidebar-link"><span class="sidebar-icon">✦</span> Avantages</a>
    <a href="../sms-banking/index.php" class="sidebar-link active"><span class="sidebar-icon">✉</span> SMS Banking</a>
    <a href="../profil/index.php" class="sidebar-link"><span class="sidebar-icon">👤</span> Mon Profil</a>
    <a href="../parametres/index.php" class="sidebar-link"><span class="sidebar-icon">⚙</span> Paramètres</a>
    <a href="../support/index.php" class="sidebar-link"><span class="sidebar-icon">❓</span> Support</a>
  </nav>
  <div class="sidebar-footer">
    <a href="../../authentification/api/logout.php" class="sidebar-footer-link">⏻ Déconnexion</a>
  </div>
</aside>

<!-- Topbar -->
<div class="topbar">
  <button class="sidebar-toggle" id="sidebarToggle">☰</button>
  <div class="topbar-title">SMS Banking <small>Gérez vos opérations par SMS</small></div>
  <a href="../notifications.php" class="topbar-btn" title="Notifications">
    🔔<?php if($notif_count>0):?><span class="notif-dot"></span><?php endif;?>
  </a>
  <a href="../profil/index.php" class="topbar-btn" title="Profil"><?=$initials?></a>
</div>

<div class="main">
<div class="main-inner">

<?php if($success):?><div class="alert alert-success">✓ <?=htmlspecialchars($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error">✕ <?=htmlspecialchars($error)?></div><?php endif;?>

<!-- Hero statut -->
<div class="status-hero">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
    <div>
      <div class="status-hero-title">✉ SMS Banking</div>
      <div class="status-hero-sub">Consultez votre solde, faites des virements et recevez des alertes par SMS — sans connexion internet.</div>
      <?php if($sms && $sms['is_active']):?>
        <div class="status-num">+<?=htmlspecialchars($sms['telephone']??'')?></div>
        <div class="status-num-label">Numéro enregistré · Statut actif</div>
      <?php else:?>
        <div style="margin-top:.75rem"><span class="badge badge-red">● Service inactif</span></div>
      <?php endif;?>
    </div>
    <div style="text-align:right">
      <div style="color:rgba(255,255,255,.4);font-size:.72rem;margin-bottom:.4rem">Envoyez vos commandes au</div>
      <div style="font-family:'JetBrains Mono',monospace;font-size:1.2rem;font-weight:700;color:var(--gold)"><?=SMS_BANKING_NUMBER?></div>
      <?php if($sms && $sms['is_active']):?>
        <form method="POST" style="margin-top:.75rem">
          <input type="hidden" name="_csrf" value="<?=$csrf?>">
          <input type="hidden" name="_action" value="deactivate">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Désactiver le SMS Banking ?')">Désactiver</button>
        </form>
      <?php else:?>
        <button class="btn btn-primary" style="margin-top:.75rem" onclick="document.getElementById('modalActivate').classList.add('open')">Activer le service</button>
      <?php endif;?>
    </div>
  </div>
</div>

<!-- KPIs -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-icon" style="background:rgba(212,175,55,.1);color:var(--gold)">✉</div>
    <div><div class="kpi-label">SMS reçus</div><div class="kpi-value"><?=count(array_filter($logs,fn($l)=>$l['direction']==='IN'))?></div></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon" style="background:rgba(59,130,246,.1);color:var(--blue)">📤</div>
    <div><div class="kpi-label">Réponses envoyées</div><div class="kpi-value"><?=count(array_filter($logs,fn($l)=>$l['direction']==='OUT'))?></div></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon" style="background:rgba(16,185,129,.1);color:var(--success)">✓</div>
    <div><div class="kpi-label">Commandes réussies</div><div class="kpi-value"><?=count(array_filter($logs,fn($l)=>$l['statut']==='success'))?></div></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-icon" style="background:<?=$sms&&$sms['is_active']?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'?>;color:<?=$sms&&$sms['is_active']?'var(--success)':'var(--danger)'?>">●</div>
    <div><div class="kpi-label">Statut service</div><div class="kpi-value" style="font-size:.9rem"><?=$sms&&$sms['is_active']?'Actif':'Inactif'?></div></div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:1.25rem">

  <!-- Commandes disponibles -->
  <div class="card">
    <div class="card-head">
      <div class="card-title">📋 Commandes SMS</div>
      <span class="badge badge-gray">Envoyez au <?=SMS_BANKING_NUMBER?></span>
    </div>
    <div style="padding:0 .25rem">
      <table class="cmd-table">
        <thead><tr><th>Commande</th><th>Description</th><th>Exemple</th></tr></thead>
        <tbody>
          <?php $cmds=[
            ['SOLDE','Consulter votre solde','SOLDE 1234'],
            ['HIST','5 dernières transactions','HIST 1234'],
            ['VIRER','Virement vers un bénéficiaire','VIRER 1234 FR76... 150.00'],
            ['COMPTE','Numéros de vos comptes','COMPTE 1234'],
            ['BLOQUER','Bloquer une carte','BLOQUER 1234 4512'],
            ['AIDE','Liste de toutes les commandes','AIDE'],
            ['STOP','Désactiver les alertes SMS','STOP'],
            ['START','Réactiver les alertes SMS','START'],
          ]; foreach($cmds as $c):?>
          <tr>
            <td><span class="cmd-code"><?=$c[0]?></span></td>
            <td><?=$c[1]?></td>
            <td><span class="cmd-example"><?=$c[2]?></span></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <div style="padding:.75rem 1rem;background:rgba(239,68,68,.04);border-top:1px solid var(--gray100);font-size:.72rem;color:var(--gray500)">
      ⚠️ Remplacez <code>1234</code> par votre PIN SMS à 4 chiffres. Ne communiquez jamais votre PIN.
    </div>
  </div>

  <!-- Paramètres alertes + PIN -->
  <div style="display:flex;flex-direction:column;gap:1.25rem">

    <?php if($sms):?>
    <!-- Alertes -->
    <div class="card">
      <div class="card-head"><div class="card-title">🔔 Alertes SMS</div></div>
      <form method="POST">
        <input type="hidden" name="_csrf" value="<?=$csrf?>">
        <input type="hidden" name="_action" value="update_alerts">
        <div style="padding:0 1.25rem">
          <div class="toggle-wrap">
            <div class="toggle-info">Alerte débit <small>SMS à chaque débit sur vos comptes</small></div>
            <label class="toggle"><input type="checkbox" name="alert_debit" <?=($sms['alert_debit']??1)?'checked':''?> onchange="this.form.submit()"><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-wrap">
            <div class="toggle-info">Alerte crédit <small>SMS à chaque crédit reçu</small></div>
            <label class="toggle"><input type="checkbox" name="alert_credit" <?=($sms['alert_credit']??1)?'checked':''?> onchange="this.form.submit()"><span class="toggle-slider"></span></label>
          </div>
        </div>
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--gray100);display:flex;gap:.75rem;align-items:flex-end">
          <div style="flex:1">
            <label class="form-label">Montant minimum d'alerte (€)</label>
            <input type="number" name="alert_min" class="form-input" value="<?=number_format($sms['alert_min']??0,2,'.','')?>" min="0" step="1" placeholder="0 = toutes les opérations">
          </div>
          <button type="submit" class="btn btn-outline">Enregistrer</button>
        </div>
      </form>
    </div>

    <!-- Changer PIN -->
    <div class="card">
      <div class="card-head"><div class="card-title">🔑 Modifier le PIN SMS</div></div>
      <form method="POST">
        <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem">
          <input type="hidden" name="_csrf" value="<?=$csrf?>">
          <input type="hidden" name="_action" value="change_pin">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Nouveau PIN (4 chiffres)</label>
            <input type="password" name="new_pin" class="form-input" maxlength="4" pattern="\d{4}" required placeholder="••••">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Confirmer le PIN</label>
            <input type="password" name="confirm_pin" class="form-input" maxlength="4" pattern="\d{4}" required placeholder="••••">
          </div>
          <button type="submit" class="btn btn-primary" style="align-self:flex-start">Mettre à jour</button>
        </div>
      </form>
    </div>
    <?php else:?>
    <div class="card">
      <div class="card-body" style="text-align:center;padding:2.5rem 1.25rem;color:var(--gray400)">
        <div style="font-size:2.5rem;margin-bottom:.75rem">✉</div>
        <div style="font-weight:600;color:var(--gray600);margin-bottom:.5rem">Service non activé</div>
        <p style="font-size:.8rem">Activez le SMS Banking pour gérer vos alertes et paramètres.</p>
      </div>
    </div>
    <?php endif;?>
  </div>
</div>

<!-- Historique SMS -->
<div class="card">
  <div class="card-head">
    <div class="card-title">💬 Historique des échanges SMS</div>
    <span class="badge badge-gray"><?=count($logs)?> messages</span>
  </div>
  <div class="card-body">
    <?php if(empty($logs)):?>
    <div style="text-align:center;padding:2rem;color:var(--gray400)">
      <div style="font-size:2rem;margin-bottom:.75rem">💬</div>
      <div style="font-size:.82rem">Aucun échange SMS pour le moment.<br>Activez le service et envoyez votre première commande.</div>
    </div>
    <?php else:?>
    <?php foreach($logs as $log):?>
    <div class="sms-log-item <?=$log['direction']==='IN'?'sms-in':'sms-out'?>">
      <div>
        <div class="sms-bubble"><?=htmlspecialchars($log['contenu']??'')?></div>
        <div class="sms-meta">
          <?=$log['direction']==='IN'?'Reçu':'Envoyé'?> ·
          <?=date('d/m/Y H:i',strtotime($log['created_at']))?>
          <?php if($log['statut']==='success'):?> · <span style="color:var(--success)">✓ succès</span><?php elseif($log['statut']==='error'):?> · <span style="color:var(--danger)">✕ erreur</span><?php endif;?>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php endif;?>
  </div>
</div>

</div><!-- /.main-inner -->
</div><!-- /.main -->

<!-- Modal activation -->
<div class="modal-bg" id="modalActivate">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Activer le SMS Banking</div>
      <button class="modal-close" onclick="document.getElementById('modalActivate').classList.remove('open')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?=$csrf?>">
        <input type="hidden" name="_action" value="activate">
        <div class="form-group">
          <label class="form-label">Numéro de téléphone</label>
          <input type="tel" name="telephone" class="form-input" value="<?=htmlspecialchars(preg_replace('/\D/','',$u['phone']??''))?>" required placeholder="336XXXXXXXX">
          <div class="form-hint">Format international sans +, ex: 33612345678</div>
        </div>
        <div class="form-group">
          <label class="form-label">Créer un code PIN SMS (4 chiffres)</label>
          <input type="password" name="pin" class="form-input" maxlength="4" pattern="\d{4}" required placeholder="••••">
          <div class="form-hint">Ce PIN sécurise vos commandes SMS. Ne le communiquez jamais.</div>
        </div>
        <div style="background:rgba(212,175,55,.07);border:1px solid rgba(212,175,55,.2);border-radius:var(--r-md);padding:.85rem;font-size:.78rem;color:var(--gray600)">
          ℹ️ En activant ce service, vous acceptez de recevoir des SMS de la part de Global Trust Bank au numéro indiqué. Des frais opérateur peuvent s'appliquer.
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalActivate').classList.remove('open')">Annuler</button>
        <button type="submit" class="btn btn-primary">Activer</button>
      </div>
    </form>
  </div>
</div>

<script>
const sidebar=document.getElementById('sidebar');
const overlay=document.getElementById('sidebarOverlay');
document.getElementById('sidebarToggle')?.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('show')});
overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show')});
</script>
</body>
</html>
