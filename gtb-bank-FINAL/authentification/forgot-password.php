<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/session.php';
require_once __DIR__ . '/../backend/security.php';
Session::start();
$csrf = Security::csrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Mot de passe oublié — Global Trust Bank</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sora',sans-serif;background:#0D1B2A;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.logo{font-size:22px;font-weight:700;color:#0D1B2A;margin-bottom:8px}
.logo span{color:#D4AF37}
h1{font-size:20px;font-weight:600;color:#0D1B2A;margin-bottom:8px}
p.sub{color:#6C757D;font-size:14px;margin-bottom:32px}
label{display:block;font-size:13px;font-weight:500;color:#343A40;margin-bottom:6px}
input{width:100%;padding:12px 16px;border:1.5px solid #DEE2E6;border-radius:8px;font-size:15px;font-family:inherit;outline:none;transition:.2s}
input:focus{border-color:#D4AF37}
.btn{width:100%;padding:14px;background:#D4AF37;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;margin-top:20px;transition:.2s}
.btn:hover{background:#b8962e}
.btn:disabled{opacity:.6;cursor:not-allowed}
.msg{margin-top:16px;padding:12px 16px;border-radius:8px;font-size:14px;display:none}
.msg.success{background:#d1fae5;color:#065f46}
.msg.error{background:#fee2e2;color:#991b1b}
.back{display:block;text-align:center;margin-top:20px;color:#D4AF37;font-size:14px;text-decoration:none}
.back:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="card">
  <div class="logo">Global <span>Trust</span> Bank</div>
  <h1>Mot de passe oublié</h1>
  <p class="sub">Entrez votre email pour recevoir un code de réinitialisation.</p>
  <label for="email">Adresse email</label>
  <input type="email" id="email" placeholder="votre@email.com" autocomplete="email"/>
  <button class="btn" id="btn">Envoyer le code</button>
  <div class="msg" id="msg"></div>
  <a href="login.php" class="back">← Retour à la connexion</a>
</div>
<script>
document.getElementById('btn').addEventListener('click', async () => {
  const email = document.getElementById('email').value.trim();
  const btn   = document.getElementById('btn');
  const msg   = document.getElementById('msg');

  if (!email) { showMsg('Veuillez entrer votre email.', 'error'); return; }

  btn.disabled = true;
  btn.textContent = 'Envoi en cours...';
  msg.style.display = 'none';

  try {
    const res = await fetch('api/forgot_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, csrf_token: '<?= $csrf ?>' })
    });
    const data = await res.json();
    if (data.success) {
      showMsg('Si cet email existe, un code vous a été envoyé.', 'success');
    } else {
      showMsg(data.error || 'Une erreur est survenue.', 'error');
    }
  } catch(e) {
    showMsg('Erreur réseau. Vérifiez votre connexion.', 'error');
  }

  btn.disabled = false;
  btn.textContent = 'Envoyer le code';
});

function showMsg(text, type) {
  const msg = document.getElementById('msg');
  msg.textContent = text;
  msg.className = 'msg ' + type;
  msg.style.display = 'block';
}
</script>
<script src="../pages-publiques/app-nav.js"></script>
</body>
</html>
