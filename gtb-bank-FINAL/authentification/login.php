<?php
/**
 * authentification/login.php
 * Page de connexion — HTML inchangé, PHP greffé en haut.
 */
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/session.php';
require_once __DIR__ . '/../backend/security.php';
require_once __DIR__ . '/../backend/helpers.php';

Session::start();

// Si l'user est déjà connecté, on le redirige vers son espace
if (Session::isUser()) {
    redirect(GTB_BASE_URL . '/dashboard/index.php');
}
if (Session::isAdmin()) {
    redirect(GTB_BASE_URL . '/admin/index.php');
}

// Génère (ou récupère) le jeton CSRF de cette session
$csrf = Security::csrfToken();

// Message d'info selon la raison de redirection vers login
$fromMsg = match($_GET['from'] ?? '') {
    'expired'  => 'Votre session a expiré. Reconnectez-vous.',
    'timeout'  => 'Déconnecté automatiquement après inactivité.',
    'disabled' => 'Votre compte a été suspendu. Contactez le support.',
    '2fa'      => 'Authentification à deux facteurs requise.',
    default    => '',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="icon" type="image/png" href="/favicon.png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Connexion — Global Trust Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
:root{
  --green:#0D1B2A;--dark:#091520;--deeper:#050B14;
  --gold:#D4AF37;--mint:#EAD9B5;--sand:#F2F4F7;
  --white:#FFFFFF;--g50:#F8F9FA;--g100:#E9ECEF;
  --g200:#DEE2E6;--g400:#ADB5BD;--g600:#6C757D;--g800:#343A40;
  --red:#E5373A;--green-ok:#22C55E;
  --ease:cubic-bezier(.25,.46,.45,.94);
  --bounce:cubic-bezier(.34,1.56,.64,1);
  --r-sm:6px;--r-md:12px;--r-lg:18px;--r-xl:28px;--r-full:9999px;
  --sh-sm:0 2px 8px rgba(13,27,42,.08);
  --sh-md:0 8px 32px rgba(13,27,42,.12);
  --sh-lg:0 24px 64px rgba(13,27,42,.18);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'DM Sans',sans-serif;background:var(--g50);color:var(--g800);overflow-x:hidden;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
button{font-family:'DM Sans',sans-serif;cursor:pointer;border:none}
input{font-family:'DM Sans',sans-serif}

/* ══ LAYOUT ══ */
.auth-page{display:grid;grid-template-columns:1fr 1fr;min-height:100vh}

/* ══ LEFT PANEL ══ */
.auth-left{
  position:relative;overflow:hidden;
  background:linear-gradient(160deg,var(--deeper) 0%,var(--dark) 50%,#0a1e12 100%);
  display:flex;flex-direction:column;padding:2.5rem;
}
.auth-left-grid{
  position:absolute;inset:0;opacity:.05;
  background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);
  background-size:52px 52px;
}
.auth-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.auth-orb{position:absolute;border-radius:50%;filter:blur(90px);animation:orb-float 9s ease-in-out infinite}
.auth-orb-1{width:480px;height:480px;background:rgba(212,175,55,.12);top:-120px;left:-80px}
.auth-orb-2{width:360px;height:360px;background:rgba(34,197,94,.07);bottom:-60px;right:-40px;animation-delay:-4s}
.auth-orb-3{width:200px;height:200px;background:rgba(212,175,55,.08);top:45%;left:30%;animation-delay:-7s}
@keyframes orb-float{0%,100%{transform:translate(0,0)}40%{transform:translate(20px,-25px)}70%{transform:translate(-15px,18px)}}

.auth-logo{
  position:relative;z-index:5;
  display:flex;align-items:center;gap:.7rem;margin-bottom:auto;
}
.auth-logo-mark{
  width:42px;height:42px;border-radius:var(--r-sm);
  background:linear-gradient(135deg,var(--gold),rgba(212,175,55,.75));
  display:flex;align-items:center;justify-content:center;
  font-family:'Sora',sans-serif;font-weight:800;font-size:.8rem;color:white;
  letter-spacing:-.02em;
}
.auth-logo-text strong{font-family:'Sora',sans-serif;font-weight:700;font-size:.92rem;color:white;display:block}
.auth-logo-text span{font-size:.6rem;color:rgba(255,255,255,.35);letter-spacing:.08em;text-transform:uppercase}

.auth-left-content{position:relative;z-index:5;flex:1;display:flex;flex-direction:column;justify-content:center;padding:2rem 0}
.auth-eyebrow{
  display:inline-flex;align-items:center;gap:.55rem;
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  border-radius:var(--r-full);padding:.35rem .9rem;
  margin-bottom:1.8rem;align-self:flex-start;
}
.auth-eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--gold);animation:pulse-dot 2s ease-in-out infinite}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.55)}}
.auth-eyebrow span{font-size:.7rem;font-weight:600;color:var(--gold);letter-spacing:.1em;text-transform:uppercase}
.auth-left-title{
  font-family:'DM Serif Display',serif;
  font-size:clamp(2rem,3.5vw,3rem);
  color:white;line-height:1.1;letter-spacing:-.02em;
  margin-bottom:1.1rem;
}
.auth-left-title em{font-style:italic;color:var(--gold)}
.auth-left-sub{font-size:.92rem;color:rgba(255,255,255,.55);line-height:1.75;margin-bottom:2.5rem;max-width:380px}

