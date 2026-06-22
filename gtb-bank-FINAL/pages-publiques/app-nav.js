/**
 * GTB Pages Publiques — App Navigation Mobile
 * Remplace le hamburger par une bottom nav style app sur mobile.
 */
(function () {
  'use strict';

  function getBase() {
    var path = window.location.pathname;
    if (/\/pages-publiques\//.test(path)) return '';
    if (/\/produits-bancaires\//.test(path)) return '../pages-publiques/';
    return '';
  }

  var base = getBase();
  var authBase = base.replace('pages-publiques/', '') + 'authentification/';
  var prodBase = base.replace('pages-publiques/', '') + 'produits-bancaires/';

  var tabs = [
    { icon: '⌂',  label: 'Accueil',   href: base + 'index.html',              match: /pages-publiques\/index\.html|pages-publiques\/$/ },
    { icon: '⊟',  label: 'Produits',  href: prodBase + 'index.html',           match: /\/produits-bancaires\// },
    { icon: '❓',  label: 'FAQ',       href: base + 'faq.html',                 match: /faq\.html/ },
    { icon: '✉',  label: 'Contact',   href: base + 'contact.html',             match: /contact\.html/ },
    { icon: '🔐', label: 'Connexion', href: authBase + 'login.php',            match: /\/authentification\// }
  ];

  var currentPath = window.location.pathname;

  function buildNav() {
    if (document.querySelector('.gtb-pub-bottom-nav')) return;
    if (window.innerWidth > 768) return;

    var nav = document.createElement('nav');
    nav.className = 'gtb-pub-bottom-nav';
    var inner = document.createElement('div');
    inner.className = 'gtb-pub-bottom-nav__inner';

    tabs.forEach(function (tab) {
      var isActive = tab.match.test(currentPath);
      var a = document.createElement('a');
      a.className = 'gtb-pbn-item' + (isActive ? ' active' : '');
      a.href = tab.href;
      a.innerHTML =
        '<span class="gtb-pbn-icon">' + tab.icon + '</span>' +
        '<span class="gtb-pbn-label">' + tab.label + '</span>';
      inner.appendChild(a);
    });

    nav.appendChild(inner);
    document.body.appendChild(nav);

    // Padding pour ne pas couvrir le contenu
    document.body.style.paddingBottom = 'calc(68px + env(safe-area-inset-bottom))';
    // Cacher le footer sur mobile
    var footer = document.querySelector('.bnp-footer');
    if (footer) footer.style.paddingBottom = 'calc(68px + env(safe-area-inset-bottom))';
  }

  function hideHamburger() {
    if (window.innerWidth > 768) return;
    var ham = document.querySelector('.ham-btn');
    var mobileMenu = document.querySelector('.mobile-menu');
    if (ham) ham.style.display = 'none';
    // Le menu fullscreen n'est plus utile, on garde juste la bottom nav
    if (mobileMenu) mobileMenu.style.display = 'none';
    // Cacher le dernier bouton de la nav desktop (connexion) sur mobile
    var navActions = document.querySelector('.nav-actions');
    if (navActions) navActions.style.display = 'none';
  }

  function init() {
    hideHamburger();
    buildNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
