<?php
/**
 * authentification/signup.php
 * Inscription 3 étapes — HTML inchangé, PHP + API greffés.
 */
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/session.php';
require_once __DIR__ . '/../backend/security.php';
require_once __DIR__ . '/../backend/helpers.php';
Session::start();

// Rediriger si déjà connecté
if (Session::isUser()) {
    redirect(GTB_BASE_URL . '/dashboard/index.php');
}
$csrf = Security::csrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Ouvrir un compte — Global Trust Bank</title>
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
input,select{font-family:'DM Sans',sans-serif}

.auth-page{display:grid;grid-template-columns:1fr 1fr;min-height:100vh}

/* ── LEFT ── */
.auth-left{
  position:relative;overflow:hidden;
  background:linear-gradient(160deg,var(--deeper) 0%,var(--dark) 60%,#091520 100%);
  display:flex;flex-direction:column;padding:2.5rem;
}
.auth-left-grid{position:absolute;inset:0;opacity:.05;background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:52px 52px}
.auth-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.auth-orb{position:absolute;border-radius:50%;filter:blur(90px);animation:orb-float 9s ease-in-out infinite}
.auth-orb-1{width:500px;height:500px;background:rgba(212,175,55,.1);top:-100px;right:-60px}
.auth-orb-2{width:300px;height:300px;background:rgba(13,27,42,.15);bottom:-80px;left:-30px;animation-delay:-5s}
@keyframes orb-float{0%,100%{transform:translate(0,0)}45%{transform:translate(25px,-20px)}75%{transform:translate(-12px,22px)}}

.auth-logo{position:relative;z-index:5;display:flex;align-items:center;gap:.7rem;margin-bottom:auto}
.auth-logo-mark{width:42px;height:42px;border-radius:var(--r-sm);background:linear-gradient(135deg,var(--gold),rgba(212,175,55,.75));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.8rem;color:white}
.auth-logo-text strong{font-family:'Sora',sans-serif;font-weight:700;font-size:.92rem;color:white;display:block}
.auth-logo-text span{font-size:.6rem;color:rgba(255,255,255,.35);letter-spacing:.08em;text-transform:uppercase}

.auth-left-content{position:relative;z-index:5;flex:1;display:flex;flex-direction:column;justify-content:center;padding:2rem 0}
.auth-left-title{font-family:'DM Serif Display',serif;font-size:clamp(1.9rem,3.2vw,2.8rem);color:white;line-height:1.1;letter-spacing:-.02em;margin-bottom:1rem}
.auth-left-title em{font-style:italic;color:var(--gold)}
.auth-left-sub{font-size:.88rem;color:rgba(255,255,255,.5);line-height:1.75;margin-bottom:2.5rem;max-width:380px}

/* Avantages */
.avantages{display:flex;flex-direction:column;gap:.75rem}
.avantage-item{display:flex;align-items:center;gap:.8rem;padding:.8rem 1rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:var(--r-lg)}
.avantage-icon{font-size:1.1rem;flex-shrink:0}
.avantage-text{font-size:.82rem;color:rgba(255,255,255,.7);font-weight:500}
.avantage-text strong{color:var(--gold)}

/* Steps indicator (left panel) */
.steps-left{display:flex;flex-direction:column;gap:1rem;margin-top:2.5rem}
.step-left-item{display:flex;align-items:center;gap:.75rem}
.step-left-num{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-family:'Sora',sans-serif;font-weight:700;font-size:.75rem;
  border:1.5px solid rgba(255,255,255,.2);color:rgba(255,255,255,.4);
  transition:all .4s var(--ease);
}
.step-left-num.done{background:var(--green-ok);border-color:var(--green-ok);color:white}
.step-left-num.active{background:var(--gold);border-color:var(--gold);color:white}
.step-left-label{font-size:.8rem;color:rgba(255,255,255,.4);transition:color .4s}
.step-left-label.active{color:white;font-weight:600}
.step-left-label.done{color:rgba(255,255,255,.55)}

.auth-left-footer{position:relative;z-index:5;font-size:.68rem;color:rgba(255,255,255,.25);padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.07);margin-top:2.5rem}