.auth-badges{display:flex;flex-direction:column;gap:.85rem}
.auth-badge{
  display:flex;align-items:center;gap:.85rem;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
  border-radius:var(--r-lg);padding:.9rem 1.1rem;
  transition:background .25s;
}
.auth-badge:hover{background:rgba(255,255,255,.08)}
.auth-badge-icon{font-size:1.2rem;flex-shrink:0}
.auth-badge-text strong{display:block;font-size:.82rem;font-weight:600;color:white;margin-bottom:.1rem}
.auth-badge-text span{font-size:.72rem;color:rgba(255,255,255,.4)}

.auth-left-footer{
  position:relative;z-index:5;
  font-size:.68rem;color:rgba(255,255,255,.25);
  padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.07);
  margin-top:2.5rem;
}

/* ══ RIGHT PANEL ══ */
.auth-right{
  display:flex;align-items:center;justify-content:center;
  background:var(--g50);padding:2rem;
  overflow-y:auto;
}
.auth-box{
  width:100%;max-width:440px;
  background:white;border-radius:var(--r-xl);
  padding:clamp(2rem,4vw,2.8rem);
  box-shadow:var(--sh-lg);
  border:1.5px solid var(--g100);
}

/* TABS */
.auth-tabs{
  display:flex;background:var(--g50);border-radius:var(--r-full);
  padding:3px;margin-bottom:1.8rem;border:1.5px solid var(--g100);
}
.auth-tab{
  flex:1;padding:.52rem 1rem;border-radius:var(--r-full);
  font-size:.8rem;font-weight:500;color:var(--g600);
  background:none;border:none;cursor:pointer;
  transition:all .25s var(--ease);
}
.auth-tab.active{
  background:white;color:var(--g800);font-weight:700;
  box-shadow:var(--sh-sm);
}

.auth-form-title{
  font-family:'Sora',sans-serif;font-weight:700;
  font-size:1.25rem;color:var(--g800);margin-bottom:.3rem;
}
.auth-form-sub{font-size:.82rem;color:var(--g400);margin-bottom:1.8rem;line-height:1.5}

/* FORM */
.form-group{display:flex;flex-direction:column;gap:.45rem;margin-bottom:1.1rem}
.form-label{font-size:.72rem;font-weight:600;color:var(--g600);letter-spacing:.05em;text-transform:uppercase}
.input-wrap{position:relative}
.input-icon{
  position:absolute;left:1rem;top:50%;transform:translateY(-50%);
  font-size:.95rem;pointer-events:none;z-index:1;
}
.form-input{
  width:100%;padding:.85rem 1.1rem .85rem 2.7rem;
  font-family:'DM Sans',sans-serif;font-size:.9rem;color:var(--g800);
  background:var(--g50);border:1.5px solid var(--g100);
  border-radius:var(--r-md);outline:none;
  transition:all .25s var(--ease);
}
.form-input:focus{border-color:var(--gold);background:white;box-shadow:0 0 0 3px rgba(212,175,55,.12)}
.form-input::placeholder{color:var(--g400)}
.form-input.no-icon{padding-left:1.1rem}
.eye-btn{
  position:absolute;right:.9rem;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:.9rem;
  padding:.25rem;border-radius:var(--r-sm);transition:background .2s;
}
.eye-btn:hover{background:var(--g100)}

