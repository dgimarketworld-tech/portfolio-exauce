<?php
/* footer.php — GTB Bottom Nav + Menu Plus
 * $navActive : 'home'|'virement'|'cartes'|'transactions'|'more'
 * $depth     : 0 (racine) ou 1 (sous-dossier)
 */
if (!isset($depth)) $depth = 0;
$_base = str_repeat('../', $depth);
$_nav  = $navActive ?? 'home';
?>
</div><!-- /.gtb-body -->

<!-- ════ BOTTOM NAV ════ -->
<nav class="gtb-bottom-nav" id="gtbNav">
<style>
.gtb-bottom-nav{
  position:fixed;bottom:0;left:0;right:0;z-index:400;
  background:var(--card);border-top:1px solid var(--border);
  padding:8px 0 calc(8px + env(safe-area-inset-bottom));
  box-shadow:0 -2px 16px rgba(13,27,42,.08);
}
.gtb-bottom-nav-inner{display:flex;justify-content:space-around;align-items:flex-end;max-width:640px;margin:0 auto}
.gtb-nav-item{
  display:flex;flex-direction:column;align-items:center;gap:3px;
  flex:1;padding:4px 2px;color:var(--sub2);transition:color .2s;
  font-size:10px;font-weight:600;cursor:pointer;text-decoration:none;
}
.gtb-nav-item svg{transition:transform .2s}
.gtb-nav-item.active{color:var(--dark)}
.gtb-nav-item.active svg{transform:scale(1.1)}
.gtb-nav-center{
  width:52px;height:52px;border-radius:50%;
  background:linear-gradient(135deg,var(--dark),#1a3352);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 16px rgba(13,27,42,.25);
  margin-bottom:8px;flex-shrink:0;transition:transform .2s;
}
.gtb-nav-center:hover{transform:scale(1.08)}
.gtb-nav-center svg{color:#fff}

/* ── PLUS PANEL ── */
.gtb-plus-overlay{
  position:fixed;inset:0;background:rgba(13,27,42,.45);
  z-index:500;opacity:0;visibility:hidden;transition:.3s;
  backdrop-filter:blur(4px);
}
.gtb-plus-overlay.open{opacity:1;visibility:visible}
.gtb-plus-panel{
  position:fixed;bottom:0;left:0;right:0;z-index:501;
  background:var(--card);border-radius:24px 24px 0 0;
  padding:0 0 calc(16px + env(safe-area-inset-bottom));
  transform:translateY(100%);transition:.35s cubic-bezier(.34,1.1,.64,1);
  max-height:80vh;overflow-y:auto;
}
.gtb-plus-overlay.open .gtb-plus-panel{transform:translateY(0)}
.gtb-plus-handle{width:36px;height:4px;border-radius:2px;background:var(--border);margin:12px auto 20px}
.gtb-plus-title{font-size:16px;font-weight:700;color:var(--dark);padding:0 20px 16px;border-bottom:1px solid var(--border2)}
.gtb-plus-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:16px}
.gtb-plus-item{
  display:flex;flex-direction:column;align-items:center;gap:8px;
  padding:14px 8px;background:var(--bg);border-radius:14px;
  border:1px solid var(--border);text-align:center;transition:all .2s;
}
.gtb-plus-item:hover,.gtb-plus-item.active{background:var(--gold-light);border-color:var(--gold)}
.gtb-plus-item-icon{
  width:40px;height:40px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  background:var(--card);
}
.gtb-plus-item-label{font-size:11px;font-weight:600;color:var(--sub)}
.gtb-plus-item.active .gtb-plus-item-label{color:var(--gold2)}
.gtb-plus-sep{margin:0 16px;border:none;border-top:1px solid var(--border2)}
.gtb-plus-logout{
  display:flex;align-items:center;justify-content:center;gap:8px;
  padding:14px 20px;margin:12px 16px 0;border-radius:12px;
  background:var(--red-light);color:var(--red);font-size:13px;font-weight:600;
}
</style>

<div class="gtb-bottom-nav-inner">
  <a href="<?= $_base ?>index.php" class="gtb-nav-item <?= $_nav==='home'?'active':'' ?>">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="<?= $_nav==='home'?'2.5':'2' ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    Accueil
  </a>
  <a href="<?= $_base ?>virement/index.php" class="gtb-nav-item <?= $_nav==='virement'?'active':'' ?>">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="<?= $_nav==='virement'?'2.5':'2' ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
    Virements
  </a>
  <a href="<?= $_base ?>virement/index.php" class="gtb-nav-center">
    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
  </a>
  <a href="<?= $_base ?>cartes/index.php" class="gtb-nav-item <?= $_nav==='cartes'?'active':'' ?>">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="<?= $_nav==='cartes'?'2.5':'2' ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
    Cartes
  </a>
  <button class="gtb-nav-item <?= in_array($_nav,['comptes','credits','assurance','investissement','avantage','sms','support','parametres','profil'])?'active':'' ?>" onclick="document.getElementById('gtbPlusOverlay').classList.toggle('open')">
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    Plus
  </button>
</div>
</nav>

<!-- ════ PLUS PANEL ════ -->
<div class="gtb-plus-overlay" id="gtbPlusOverlay" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="gtb-plus-panel">
    <div class="gtb-plus-handle"></div>
    <div class="gtb-plus-title">Navigation</div>
    <div class="gtb-plus-grid">

      <a href="<?= $_base ?>comptes/index.php" class="gtb-plus-item <?= $_nav==='comptes'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
        <span class="gtb-plus-item-label">Comptes</span>
      </a>

      <a href="<?= $_base ?>transactions.php" class="gtb-plus-item <?= $_nav==='transactions'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
        <span class="gtb-plus-item-label">Transactions</span>
      </a>

      <a href="<?= $_base ?>credits/index.php" class="gtb-plus-item <?= $_nav==='credits'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <span class="gtb-plus-item-label">Crédits</span>
      </a>

      <a href="<?= $_base ?>assurance/index.php" class="gtb-plus-item <?= $_nav==='assurance'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
        <span class="gtb-plus-item-label">Assurance</span>
      </a>

      <a href="<?= $_base ?>investissement/index.php" class="gtb-plus-item <?= $_nav==='investissement'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
        <span class="gtb-plus-item-label">Investissement</span>
      </a>

      <a href="<?= $_base ?>avantage/index.php" class="gtb-plus-item <?= $_nav==='avantage'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
        <span class="gtb-plus-item-label">Avantages</span>
      </a>

      <a href="<?= $_base ?>support/index.php" class="gtb-plus-item <?= $_nav==='support'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
        <span class="gtb-plus-item-label">Support</span>
      </a>

      <a href="<?= $_base ?>sms-banking/index.php" class="gtb-plus-item <?= $_nav==='sms'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
        <span class="gtb-plus-item-label">SMS Banking</span>
      </a>

      <a href="<?= $_base ?>releves.php" class="gtb-plus-item <?= $_nav==='releves'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <span class="gtb-plus-item-label">Relevés</span>
      </a>

      <a href="<?= $_base ?>parametres/index.php" class="gtb-plus-item <?= $_nav==='parametres'?'active':'' ?>">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <span class="gtb-plus-item-label">Paramètres</span>
      </a>

      <a href="<?= $_base ?>profil/index.php" class="gtb-plus-item <?= $_nav==='profil'?'active':'' ?>" style="grid-column:span 2">
        <div class="gtb-plus-item-icon"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--dark)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
        <span class="gtb-plus-item-label">Mon Profil</span>
      </a>

    </div>
    <hr class="gtb-plus-sep"/>
    <a href="<?= $_base ?>../authentification/api/logout.php" class="gtb-plus-logout" onclick="return confirm('Se déconnecter ?')">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Déconnexion sécurisée
    </a>
  </div>
</div>

</body>
</html>