/* ── RIGHT ── */
.auth-right{display:flex;align-items:flex-start;justify-content:center;background:var(--g50);padding:2.5rem 2rem;overflow-y:auto}
.auth-box{width:100%;max-width:480px;background:white;border-radius:var(--r-xl);padding:clamp(1.8rem,4vw,2.6rem);box-shadow:var(--sh-lg);border:1.5px solid var(--g100);margin:auto}

/* Step indicator (top of form) */
.step-indicator{display:flex;align-items:center;gap:.5rem;margin-bottom:2rem}
.step-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.78rem;border:2px solid var(--g200);color:var(--g400);transition:all .35s var(--ease);flex-shrink:0}
.step-dot.active{background:var(--gold);border-color:var(--gold);color:white;box-shadow:0 4px 14px rgba(212,175,55,.4)}
.step-dot.done{background:var(--green-ok);border-color:var(--green-ok);color:white}
.step-line{flex:1;height:2px;background:var(--g100);border-radius:var(--r-full);transition:background .4s}
.step-line.done{background:var(--green-ok)}

.form-title{font-family:'Sora',sans-serif;font-weight:700;font-size:1.2rem;color:var(--g800);margin-bottom:.25rem}
.form-sub{font-size:.82rem;color:var(--g400);margin-bottom:1.6rem;line-height:1.5}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.form-group{display:flex;flex-direction:column;gap:.45rem;margin-bottom:1rem}
.form-label{font-size:.72rem;font-weight:600;color:var(--g600);letter-spacing:.05em;text-transform:uppercase}
.input-wrap{position:relative}
.input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);font-size:.9rem;pointer-events:none;z-index:1}
.form-input{
  width:100%;padding:.85rem 1.1rem .85rem 2.65rem;
  font-size:.9rem;color:var(--g800);
  background:var(--g50);border:1.5px solid var(--g100);
  border-radius:var(--r-md);outline:none;
  transition:all .25s var(--ease);
}
.form-input.no-icon{padding-left:1.1rem}
.form-input:focus{border-color:var(--gold);background:white;box-shadow:0 0 0 3px rgba(212,175,55,.12)}
.form-input::placeholder{color:var(--g400)}
.form-hint{font-size:.7rem;color:var(--g400)}

/* Plan cards */
.plan-cards{display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.4rem}
.plan-card{
  display:flex;align-items:center;gap:1rem;padding:1.1rem 1.2rem;
  border:1.5px solid var(--g100);border-radius:var(--r-xl);
  cursor:pointer;transition:all .25s var(--ease);position:relative;overflow:hidden;
}
.plan-card:hover{border-color:rgba(212,175,55,.4);background:rgba(212,175,55,.03)}
.plan-card.selected{border-color:var(--gold);background:rgba(212,175,55,.06)}
.plan-card-radio{
  width:20px;height:20px;border-radius:50%;border:2px solid var(--g200);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  transition:all .25s var(--ease);
}
.plan-card.selected .plan-card-radio{border-color:var(--gold);background:var(--gold)}
.plan-card.selected .plan-card-radio::after{content:'✓';font-size:.65rem;color:white;font-weight:700}
.plan-card-icon{font-size:1.4rem;flex-shrink:0}
.plan-card-info{flex:1}
.plan-card-name{font-weight:700;font-size:.9rem;color:var(--g800)}
.plan-card-desc{font-size:.75rem;color:var(--g400);margin-top:.15rem}
.plan-card-price{font-family:'Sora',sans-serif;font-weight:800;font-size:1rem;color:var(--g800);flex-shrink:0}
.plan-card-price small{font-size:.65rem;font-weight:400;color:var(--g400);display:block;text-align:right}
.plan-featured-badge{position:absolute;top:.6rem;right:.6rem;background:var(--gold);color:white;font-size:.58rem;font-weight:700;padding:.2rem .55rem;border-radius:var(--r-full)}

/* CGU */
.cgu-check{display:flex;align-items:flex-start;gap:.6rem;font-size:.78rem;color:var(--g600);cursor:pointer;margin-bottom:1.2rem}
.cgu-check input{margin-top:2px;accent-color:var(--gold)}
.cgu-check a{color:var(--gold);font-weight:500}