/* OPTIONS ROW */
.form-options{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;gap:.5rem;flex-wrap:wrap}
.checkbox-label{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.8rem;color:var(--g600)}
.checkbox-label input[type=checkbox]{
  width:16px;height:16px;border-radius:4px;
  accent-color:var(--gold);cursor:pointer;
}
.link-gold{font-size:.8rem;color:var(--gold);font-weight:500;transition:opacity .2s}
.link-gold:hover{opacity:.75}

/* BUTTONS */
.btn-full{
  width:100%;padding:.95rem;border-radius:var(--r-full);
  font-family:'DM Sans',sans-serif;font-weight:700;font-size:.9rem;
  border:none;cursor:pointer;transition:all .3s var(--ease);
}
.btn-primary-auth{
  background:linear-gradient(135deg,var(--gold),rgba(212,175,55,.85));
  color:white;box-shadow:0 6px 20px rgba(212,175,55,.4);
}
.btn-primary-auth:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(212,175,55,.5)}
.btn-primary-auth:active{transform:translateY(0)}

/* DIVIDER */
.auth-divider{
  display:flex;align-items:center;gap:.8rem;
  font-size:.75rem;color:var(--g400);margin:1.3rem 0;
}
.auth-divider::before,.auth-divider::after{content:'';flex:1;height:1px;background:var(--g100)}

/* SOCIAL */
.social-btns{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:1.3rem}
.social-btn{
  padding:.75rem;border-radius:var(--r-md);
  font-size:.8rem;font-weight:600;color:var(--g800);
  background:white;border:1.5px solid var(--g100);
  cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.4rem;
}
.social-btn:hover{border-color:var(--g200);background:var(--g50);transform:translateY(-1px)}

/* SECURITY BADGE */
.security-note{
  display:flex;align-items:center;gap:.5rem;
  background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);
  border-radius:var(--r-md);padding:.65rem .9rem;
  font-size:.72rem;color:#15803D;font-weight:500;
  margin-top:1.2rem;
}

/* FOOTER TEXT */
.auth-footer-text{text-align:center;font-size:.78rem;color:var(--g400);margin-top:1.2rem}
.auth-footer-text a{color:var(--gold);font-weight:600}
.auth-footer-text a:hover{text-decoration:underline}

/* ══ STEP 2FA ══ */
.step-2fa{text-align:center}
.step-2fa-icon{
  width:70px;height:70px;border-radius:50%;
  background:rgba(212,175,55,.1);border:2px solid rgba(212,175,55,.2);
  display:flex;align-items:center;justify-content:center;
  font-size:1.8rem;margin:0 auto 1.2rem;
  animation:icon-pulse 2s ease-in-out infinite;
}
@keyframes icon-pulse{0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.3)}50%{box-shadow:0 0 0 10px rgba(212,175,55,0)}}
.otp-label{font-size:.78rem;color:var(--g400);margin-bottom:.9rem}
.otp-grid{display:flex;gap:.6rem;justify-content:center;margin-bottom:1.5rem}
.otp-input{
  width:46px;height:56px;border-radius:var(--r-md);
  border:1.5px solid var(--g100);background:var(--g50);
  font-family:'Sora',sans-serif;font-weight:700;font-size:1.3rem;
  text-align:center;color:var(--g800);outline:none;
  transition:all .25s var(--ease);
}
.otp-input:focus{border-color:var(--gold);background:white;box-shadow:0 0 0 3px rgba(212,175,55,.12)}
.otp-input.filled{border-color:var(--gold);background:rgba(212,175,55,.06);color:var(--gold)}
.otp-resend{font-size:.78rem;color:var(--g400);margin-top:1rem}
.otp-resend a{color:var(--gold);font-weight:600}

/* ══ RESPONSIVE ══ */
@media(max-width:768px){
  .auth-page{display:block}
  .auth-left{display:none}
  .auth-right{
    min-height:100vh;width:100%;
    padding:1.5rem 1.1rem;
    display:flex;flex-direction:column;align-items:center;
    overflow-x:hidden;box-sizing:border-box;
  }
  .auth-box{
    width:100%;max-width:100%;
    box-shadow:none;border:none;background:transparent;
    padding:.75rem 0;
  }
  .otp-input{width:40px;height:50px;font-size:1.1rem}
}
@media(max-width:480px){
  .auth-right{padding:1.25rem .9rem}
  .social-btns{grid-template-columns:1fr}
  .form-options{flex-direction:column;align-items:flex-start;gap:.5rem}
  .auth-form-title{font-size:1.05rem}
  .auth-tabs{border-radius:var(--r-md)}
  .auth-tab{font-size:.73rem;padding:.4rem .5rem}
  .otp-grid{gap:.35rem}
  .otp-input{width:34px;height:44px;font-size:1rem}
  .btn-full{font-size:.85rem;padding:.85rem}
  .social-btn{font-size:.73rem;padding:.6rem}
  .form-input{font-size:.85rem;padding:.8rem 1rem .8rem 2.5rem}
}
</style>
<link rel="stylesheet" href="../assets/gtb-ds.css">
</head>
<body>

