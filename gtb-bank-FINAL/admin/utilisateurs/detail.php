<?php
require_once __DIR__.'/../../backend/admin_required.php';
$adm=$currentAdmin;
$initials=strtoupper(substr($adm['first_name']??'A',0,1).substr($adm['last_name']??'M',0,1));
$uid=(int)($_GET['id']??0);
if(!$uid){header('Location: index.php');exit;}
$user=DB::one("SELECT * FROM users WHERE id=:id AND role='user'",['id'=>$uid]);
if(!$user){header('Location: index.php?error=not_found');exit;}
$accounts=DB::all("SELECT * FROM accounts WHERE user_id=:id ORDER BY created_at DESC",['id'=>$uid]);
$cards=DB::all("SELECT * FROM cards WHERE user_id=:id ORDER BY created_at DESC",['id'=>$uid]);
$txlast=DB::all("SELECT * FROM transactions WHERE account_id IN (SELECT id FROM accounts WHERE user_id=:id) ORDER BY created_at DESC LIMIT 10",['id'=>$uid]);
$title='Détail utilisateur — '.e(($user['first_name']??'').' '.($user['last_name']??''));
$csrf=Security::csrfTokenAdmin();
$fullname=e(($user['first_name']??'').' '.($user['last_name']??''));
$av=strtoupper(substr($user['first_name']??'U',0,1).substr($user['last_name']??'',0,1));
// Actions POST
if($_SERVER['REQUEST_METHOD']==='POST'&&Security::verifyCsrfAdmin($_POST['_csrf']??'')){
    $action=$_POST['action']??'';
    if($action==='suspend'){DB::update("UPDATE users SET status='suspended' WHERE id=:id",['id'=>$uid]);header("Location: detail.php?id=$uid&msg=suspended");exit;}
    if($action==='activate'){DB::update("UPDATE users SET status='active' WHERE id=:id",['id'=>$uid]);header("Location: detail.php?id=$uid&msg=activated");exit;}
    if($action==='reset_password'){
        $tmp=bin2hex(random_bytes(8));
        DB::update("UPDATE users SET password=:p WHERE id=:id",['p'=>password_hash($tmp,PASSWORD_DEFAULT),'id'=>$uid]);
        header("Location: detail.php?id=$uid&msg=pwd_reset&tmp=".urlencode($tmp));exit;
    }
}
?><!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB Admin — <?php echo $title;?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap');
:root{--admin-bg:#0F172A;--admin-bg-deeper:#020617;--admin-bg-mid:#1E293B;--accent:#3B82F6;--accent-light:#60A5FA;--accent-deep:#1D4ED8;--white:#FFFFFF;--off:#F1F5F9;--gray50:#F8FAFC;--gray100:#F1F5F9;--gray200:#E2E8F0;--gray300:#CBD5E1;--gray400:#94A3B8;--gray500:#64748B;--gray600:#475569;--gray700:#334155;--gray800:#1E293B;--gray900:#0F172A;--success:#10B981;--warning:#F59E0B;--danger:#EF4444;--info:#0EA5E9;--gold:#D4AF37;--sh-sm:0 1px 3px rgba(15,23,42,.06);--sh-md:0 6px 24px rgba(15,23,42,.08);--sh-lg:0 20px 50px rgba(15,23,42,.12);--r-sm:6px;--r-md:10px;--r-lg:16px;--r-full:9999px;--ease:cubic-bezier(.25,.46,.45,.94);--sidebar-w:248px;--topbar-h:60px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{font-size:16px;}body{font-family:'DM Sans',sans-serif;background:var(--off);color:var(--gray800);-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}
.admin-layout{display:flex;min-height:100vh;}
.sidebar-overlay{position:fixed;inset:0;background:rgba(2,6,23,.5);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:.3s;z-index:150;}
.sidebar-overlay.show{opacity:1;visibility:visible;}
.sidebar{background:var(--admin-bg);position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);display:flex;flex-direction:column;z-index:200;overflow:hidden;transition:transform .35s var(--ease);border-right:1px solid rgba(255,255,255,.06);}
.sidebar-logo{display:flex;align-items:center;gap:.65rem;padding:1.25rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);}
.logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.74rem;color:white;}
.logo-text{display:flex;flex-direction:column;line-height:1;}
.logo-text span:first-child{font-family:'Sora',sans-serif;font-weight:700;font-size:.84rem;color:white;}
.logo-text span:last-child{font-size:.58rem;color:var(--accent-light);letter-spacing:.1em;text-transform:uppercase;margin-top:3px;font-weight:600;}
.admin-user{padding:.85rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:.7rem;}
.admin-av{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0;}
.admin-name{font-size:.8rem;font-weight:600;color:white;}
.admin-role{font-size:.6rem;color:#EF4444;letter-spacing:.08em;text-transform:uppercase;font-weight:700;}
.sidebar-nav{flex:1;padding:.75rem 0;overflow-y:auto;}
.nav-section-label{font-size:.55rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.65rem 1.4rem .25rem;}
.nav-link{display:flex;align-items:center;gap:.7rem;padding:.55rem 1.4rem;margin:.05rem .6rem;border-radius:8px;text-decoration:none;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;transition:.2s;}
.nav-link:hover{color:white;background:rgba(255,255,255,.06);}
.nav-link.active{color:white;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25);}
.nav-icon{width:16px;text-align:center;font-style:normal;}
.sidebar-footer{padding:.75rem .6rem;border-top:1px solid rgba(255,255,255,.05);}
.admin-topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:white;border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;z-index:100;box-shadow:var(--sh-sm);}
.admin-main{margin-left:var(--sidebar-w);padding-top:var(--topbar-h);min-height:100vh;flex:1;}
.admin-content{padding:1.75rem;}
.sidebar-toggle{display:none;background:none;border:none;font-size:1.2rem;cursor:pointer;padding:.25rem;}
.badge{display:inline-flex;align-items:center;padding:.2rem .55rem;border-radius:var(--r-full);font-size:.68rem;font-weight:700;font-family:'Sora',sans-serif;}
.badge-green{background:rgba(16,185,129,.1);color:#059669;}
.badge-red{background:rgba(239,68,68,.1);color:#DC2626;}
.badge-blue{background:rgba(59,130,246,.1);color:#2563EB;}
.badge-gold{background:rgba(212,175,55,.1);color:#B45309;}
.badge-gray{background:var(--gray100);color:var(--gray600);}
.badge-orange{background:rgba(245,158,11,.1);color:#D97706;}
/* Page styles */
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.75rem;gap:1rem;flex-wrap:wrap;}
.page-title{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:var(--gray800);}
.breadcrumb{font-size:.78rem;color:var(--gray400);margin-top:.25rem;}
.breadcrumb a{color:var(--accent);text-decoration:none;}
.breadcrumb a:hover{text-decoration:underline;}
.action-bar{display:flex;gap:.6rem;flex-wrap:wrap;}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:var(--r-md);font-size:.8rem;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;border:none;transition:.2s;text-decoration:none;}
.btn-primary{background:var(--accent);color:white;}
.btn-primary:hover{background:var(--accent-deep);}
.btn-danger{background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.2);}
.btn-danger:hover{background:rgba(239,68,68,.15);}
.btn-success{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2);}
.btn-success:hover{background:rgba(16,185,129,.15);}
.btn-warning{background:rgba(245,158,11,.1);color:#D97706;border:1px solid rgba(245,158,11,.2);}
.btn-warning:hover{background:rgba(245,158,11,.15);}
.btn-ghost{background:white;color:var(--gray600);border:1px solid var(--gray200);}
.btn-ghost:hover{background:var(--gray50);}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;}
.card{background:white;border-radius:var(--r-lg);border:1px solid var(--gray200);box-shadow:var(--sh-sm);overflow:hidden;}
.card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--gray100);display:flex;align-items:center;justify-content:space-between;}
.card-title{font-family:'Sora',sans-serif;font-size:.88rem;font-weight:700;color:var(--gray800);}
.card-body{padding:1.25rem;}
.user-hero{display:flex;align-items:center;gap:1.5rem;padding:1.5rem;}
.user-avatar{width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:1.4rem;color:white;flex-shrink:0;}
.user-hero-info h2{font-family:'Sora',sans-serif;font-size:1.2rem;font-weight:700;color:var(--gray800);margin-bottom:.35rem;}
.user-hero-info .meta{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;}
.dl{display:grid;grid-template-columns:140px 1fr;gap:.55rem 1rem;align-items:start;}
.dl dt{font-size:.78rem;font-weight:600;color:var(--gray500);}
.dl dd{font-size:.82rem;color:var(--gray800);}
.dl dd code{font-family:'JetBrains Mono',monospace;font-size:.75rem;background:var(--gray100);padding:.1rem .35rem;border-radius:4px;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:1rem;}
.stat-item{text-align:center;padding:.75rem;}
.stat-val{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;color:var(--gray800);}
.stat-lbl{font-size:.68rem;color:var(--gray400);text-transform:uppercase;letter-spacing:.06em;margin-top:.2rem;}
.table{width:100%;border-collapse:collapse;font-size:.8rem;}
.table th{padding:.65rem .8rem;text-align:left;font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--gray400);border-bottom:2px solid var(--gray100);}
.table td{padding:.65rem .8rem;border-bottom:1px solid var(--gray100);color:var(--gray700);}
.table tr:last-child td{border-bottom:none;}
.table tr:hover td{background:var(--gray50);}
.alert{padding:.75rem 1rem;border-radius:var(--r-md);font-size:.82rem;margin-bottom:1rem;}
.alert-success{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#065F46;}
.alert-warning{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#92400E;}
.alert-danger{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#991B1B;}
.mono{font-family:'JetBrains Mono',monospace;font-size:.78rem;}
.text-green{color:var(--success);}
.text-red{color:var(--danger);}
.empty{text-align:center;padding:2rem;color:var(--gray400);font-size:.85rem;}
@media(max-width:1024px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.admin-topbar,.admin-main{left:0;margin-left:0}.sidebar-toggle{display:flex}.grid-2{grid-template-columns:1fr}.grid-3{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.grid-3{grid-template-columns:1fr}.user-hero{flex-direction:column;text-align:center}.action-bar{justify-content:center}}
</style>
</head>
<body><div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo"><div class="logo-mark">GTB</div><div class="logo-text"><span>Global Trust Bank</span><span>Administration</span></div></div>
  <div class="admin-user"><div class="admin-av"><?php echo $initials;?></div><div><div class="admin-name"><?php echo e(($adm['first_name']??'').' '.($adm['last_name']??''));?></div><div class="admin-role"><?php echo ucfirst($adm['role']??'admin');?></div></div></div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>
    <a href="../index.php" class="nav-link"><span class="nav-icon">⊞</span> Dashboard</a>
    <a href="index.php" class="nav-link active"><span class="nav-icon">👥</span> Utilisateurs</a>
    <a href="../kyc/index.php" class="nav-link"><span class="nav-icon">🪪</span> KYC / Vérification</a>
    <a href="../comptes/index.php" class="nav-link"><span class="nav-icon">🏦</span> Comptes</a>
    <a href="../cartes/index.php" class="nav-link"><span class="nav-icon">💳</span> Cartes</a>
    <a href="../transactions/index.php" class="nav-link"><span class="nav-icon">≡</span> Transactions</a>
    <a href="../virements/index.php" class="nav-link"><span class="nav-icon">⇄</span> Virements</a>
    <a href="../credits/index.php" class="nav-link"><span class="nav-icon">📋</span> Crédits</a>
    <a href="../assurances/index.php" class="nav-link"><span class="nav-icon">🛡️</span> Assurances</a>
    <div class="nav-section-label">Contrôle</div>
    <a href="../fraude/index.php" class="nav-link"><span class="nav-icon">🚨</span> Fraude / Alertes</a>
    <a href="../support/index.php" class="nav-link"><span class="nav-icon">❓</span> Support Client</a>
    <div class="nav-section-label">Équipe & Config</div>
    <a href="../equipe/index.php" class="nav-link"><span class="nav-icon">👤</span> Équipe</a>
    <a href="../notifications/index.php" class="nav-link"><span class="nav-icon">🔔</span> Notifications</a>
    <a href="../logs/index.php" class="nav-link"><span class="nav-icon">📜</span> Audit Logs</a>
    <a href="../parametres/index.php" class="nav-link"><span class="nav-icon">⚙</span> Paramètres</a>
  </nav>
  <div class="sidebar-footer"><a href="../../authentification/api/logout.php" class="nav-link" onclick="return confirm('Se déconnecter ?')"><span>🔒</span> Déconnexion</a></div>
</aside>
<header class="admin-topbar">
  <div style="display:flex;align-items:center;gap:1rem"><button class="sidebar-toggle" id="sidebarToggle">☰</button><h1 style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:700;color:var(--gray800)"><?php echo $title;?></h1></div>
  <div style="display:flex;align-items:center;gap:.85rem"><span style="font-size:.8rem;color:var(--gray500)"><?php echo e(($adm['first_name']??'').' '.($adm['last_name']??''));?></span><span class="badge badge-blue"><?php echo ucfirst($adm['role']??'admin');?></span></div>
</header>
<main class="admin-main"><div class="admin-content">

<?php if(isset($_GET['msg'])):?>
<?php if($_GET['msg']==='suspended'):?><div class="alert alert-warning">Compte suspendu avec succès.</div><?php endif;?>
<?php if($_GET['msg']==='activated'):?><div class="alert alert-success">Compte réactivé avec succès.</div><?php endif;?>
<?php if($_GET['msg']==='pwd_reset'):?><div class="alert alert-success">Mot de passe réinitialisé. Mot de passe temporaire : <code class="mono"><?php echo e($_GET['tmp']??'');?></code> — Transmettez-le à l'utilisateur de façon sécurisée.</div><?php endif;?>
<?php endif;?>

<!-- Header -->
<div class="page-header">
  <div>
    <div class="breadcrumb"><a href="../index.php">Dashboard</a> / <a href="index.php">Utilisateurs</a> / <?php echo $fullname;?></div>
    <div class="page-title"><?php echo $fullname;?></div>
  </div>
  <div class="action-bar">
    <a href="index.php" class="btn btn-ghost">← Retour</a>
    <?php if(($user['status']??'active')==='active'):?>
    <form method="POST" style="display:inline"><input type="hidden" name="_csrf" value="<?php echo $csrf;?>"/><input type="hidden" name="action" value="suspend"/><button type="submit" class="btn btn-danger" onclick="return confirm('Suspendre ce compte ?')">Suspendre</button></form>
    <?php else:?>
    <form method="POST" style="display:inline"><input type="hidden" name="_csrf" value="<?php echo $csrf;?>"/><input type="hidden" name="action" value="activate"/><button type="submit" class="btn btn-success">Réactiver</button></form>
    <?php endif;?>
    <form method="POST" style="display:inline"><input type="hidden" name="_csrf" value="<?php echo $csrf;?>"/><input type="hidden" name="action" value="reset_password"/><button type="submit" class="btn btn-warning" onclick="return confirm('Réinitialiser le mot de passe ?')">Reset MDP</button></form>
  </div>
</div>

<!-- User Hero Card -->
<div class="card" style="margin-bottom:1.25rem">
  <div class="user-hero">
    <div class="user-avatar"><?php echo $av;?></div>
    <div class="user-hero-info">
      <h2><?php echo $fullname;?></h2>
      <div class="meta">
        <span class="badge badge-<?php echo ($user['status']??'active')==='active'?'green':'red';?>"><?php echo ucfirst($user['status']??'active');?></span>
        <span class="badge badge-<?php echo ($user['plan']??'')==='premium'?'gold':'blue';?>"><?php echo ucfirst($user['plan']??'standard');?></span>
        <span class="badge badge-<?php echo ($user['kyc_status']??'pending')==='verified'?'green':(($user['kyc_status']??'pending')==='rejected'?'red':'orange');?>">KYC: <?php echo ucfirst($user['kyc_status']??'pending');?></span>
      </div>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="grid-3" style="margin-bottom:1.25rem">
  <div class="card"><div class="card-body"><div class="stat-grid"><div class="stat-item"><div class="stat-val"><?php echo count($accounts);?></div><div class="stat-lbl">Comptes</div></div></div></div></div>
  <div class="card"><div class="card-body"><div class="stat-grid"><div class="stat-item"><div class="stat-val"><?php echo count($cards);?></div><div class="stat-lbl">Cartes</div></div></div></div></div>
  <div class="card"><div class="card-body"><div class="stat-grid"><div class="stat-item"><div class="stat-val"><?php echo count($txlast);?>+</div><div class="stat-lbl">Transactions</div></div></div></div></div>
</div>

<!-- Infos personnelles + Comptes -->
<div class="grid-2" style="margin-bottom:1.25rem">
  <div class="card">
    <div class="card-header"><span class="card-title">Informations personnelles</span></div>
    <div class="card-body">
      <dl class="dl">
        <dt>N° client</dt><dd><code><?php echo e($user['client_number']??'—');?></code></dd>
        <dt>Prénom</dt><dd><?php echo e($user['first_name']??'—');?></dd>
        <dt>Nom</dt><dd><?php echo e($user['last_name']??'—');?></dd>
        <dt>Email</dt><dd><?php echo e($user['email']??'—');?></dd>
        <dt>Téléphone</dt><dd><?php echo e($user['phone']??'—');?></dd>
        <dt>Date de naissance</dt><dd><?php echo e($user['date_of_birth']??'—');?></dd>
        <dt>Nationalité</dt><dd><?php echo e($user['nationality']??'—');?></dd>
        <dt>Adresse</dt><dd><?php echo e($user['address']??'—');?></dd>
        <dt>Inscrit le</dt><dd><?php echo format_date($user['created_at']??'');?></dd>
        <dt>Dernière connexion</dt><dd><?php echo format_date($user['last_login']??'');?></dd>
      </dl>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Comptes bancaires</span></div>
    <div class="card-body" style="padding:0">
      <?php if($accounts):?>
      <table class="table">
        <thead><tr><th>IBAN</th><th>Type</th><th>Solde</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach($accounts as $a):?>
        <tr>
          <td class="mono"><?php echo e(substr($a['iban']??'',0,8).'…');?></td>
          <td><?php echo ucfirst($a['account_type']??'—');?></td>
          <td class="<?php echo ($a['balance']??0)>=0?'text-green':'text-red';?>"><strong><?php echo number_format(($a['balance']??0)/100,2,',',' ');?> €</strong></td>
          <td><span class="badge badge-<?php echo ($a['status']??'active')==='active'?'green':'red';?>"><?php echo ucfirst($a['status']??'active');?></span></td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
      <?php else:?><div class="empty">Aucun compte</div><?php endif;?>
    </div>
  </div>
</div>

<!-- Dernières transactions -->
<div class="card">
  <div class="card-header"><span class="card-title">10 dernières transactions</span></div>
  <div class="card-body" style="padding:0">
    <?php if($txlast):?>
    <table class="table">
      <thead><tr><th>Date</th><th>Libellé</th><th>Type</th><th>Montant</th><th>Statut</th></tr></thead>
      <tbody>
      <?php foreach($txlast as $t):?>
      <tr>
        <td style="color:var(--gray400);font-size:.75rem"><?php echo format_date($t['created_at']??'');?></td>
        <td><?php echo e($t['label']??$t['description']??'—');?></td>
        <td><span class="badge badge-gray"><?php echo ucfirst($t['type']??'—');?></span></td>
        <td class="<?php echo ($t['direction']??'debit')==='credit'?'text-green':'text-red';?>">
          <strong><?php echo ($t['direction']??'debit')==='credit'?'+':'−';?><?php echo number_format(abs(($t['amount']??0)/100),2,',',' ');?> €</strong>
        </td>
        <td><span class="badge badge-<?php echo ($t['status']??'')==='completed'?'green':(($t['status']??'')==='failed'?'red':'orange');?>"><?php echo ucfirst($t['status']??'—');?></span></td>
      </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php else:?><div class="empty">Aucune transaction</div><?php endif;?>
  </div>
</div>

</div></main></div>
<script>
var tog=document.getElementById('sidebarToggle'),sb=document.getElementById('sidebar'),ov=document.getElementById('sidebarOverlay');
if(tog){tog.addEventListener('click',function(){sb.classList.toggle('open');ov.classList.toggle('show');});}
if(ov){ov.addEventListener('click',function(){sb.classList.remove('open');ov.classList.remove('show');});}
</script>
</body></html>