/* Boutons nav */
.form-nav{display:flex;gap:.8rem;margin-top:1rem}
.btn-full{width:100%;padding:.95rem;border-radius:var(--r-full);font-family:'DM Sans',sans-serif;font-weight:700;font-size:.9rem;border:none;cursor:pointer;transition:all .3s var(--ease)}
.btn-primary-auth{background:linear-gradient(135deg,var(--gold),rgba(212,175,55,.85));color:white;box-shadow:0 6px 20px rgba(212,175,55,.4)}
.btn-primary-auth:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(212,175,55,.5)}
.btn-secondary-auth{background:white;color:var(--g800);border:1.5px solid var(--g100);box-shadow:var(--sh-sm)}
.btn-secondary-auth:hover{border-color:var(--g200);background:var(--g50)}

/* Succès */
.success-state{text-align:center;padding:1.5rem 0}
.success-icon{width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,.1);border:2px solid rgba(34,197,94,.25);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.5rem;animation:success-pop .6s var(--bounce) both}
@keyframes success-pop{0%{transform:scale(0);opacity:0}100%{transform:scale(1);opacity:1}}
.success-title{font-family:'Sora',sans-serif;font-weight:800;font-size:1.3rem;color:var(--g800);margin-bottom:.5rem}
.success-sub{font-size:.85rem;color:var(--g400);line-height:1.65;margin-bottom:2rem}

.auth-footer-text{text-align:center;font-size:.78rem;color:var(--g400);margin-top:1.2rem}
.auth-footer-text a{color:var(--gold);font-weight:600}

.password-strength{margin-top:.4rem}
.strength-bar{height:4px;background:var(--g100);border-radius:var(--r-full);overflow:hidden;margin-bottom:.3rem}
.strength-fill{height:100%;border-radius:var(--r-full);transition:width .4s var(--ease),background .4s}
.strength-label{font-size:.68rem;color:var(--g400)}

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
  .form-row{grid-template-columns:1fr}
}
@media(max-width:480px){
  .auth-right{padding:1.25rem .9rem}
  .auth-form-title{font-size:1.05rem}
  .btn-full{font-size:.85rem;padding:.85rem}
  .form-input{font-size:.85rem}
  .step-indicator{gap:.4rem}
  .step-num{width:28px;height:28px;font-size:.7rem}
}
</style>
</head>
<body>

