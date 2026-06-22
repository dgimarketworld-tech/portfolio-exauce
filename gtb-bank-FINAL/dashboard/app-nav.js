/**
 * GTB Dashboard — App Navigation Mobile
 * Injecte la bottom nav style app sur toutes les pages dashboard.
 * Se charge après </head>, fonctionne sur tous les sous-dossiers.
 */
(function () {
  'use strict';

  /* ── Résolution du chemin racine dashboard ─────────────────── */
  function getBase() {
    var path = window.location.pathname;
    // Ex: /gtb-bank-FINAL/dashboard/virement/index.php → depth = 2
    var match = path.match(/\/dashboard(\/[^/]+)?\//);
    if (!match) return '';
    var sub = match[1]; // ex: "/virement" ou undefined
    return sub ? '../' : '';
  }

  var base = getBase();

  /* ── Config des onglets ───────────────────────────────────── */
  var tabs = [
    { icon: '⌂',  label: 'Accueil',    href: base + 'index.php',           match: /dashboard\/index\.php|dashboard\/$/ },
    { icon: '⊞',  label: 'Comptes',    href: base + 'comptes/index.php',    match: /\/comptes\// },
    { icon: '⇄',  label: 'Virement',   href: base + 'virement/index.php',   match: /\/virement\// },
    { icon: '▣',  label: 'Cartes',     href: base + 'cartes/index.php',     match: /\/cartes\// },
    { icon: '⋯',  label: 'Plus',       href: '#more',                       match: /^$/ }
  ];

  var currentPath = window.location.pathname;

  /* ── Injection bottom nav ─────────────────────────────────── */
  function buildNav() {
    if (document.querySelector('.gtb-app-bottom-nav')) return;

    var nav = document.createElement('nav');
    nav.className = 'gtb-app-bottom-nav';

    var inner = document.createElement('div');
    inner.className = 'gtb-app-bottom-nav__inner';

    tabs.forEach(function (tab, i) {
      var isActive = tab.match.test(currentPath);
      var a = document.createElement('a');
      a.className = 'gtb-abn-item' + (isActive ? ' active' : '');
      a.href = tab.href;
      if (tab.href === '#more') {
        a.addEventListener('click', function (e) {
          e.preventDefault();
          toggleSidebar();
        });
      }
      a.innerHTML =
        '<span class="gtb-abn-icon">' + tab.icon + '</span>' +
        '<span class="gtb-abn-label">' + tab.label + '</span>';
      // Indicateur point actif
      if (isActive) {
        var dot = document.createElement('span');
        dot.className = 'gtb-abn-dot';
        a.appendChild(dot);
      }
      inner.appendChild(a);
    });

    nav.appendChild(inner);
    document.body.appendChild(nav);
  }

  /* ── Sidebar toggle via bouton "Plus" ─────────────────────── */
  function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!sidebar) return;
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
  }

  /* ── Masquer le bouton hamburger desktop sur mobile ───────── */
  function patchTopbar() {
    // Le toggle sidebar existant reste pour les > 768px
    // Sur mobile on cache juste le texte du topbar
    var toggle = document.querySelector('.sidebar-toggle');
    if (toggle) toggle.style.display = 'none'; // bottom nav "Plus" gère l'ouverture
  }

  /* ── Padding content pour bottom nav ─────────────────────── */
  function addBodyPadding() {
    if (window.innerWidth > 768) return;
    var main = document.querySelector('.dash-main');
    if (main) {
      main.style.paddingBottom = 'calc(72px + env(safe-area-inset-bottom))';
    }
  }

  /* ── Init ─────────────────────────────────────────────────── */
  function init() {
    buildNav();
    patchTopbar();
    addBodyPadding();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.addEventListener('resize', addBodyPadding);
})();