<div class="auth-page">

  <!-- ════ LEFT ════ -->
  <div class="auth-left">
    <div class="auth-left-grid"></div>
    <div class="auth-orbs">
      <div class="auth-orb auth-orb-1"></div>
      <div class="auth-orb auth-orb-2"></div>
      <div class="auth-orb auth-orb-3"></div>
    </div>

    <a href="../pages-publiques/index.html" class="auth-logo">
      <div class="auth-logo-mark">GTB</div>
      <div class="auth-logo-text">
        <strong>Global Trust Bank</strong>
        <span>Banque en ligne</span>
      </div>
    </a>

    <div class="auth-left-content">
      <div class="auth-eyebrow">
        <div class="auth-eyebrow-dot"></div>
        <span>Espace sécurisé</span>
      </div>
      <h1 class="auth-left-title">Bienvenue dans<br>votre espace<br><em>sécurisé</em></h1>
      <p class="auth-left-sub">Accédez à vos comptes, gérez vos finances et effectuez vos opérations bancaires en toute confiance depuis n'importe où.</p>

      <div class="auth-badges">
        <div class="auth-badge">
          <span class="auth-badge-icon">🔐</span>
          <div class="auth-badge-text">
            <strong>Connexion chiffrée SSL</strong>
            <span>Protocole TLS 1.3 — Certification bancaire</span>
          </div>
        </div>
        <div class="auth-badge">
          <span class="auth-badge-icon">📱</span>
          <div class="auth-badge-text">
            <strong>Authentification forte</strong>
            <span>Double facteur &amp; biométrie mobile</span>
          </div>
        </div>
        <div class="auth-badge">
          <span class="auth-badge-icon">⏱️</span>
          <div class="auth-badge-text">
            <strong>Session sécurisée</strong>
            <span>Déconnexion automatique après 10 min</span>
          </div>
        </div>
      </div>
    </div>

    <div class="auth-left-footer">© 2026 Global Trust Bank — Établissement de crédit agréé ACPR · Numéro SIREN 662 042 449</div>
  </div>

  <!-- ════ RIGHT ════ -->
  <div class="auth-right">
    <div class="auth-box">

      <!-- STEP 1 : Identifiants -->
      <div id="step-login">
        <div class="auth-tabs">
          <button class="auth-tab active" id="tab-login" onclick="GTB.auth.switchTab('login')">Se connecter</button>
          <button class="auth-tab" id="tab-register" onclick="window.location.href='signup.php'">S'inscrire</button>
        </div>

        <div class="auth-form-title" id="form-title">Content de vous revoir 👋</div>
        <div class="auth-form-sub" id="form-sub">Connectez-vous à votre espace Global Trust Bank.</div>

        <form id="login-form" onsubmit="GTB.auth.submitLogin(event)">
          <!-- Jeton CSRF injecté par PHP, invisible pour l'utilisateur -->
          <input type="hidden" id="csrf-token" value="<?= $csrf ?>">

          <!-- Message de redirection (session expirée, etc.) -->
          <?php if ($fromMsg): ?>
          <div style="background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.3);color:#b8891d;font-size:.78rem;padding:.7rem .9rem;border-radius:var(--r-md);margin-bottom:1rem">
            ℹ️ <?= e($fromMsg) ?>
          </div>
          <?php endif; ?>
          <div class="form-group">
            <label class="form-label">Identifiant ou email</label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input class="form-input" type="email" id="email-input" placeholder="john.doe@email.com" autocomplete="email"/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <div class="input-wrap">
              <span class="input-icon">🔑</span>
              <input class="form-input" type="password" id="pw-input" placeholder="••••••••" autocomplete="current-password"/>
              <button type="button" class="eye-btn" id="eye-btn">👁️</button>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" id="remember" checked/>
              <span>Se souvenir de moi</span>
            </label>
            <a href="forgot-password.php" class="link-gold">Mot de passe oublié ?</a>
          </div>

          <div id="login-error" style="display:none;background:rgba(229,55,58,.08);border:1px solid rgba(229,55,58,.2);color:var(--red);font-size:.78rem;padding:.7rem .9rem;border-radius:var(--r-md);margin-bottom:1rem">
            ⚠️ Identifiants incorrects. Vérifiez votre email et mot de passe.
          </div>

          <button type="submit" class="btn-full btn-primary-auth" id="login-btn">
            <span id="login-btn-text">Se connecter →</span>
          </button>
        </form>

        <div class="auth-divider"><span>ou continuer avec</span></div>

        <div class="social-btns">
          <button class="social-btn">🔑 France Connect</button>
          <button class="social-btn">📱 App mobile</button>
        </div>

        <div class="security-note">
          <span>🔒</span>
          <span>Connexion 100% sécurisée — vos données sont protégées</span>
        </div>

        <div class="auth-footer-text">
          Pas encore client ? <a href="signup.php">Ouvrir un compte gratuit</a>
        </div>
      </div>

      <!-- STEP 2 : OTP 2FA -->
      <div id="step-2fa" style="display:none" class="step-2fa">
        <div class="step-2fa-icon">✉️</div>
        <div class="auth-form-title">Vérification en deux étapes</div>
        <div class="auth-form-sub">
          Nous avons envoyé un code à 6 chiffres au<br>
          <strong style="color:var(--g800)" id="email-mask">vo••@exemple.com</strong>
        </div>

        <div class="otp-label">Saisissez le code reçu par email</div>
        <div class="otp-grid" id="otp-grid">
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
        </div>

        <div id="otp-timer" style="font-size:.75rem;color:var(--g400);margin-bottom:1rem">Code valide pendant <strong id="timer-val" style="color:var(--gold)">04:59</strong></div>

        <button class="btn-full btn-primary-auth" onclick="GTB.auth.validateOTP()">Valider l'accès →</button>

        <div class="otp-resend">
          Code non reçu ? <a href="#" onclick="GTB.auth.resendOTP();return false">Renvoyer le code</a>
          · <a href="#" onclick="GTB.auth.backToLogin();return false">Retour</a>
        </div>

        <div class="security-note" style="margin-top:1.2rem">
          <span>🛡️</span><span>Ne communiquez jamais ce code à personne, même un conseiller GTB.</span>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