<div class="auth-page">

  <!-- ════ LEFT ════ -->
  <div class="auth-left">
    <div class="auth-left-grid"></div>
    <div class="auth-orbs">
      <div class="auth-orb auth-orb-1"></div>
      <div class="auth-orb auth-orb-2"></div>
    </div>

    <a href="../pages-publiques/index.html" class="auth-logo">
      <div class="auth-logo-mark">GTB</div>
      <div class="auth-logo-text">
        <strong>Global Trust Bank</strong>
        <span>Banque en ligne</span>
      </div>
    </a>

    <div class="auth-left-content">
      <h1 class="auth-left-title">Rejoignez<br><em>GTB</em> en<br>10 minutes</h1>
      <p class="auth-left-sub">Ouvrez votre compte bancaire entièrement en ligne. Sans paperasse, sans rendez-vous, sans frais cachés.</p>

      <div class="avantages">
        <div class="avantage-item"><span class="avantage-icon">⚡</span><span class="avantage-text"><strong>Ouverture instantanée</strong> — Compte actif en 10 min</span></div>
        <div class="avantage-item"><span class="avantage-icon">💳</span><span class="avantage-text"><strong>Carte offerte</strong> — Visa ou Mastercard dès l'ouverture</span></div>
        <div class="avantage-item"><span class="avantage-icon">🌍</span><span class="avantage-text"><strong>Paiements mondiaux</strong> — 150+ pays sans frais cachés</span></div>
        <div class="avantage-item"><span class="avantage-icon">🔒</span><span class="avantage-text"><strong>100% sécurisé</strong> — Certifié PCI DSS niveau 1</span></div>
      </div>

      <div class="steps-left" id="steps-left">
        <div class="step-left-item">
          <div class="step-left-num active" id="sl-1">1</div>
          <span class="step-left-label active" id="sl-label-1">Vos informations</span>
        </div>
        <div class="step-left-item">
          <div class="step-left-num" id="sl-2">2</div>
          <span class="step-left-label" id="sl-label-2">Votre offre</span>
        </div>
        <div class="step-left-item">
          <div class="step-left-num" id="sl-3">3</div>
          <span class="step-left-label" id="sl-label-3">Confirmation</span>
        </div>
      </div>
    </div>

    <div class="auth-left-footer">© 2026 Global Trust Bank — ACPR agréé · Dépôts garantis jusqu'à 100 000€ (FGDR)</div>
  </div>

  <!-- ════ RIGHT ════ -->
  <div class="auth-right">
    <div class="auth-box">

      <!-- CSRF token PHP — invisible -->
      <input type="hidden" id="signup-csrf" value="<?= $csrf ?>">

      <!-- Step indicator -->
      <div class="step-indicator">
        <div class="step-dot active" id="sd-1">1</div>
        <div class="step-line" id="sl-line-1"></div>
        <div class="step-dot" id="sd-2">2</div>
        <div class="step-line" id="sl-line-2"></div>
        <div class="step-dot" id="sd-3">3</div>
      </div>

      <!-- STEP 1 : Informations personnelles -->
      <div id="step-1">
        <div class="form-title">Vos informations personnelles</div>
        <div class="form-sub">Commençons par vous connaître. Toutes les données sont chiffrées.</div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Prénom</label>
            <div class="input-wrap">
              <span class="input-icon">👤</span>
              <input class="form-input" type="text" placeholder="John" id="prenom"/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nom</label>
            <div class="input-wrap">
              <span class="input-icon">👤</span>
              <input class="form-input" type="text" placeholder="Doe" id="nom"/>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-wrap">
            <span class="input-icon">✉️</span>
            <input class="form-input" type="email" placeholder="prenom.nom@email.com" id="email"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <div class="input-wrap">
            <span class="input-icon">📱</span>
            <input class="form-input" type="tel" placeholder="+33 6 00 00 00 00" id="phone"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Date de naissance</label>
            <input class="form-input no-icon" type="date" id="birthday"/>
          </div>
          <div class="form-group">
            <label class="form-label">Pays de résidence</label>
            <select class="form-input no-icon" id="pays">
              <option value="">— Sélectionnez votre pays —</option>
              <optgroup label="🇪🇺 Europe">
                <option value="FR">🇫🇷 France</option>
                <option value="BE">🇧🇪 Belgique</option>
                <option value="CH">🇨🇭 Suisse</option>
                <option value="LU">🇱🇺 Luxembourg</option>
                <option value="DE">🇩🇪 Allemagne</option>
                <option value="ES">🇪🇸 Espagne</option>
                <option value="IT">🇮🇹 Italie</option>
                <option value="PT">🇵🇹 Portugal</option>
                <option value="NL">🇳🇱 Pays-Bas</option>
                <option value="GB">🇬🇧 Royaume-Uni</option>
                <option value="AT">🇦🇹 Autriche</option>
                <option value="SE">🇸🇪 Suède</option>
                <option value="DK">🇩🇰 Danemark</option>
                <option value="FI">🇫🇮 Finlande</option>
                <option value="NO">🇳🇴 Norvège</option>
                <option value="PL">🇵🇱 Pologne</option>
                <option value="IE">🇮🇪 Irlande</option>
                <option value="GR">🇬🇷 Grèce</option>
              </optgroup>
              <optgroup label="🌍 International">
                <option value="CA">🇨🇦 Canada</option>
                <option value="US">🇺🇸 États-Unis</option>
                <option value="AU">🇦🇺 Australie</option>
                <option value="JP">🇯🇵 Japon</option>
                <option value="AE">🇦🇪 Émirats Arabes Unis</option>
                <option value="QA">🇶🇦 Qatar</option>
                <option value="SG">🇸🇬 Singapour</option>
                <option value="BR">🇧🇷 Brésil</option>
                <option value="MX">🇲🇽 Mexique</option>
              </optgroup>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Type de document d'identité <span style="color:var(--red,#dc2626)">*</span></label>
          <select class="form-input no-icon" id="doc-type" onchange="GTB.signup.checkDocType(this.value)">
            <option value="">— Sélectionner votre document —</option>
            <option value="cni_ue">🪪 Carte Nationale d'Identité (Union Européenne)</option>
            <option value="passeport">🛂 Passeport</option>
            <option value="permis">🚗 Permis de conduire</option>
            <option value="autre">📋 Autre document</option>
          </select>
          <div id="doc-warn" style="display:none;background:rgba(220,38,38,.07);border:1px solid rgba(220,38,38,.25);color:#b91c1c;font-size:.76rem;padding:.55rem .85rem;border-radius:8px;margin-top:.4rem;line-height:1.45">
            ⚠️ <strong>Document non accepté.</strong> Seule la <strong>Carte Nationale d'Identité européenne (CNI-UE)</strong> permet d'ouvrir un compte GTB Bank.
          </div>
          <div id="doc-ok" style="display:none;background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.25);color:#065f46;font-size:.76rem;padding:.55rem .85rem;border-radius:8px;margin-top:.4rem">
            ✓ Document accepté — Carte Nationale d'Identité européenne
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <div class="input-wrap">
            <span class="input-icon">🔑</span>
            <input class="form-input" type="password" placeholder="Minimum 8 caractères" id="password" oninput="GTB.signup.checkStrength(this.value)"/>
          </div>
          <div class="password-strength">
            <div class="strength-bar"><div class="strength-fill" id="strength-fill" style="width:0%;background:var(--red)"></div></div>
            <span class="strength-label" id="strength-label">Saisissez un mot de passe</span>
          </div>
        </div>

        <div id="step1-error" style="display:none;background:rgba(229,55,58,.08);border:1px solid rgba(229,55,58,.2);color:var(--red);font-size:.78rem;padding:.7rem .9rem;border-radius:var(--r-md);margin-bottom:1rem">
          ⚠️ Veuillez remplir tous les champs obligatoires.
        </div>

        <button class="btn-full btn-primary-auth" onclick="GTB.signup.goStep(2)">Continuer →</button>

        <div class="auth-footer-text">Déjà client ? <a href="login.php">Se connecter</a></div>
      </div>

      <!-- STEP 2 : Choix de l'offre -->
      <div id="step-2" style="display:none">
        <div class="form-title">Choisissez votre offre</div>
        <div class="form-sub">Sélectionnez le compte qui correspond à votre profil. Modifiable à tout moment.</div>

        <div class="plan-cards" id="plan-cards">
          <div class="plan-card selected" data-plan="standard" onclick="GTB.signup.selectPlan(this)">
            <div class="plan-card-radio"></div>
            <span class="plan-card-icon">🏦</span>
            <div class="plan-card-info">
              <div class="plan-card-name">Compte Standard</div>
              <div class="plan-card-desc">Idéal pour la gestion quotidienne</div>
            </div>
            <div class="plan-card-price">Gratuit<small>/ mois</small></div>
          </div>
          <div class="plan-card" data-plan="premium" onclick="GTB.signup.selectPlan(this)">
            <div class="plan-featured-badge">Populaire</div>
            <div class="plan-card-radio"></div>
            <span class="plan-card-icon">👑</span>
            <div class="plan-card-info">
              <div class="plan-card-name">Compte Premium</div>
              <div class="plan-card-desc">Carte Gold + assurances + épargne boostée</div>
            </div>
            <div class="plan-card-price">9,90€<small>/ mois</small></div>
          </div>
          <div class="plan-card" data-plan="business" onclick="GTB.signup.selectPlan(this)">
            <div class="plan-card-radio"></div>
            <span class="plan-card-icon">💼</span>
            <div class="plan-card-info">
              <div class="plan-card-name">Compte Business</div>
              <div class="plan-card-desc">Pour entrepreneurs et professions libérales</div>
            </div>
            <div class="plan-card-price">19,90€<small>/ mois</small></div>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:1.2rem">
          <label class="form-label">Code parrainage (optionnel)</label>
          <div class="input-wrap">
            <span class="input-icon">🎁</span>
            <input class="form-input" type="text" placeholder="ex: GTB-XXXX" id="parrainage"/>
          </div>
          <span class="form-hint">Un code valide vous offre 3 mois de frais offerts</span>
        </div>

        <label class="cgu-check">
          <input type="checkbox" id="cgu-check"/>
          <span>J'accepte les <a href="../pages-publiques/mentions-legales.html" target="_blank">Conditions Générales d'Utilisation</a> et la <a href="../pages-publiques/confidentialites.html" target="_blank">Politique de confidentialité</a> de GTB.</span>
        </label>

        <div class="form-nav">
          <button class="btn-full btn-secondary-auth" onclick="GTB.signup.goStep(1)" style="max-width:120px">← Retour</button>
          <button class="btn-full btn-primary-auth" onclick="GTB.signup.goStep(3)">Confirmer →</button>
        </div>
      </div>

      <!-- STEP 3 : Confirmation + vérification email -->
      <div id="step-3" style="display:none">
        <div class="success-state">
          <div class="success-icon">✅</div>
          <div class="success-title">Compte créé avec succès !</div>
          <div class="success-sub" id="success-sub">
            Félicitations ! Votre compte GTB est ouvert.<br>
            Un email de confirmation a été envoyé à<br>
            <strong id="confirm-email" style="color:var(--gold)">votre adresse</strong>.<br><br>
            Vérifiez votre boîte de réception pour activer votre compte.
          </div>

          <div style="background:var(--sand);border-radius:var(--r-xl);padding:1.3rem;margin-bottom:1.8rem;text-align:left">
            <div style="font-size:.72rem;font-weight:600;color:var(--g400);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.8rem">Récapitulatif</div>
            <div style="display:flex;flex-direction:column;gap:.5rem">
              <div style="display:flex;justify-content:space-between;font-size:.85rem">
                <span style="color:var(--g400)">Offre choisie</span>
                <span style="font-weight:700;color:var(--g800)" id="recap-plan">Standard</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.85rem">
                <span style="color:var(--g400)">Compte actif</span>
                <span style="font-weight:700;color:var(--green-ok)">Dès validation email</span>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:.85rem">
                <span style="color:var(--g400)">Carte bancaire</span>
                <span style="font-weight:700;color:var(--g800)">Envoyée sous 5 jours</span>
              </div>
            </div>
          </div>

          <a href="login.php" class="btn-full btn-primary-auth" style="display:flex;justify-content:center;margin-bottom:.8rem">
            Se connecter →
          </a>
          <a href="login.php" style="display:block;text-align:center;font-size:.82rem;color:var(--g400)">
            Se connecter directement
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
const GTB = {
  signup: {
    currentStep: 1,
    selectedPlan: 'standard',

    // ── Navigation entre étapes ──
    async goStep(n) {
      const errEl = document.getElementById('step1-error');

      // Validation locale step 1 (avant d'aller au step 2)
      if (n === 2 && this.currentStep === 1) {
        const fields = ['prenom', 'nom', 'email', 'phone', 'password'];
        const empty = fields.some(f => !document.getElementById(f)?.value.trim());
        if (empty) { errEl.style.display = 'block'; return; }
        const docType = document.getElementById('doc-type')?.value;
        if (!docType) {
          errEl.textContent = '⚠️ Veuillez sélectionner votre type de document d\'identité.';
          errEl.style.display = 'block'; return;
        }
        if (docType !== 'cni_ue') {
          errEl.textContent = '⚠️ Seule la Carte Nationale d\'Identité européenne (CNI-UE) est acceptée pour ouvrir un compte.';
          errEl.style.display = 'block'; return;
        }
        errEl.style.display = 'none';
      }

      // Validation + envoi API au step 3
      if (n === 3 && this.currentStep === 2) {
        if (!document.getElementById('cgu-check')?.checked) {
          alert('Veuillez accepter les CGU pour continuer.');
          return;
        }

        // ── Appel API signup ──
        const submitBtn = document.querySelector('#step-2 .btn-primary-auth');
        const originalTxt = submitBtn?.textContent || 'Confirmer →';
        if (submitBtn) { submitBtn.textContent = 'Création en cours…'; submitBtn.disabled = true; }

        try {
          const res = await fetch('api/signup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              prenom:        document.getElementById('prenom')?.value.trim(),
              nom:           document.getElementById('nom')?.value.trim(),
              email:         document.getElementById('email')?.value.trim(),
              phone:         document.getElementById('phone')?.value.trim(),
              birthday:      document.getElementById('birthday')?.value || null,
              pays:          document.getElementById('pays')?.value || 'FR',
              password:      document.getElementById('password')?.value,
              document_type: document.getElementById('doc-type')?.value,
              plan:          this.selectedPlan,
              parrainage:    document.getElementById('parrainage')?.value.trim() || '',
              cgu_accepted:  true,
              csrf_token:    document.getElementById('signup-csrf')?.value,
            }),
          });
          const data = await res.json();

          if (!data.success) {
            // Afficher les erreurs renvoyées par le serveur
            if (submitBtn) { submitBtn.textContent = originalTxt; submitBtn.disabled = false; }
            const msg = data.fields
              ? Object.values(data.fields).join('\n')
              : (data.error || 'Erreur lors de la création.');
            alert('⚠️ ' + msg);
            return;
          }

          // ✓ Succès : rediriger vers la page de vérification email
          const userEmail = data.email || document.getElementById('email')?.value.trim();
          sessionStorage.setItem('gtb_signup_email', userEmail);
          window.location.href = 'verification.html?email=' + encodeURIComponent(userEmail);
          return;

        } catch (err) {
          if (submitBtn) { submitBtn.textContent = originalTxt; submitBtn.disabled = false; }
          alert('⚠️ Erreur réseau. Vérifiez votre connexion et réessayez.');
          return;
        }
      }

      // Transition d'affichage
      document.getElementById(`step-${this.currentStep}`).style.display = 'none';
      document.getElementById(`step-${n}`).style.display = 'block';
      this.currentStep = n;
      this.updateIndicators(n);
    },

    updateIndicators(n) {
      [1, 2, 3].forEach(i => {
        const dot     = document.getElementById(`sd-${i}`);
        const slNum   = document.getElementById(`sl-${i}`);
        const slLabel = document.getElementById(`sl-label-${i}`);
        if (!dot) return;
        const isDone   = i < n;
        const isActive = i === n;
        dot.className = 'step-dot' + (isDone ? ' done' : isActive ? ' active' : '');
        dot.textContent = isDone ? '✓' : i;
        if (slNum)   { slNum.className = 'step-left-num' + (isDone ? ' done' : isActive ? ' active' : ''); slNum.textContent = isDone ? '✓' : i; }
        if (slLabel) { slLabel.className = 'step-left-label' + (isDone ? ' done' : isActive ? ' active' : ''); }
      });
      [1, 2].forEach(i => {
        const line = document.getElementById(`sl-line-${i}`);
        if (line) line.className = 'step-line' + (n > i ? ' done' : '');
      });
    },

    selectPlan(el) {
      document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');
      this.selectedPlan = el.dataset.plan;
    },

    checkDocType(val) {
      document.getElementById('doc-warn').style.display = (val && val !== 'cni_ue') ? 'block' : 'none';
      document.getElementById('doc-ok').style.display   = (val === 'cni_ue') ? 'block' : 'none';
    },

    checkStrength(val) {
      const bar   = document.getElementById('strength-fill');
      const label = document.getElementById('strength-label');
      if (!bar || !label) return;
      let score = 0;
      if (val.length >= 8)          score++;
      if (/[A-Z]/.test(val))        score++;
      if (/[0-9]/.test(val))        score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const levels = [
        { pct: '10%',  color: 'var(--red)',      txt: 'Très faible' },
        { pct: '30%',  color: '#F97316',          txt: 'Faible' },
        { pct: '60%',  color: '#EAB308',          txt: 'Moyen' },
        { pct: '85%',  color: '#22C55E',          txt: 'Fort' },
        { pct: '100%', color: 'var(--green-ok)',  txt: 'Très fort' },
      ];
      const lvl = levels[Math.min(score, 4)];
      bar.style.width = val ? lvl.pct : '0%';
      bar.style.background = lvl.color;
      label.textContent = val ? lvl.txt : 'Saisissez un mot de passe';
      label.style.color = val ? lvl.color : 'var(--g400)';
    }
  }
};
</script>
</body>
</html>
