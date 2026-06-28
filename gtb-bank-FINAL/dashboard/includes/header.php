<?php
// Requires: $pageTitle, $currentUser, $notif_count, $navActive
$_initials = strtoupper(
    substr($currentUser['first_name'] ?? $currentUser['prenom'] ?? 'G', 0, 1) .
    substr($currentUser['last_name']  ?? $currentUser['nom']    ?? 'T', 0, 1)
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="stylesheet" href="/dashboard/assets/gtb-light.css">
<?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- TOPBAR -->
<header class="gtb-topbar">
  <a href="/dashboard/index.php" class="gtb-topbar-logo">
    <img src="/favicon.png" alt="GTB">
    <div class="gtb-topbar-logo-text">
      <strong>Global Trust Bank</strong>
      <span>Espace client</span>
    </div>
  </a>
  <div class="gtb-topbar-actions">
    <a href="/dashboard/notifications.php" class="gtb-topbar-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <?php if (($notif_count ?? 0) > 0): ?>
        <span class="gtb-topbar-badge"><?= min((int)$notif_count, 99) ?></span>
      <?php endif; ?>
    </a>
    <a href="/dashboard/profil/index.php" class="gtb-topbar-avatar"><?= $_initials ?></a>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="gtb-main">