/* ================================================================
   GTB Auth — JS câblé sur les API PHP réelles
   Les fonctions UI (tabs, timer, OTP inputs) sont INCHANGÉES.
   Seuls submitLogin et validateOTP envoient maintenant de vraies
   requêtes vers le backend.
================================================================ */
const GTB = {
  auth: {
    timerInterval: null,
    emailMask: '',

    switchTab(tab) {
      document.getElementById('tab-login').classList.toggle('active', tab === 'login');
      document.getElementById('tab-register').classList.toggle('active', tab === 'register');
    },

    // ── ÉTAPE 1 : Email + Password → API login.php ──
    async submitLogin(e) {
      e.preventDefault();
      const email  = document.getElementById('email-input').value.trim();
      const pw     = document.getElementById('pw-input').value;
      const errEl  = document.getElementById('login-error');
      const btn    = document.getElementById('login-btn');
      const btnTxt = document.getElementById('login-btn-text');
      const csrf   = document.getElementById('csrf-token').value;

      errEl.style.display = 'none';
      if (!email || !pw) {
        errEl.style.display = 'block';
        errEl.textContent = '⚠️ Veuillez remplir tous les champs.';
        return;
      }

      // UI — état chargement
      btnTxt.textContent = 'Connexion en cours…';
      btn.style.opacity = '.75';
      btn.disabled = true;

      try {
        const res = await fetch('api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password: pw, csrf_token: csrf }),
        });
        const data = await res.json();

        if (!data.success) {
          errEl.style.display = 'block';
          errEl.textContent = '⚠️ ' + (data.error || 'Identifiants invalides.');
          return;
        }

        // Connexion directe (2FA désactivé)
        if (data.redirect) {
          window.location.href = data.redirect;
          return;
        }

        // ✓ Étape 1 réussie → passer au code OTP
        this.emailMask = data.email_mask || '';
        if (data.otp_dev) {
          console.info('%c[GTB DEV] Code OTP : ' + data.otp_dev,
            'background:#D4AF37;color:#000;font-weight:bold;padding:4px 8px;border-radius:4px');
        }
        this.goTo2FA();

      } catch (err) {
        errEl.style.display = 'block';
        errEl.textContent = '⚠️ Erreur réseau. Vérifiez votre connexion.';
      } finally {
        btn.style.opacity = '1';
        btn.disabled = false;
        btnTxt.textContent = 'Se connecter →';
      }
    },

    goTo2FA() {
      document.getElementById('step-login').style.display = 'none';
      document.getElementById('step-2fa').style.display = 'block';
      const mask = document.getElementById('email-mask');
      if (mask && this.emailMask) mask.textContent = this.emailMask;
      document.querySelector('.otp-input').focus();
      this.startTimer(299);
    },

    backToLogin() {
      document.getElementById('step-2fa').style.display = 'none';
      document.getElementById('step-login').style.display = 'block';
      clearInterval(this.timerInterval);
      document.querySelectorAll('.otp-input').forEach(i => {
        i.value = '';
        i.classList.remove('filled');
      });
    },

    // ── ÉTAPE 2 : Code OTP → API verify_otp.php ──
    async validateOTP() {
      const digits = [...document.querySelectorAll('.otp-input')].map(i => i.value).join('');
      if (digits.length < 6) {
        document.querySelectorAll('.otp-input').forEach(i => {
          i.style.borderColor = 'var(--red)';
          setTimeout(() => i.style.borderColor = '', 1500);
        });
        return;
      }
      clearInterval(this.timerInterval);

      try {
        const res = await fetch('api/verify_otp.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: digits }),
        });
        const data = await res.json();

        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        } else {
          document.querySelectorAll('.otp-input').forEach(i => {
            i.style.borderColor = 'var(--red)';
          });
          // Afficher l'erreur
          const errOtp = document.getElementById('otp-timer');
          if (errOtp) {
            errOtp.innerHTML = '<span style="color:var(--red)">⚠️ ' + (data.error || 'Code incorrect.') + '</span>';
          }
        }
      } catch {
        alert('Erreur réseau lors de la vérification du code.');
      }
    },

    resendOTP() {
      document.querySelectorAll('.otp-input').forEach(i => {
        i.value = '';
        i.classList.remove('filled');
      });
      document.querySelector('.otp-input').focus();
      this.startTimer(299);
    },

    startTimer(seconds) {
      clearInterval(this.timerInterval);
      let remaining = seconds;
      const el = document.getElementById('timer-val');
      const update = () => {
        if (!el) return;
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        el.textContent = `${m}:${s}`;
        if (remaining <= 0) {
          clearInterval(this.timerInterval);
          el.textContent = 'expiré';
          el.style.color = 'var(--red)';
        }
        remaining--;
      };
      update();
      this.timerInterval = setInterval(update, 1000);
    },

    initOTP() {
      const inputs = document.querySelectorAll('.otp-input');
      inputs.forEach((inp, i) => {
        inp.addEventListener('input', () => {
          inp.value = inp.value.replace(/\D/g, '').slice(-1);
          inp.classList.toggle('filled', !!inp.value);
          if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
          const all = [...inputs].every(x => x.value);
          if (all) setTimeout(() => this.validateOTP(), 300);
        });
        inp.addEventListener('keydown', e => {
          if (e.key === 'Backspace' && !inp.value && i > 0) {
            inputs[i - 1].focus();
            inputs[i - 1].value = '';
            inputs[i - 1].classList.remove('filled');
          }
        });
        inp.addEventListener('paste', e => {
          e.preventDefault();
          const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
          [...text.slice(0, 6)].forEach((c, j) => {
            if (inputs[i + j]) { inputs[i + j].value = c; inputs[i + j].classList.add('filled'); }
          });
          const nextEmpty = [...inputs].findIndex(x => !x.value);
          if (nextEmpty >= 0) inputs[nextEmpty].focus();
          else inputs[inputs.length - 1].focus();
        });
      });
    }
  }
};

// Initialisation
document.getElementById('eye-btn').addEventListener('click', function () {
  const f = document.getElementById('pw-input');
  f.type = f.type === 'password' ? 'text' : 'password';
  this.textContent = f.type === 'password' ? '👁️' : '🙈';
});
GTB.auth.initOTP();
</script>

<script src="../pages-publiques/app-nav.js"></script>
</body>
</html>
