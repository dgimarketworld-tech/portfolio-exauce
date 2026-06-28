</main>

<!-- BOTTOM NAV -->
<nav class="gtb-bottom-nav">
  <a href="/dashboard/index.php" class="gtb-nav-item <?= ($navActive??'')==='home' ? 'active' : '' ?>">
    <div class="gtb-nav-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </div>
    <span class="gtb-nav-label">Accueil</span>
  </a>

  <a href="/dashboard/virement/index.php" class="gtb-nav-item <?= ($navActive??'')==='virement' ? 'active' : '' ?>">
    <div class="gtb-nav-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
      </svg>
    </div>
    <span class="gtb-nav-label">Virements</span>
  </a>

  <a href="/dashboard/virement/index.php" class="gtb-nav-center">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
    </svg>
  </a>

  <a href="/dashboard/cartes/index.php" class="gtb-nav-item <?= ($navActive??'')==='cartes' ? 'active' : '' ?>">
    <div class="gtb-nav-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
      </svg>
    </div>
    <span class="gtb-nav-label">Cartes</span>
  </a>

  <a href="/dashboard/transactions.php" class="gtb-nav-item <?= ($navActive??'')==='transactions' ? 'active' : '' ?>">
    <div class="gtb-nav-icon">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
    </div>
    <span class="gtb-nav-label">Historique</span>
  </a>
</nav>

<?php if (!empty($extraScript)) echo $extraScript; ?>
</body>
</html>
