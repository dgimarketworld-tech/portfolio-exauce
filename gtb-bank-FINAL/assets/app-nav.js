/**
 * GTB App Nav — Bottom Navigation Mobile
 * Injecte une barre de navigation bas d'écran style app native
 * sur les pages publiques en mode mobile (<768px)
 */
(function () {
  'use strict';

  var NAV_ITEMS = [
    { label: 'Accueil',   icon: 'home',    href: getRelPath('pages-publiques/index.html') },
    { label: 'Produits',  icon: 'grid',    href: getRelPath('produits-bancaires/index.html') },
    { label: 'Ouvrir',    icon: 'plus',    href: getRelPath('authentification/signup.php'), primary: true },
    { label: 'Connexion', icon: 'user',    href: getRelPath('authentification/login.php') },
    { label: 'Contact',   icon: 'message', href: getRelPath('pages-publiques/contact.html') },
  ];

  var ICONS = {
    home:    '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/>',
    grid:    '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    plus:    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
    user:    '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    message: '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
  };

  function getRelPath(target) {
    var path = window.location.pathname;
    var depth = (path.match(/\//g) || []).length - 1;
    var base = '';
    for (var i = 0; i < depth; i++) base += '../';
    return base + target;
  }

  function currentHref() {
    return window.location.pathname.replace(/\/$/, '/index.html');
  }

  function makeSVG(key) {
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (ICONS[key] || '') + '</svg>';
  }

  function injectStyles() {
    if (document.getElementById('gtb-app-nav-style')) return;
    var css = [
      '.gtb-app-nav{',
        'display:none;',
        'position:fixed;bottom:0;left:0;right:0;z-index:9000;',
        'background:rgba(5,11,20,.97);',
        'backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);',
        'border-top:1px solid rgba(255,255,255,.07);',
        'padding:0.35rem 0 calc(0.35rem + env(safe-area-inset-bottom));',
        'box-shadow:0 -8px 32px rgba(0,0,0,.25);',
      '}',
      '@media(max-width:768px){.gtb-app-nav{display:flex;justify-content:space-around;align-items:stretch;}}',
      '.gtb-app-nav-item{',
        'flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;',
        'gap:3px;padding:0.45rem 0;',
        'color:rgba(255,255,255,.4);text-decoration:none;',
        'font-size:0.6rem;font-weight:600;letter-spacing:0.04em;font-family:"DM Sans",sans-serif;',
        'transition:color 0.22s cubic-bezier(.25,.46,.45,.94),transform 0.28s cubic-bezier(.34,1.56,.64,1);',
        'user-select:none;-webkit-tap-highlight-color:transparent;',
        'position:relative;',
      '}',
      '.gtb-app-nav-item:active{transform:scale(0.88);}',
      '.gtb-app-nav-item.active{color:#D4AF37;}',
      '.gtb-app-nav-item.active svg{filter:drop-shadow(0 0 6px rgba(212,175,55,.5));}',
      '.gtb-app-nav-item--primary{',
        'color:#050B14 !important;',
      '}',
      '.gtb-app-nav-item--primary .gtb-app-nav-icon{',
        'background:linear-gradient(135deg,#D4AF37,#f5d778);',
        'border-radius:50%;padding:7px;',
        'box-shadow:0 8px 20px rgba(212,175,55,.4);',
        'margin-bottom:2px;',
      '}',
      '.gtb-app-nav-item--primary svg{stroke:#050B14;}',
      '.gtb-app-nav-icon{display:flex;align-items:center;justify-content:center;position:relative;}',
      /* badge de notification */
      '.gtb-app-nav-badge{',
        'position:absolute;top:-3px;right:-5px;',
        'background:#EF4444;color:#fff;',
        'font-size:0.5rem;font-weight:800;font-family:"Sora",sans-serif;',
        'min-width:14px;height:14px;border-radius:99px;',
        'display:flex;align-items:center;justify-content:center;padding:0 3px;',
        'border:2px solid rgba(5,11,20,.97);',
      '}',
      /* Pousser le contenu au-dessus de la nav */
      '@media(max-width:768px){body{padding-bottom:calc(68px + env(safe-area-inset-bottom));}}',
    ].join('');
    var style = document.createElement('style');
    style.id = 'gtb-app-nav-style';
    style.textContent = css;
    document.head.appendChild(style);
  }

  function buildNav() {
    var nav = document.createElement('nav');
    nav.className = 'gtb-app-nav';
    nav.setAttribute('aria-label', 'Navigation principale');

    var cur = currentHref();

    NAV_ITEMS.forEach(function (item) {
      var a = document.createElement('a');
      a.href = item.href;
      a.className = 'gtb-app-nav-item' + (item.primary ? ' gtb-app-nav-item--primary' : '');
      a.setAttribute('aria-label', item.label);

      /* Active state */
      if (!item.primary && cur.indexOf(item.href.replace('../', '').replace('../../', '')) !== -1) {
        a.classList.add('active');
      }

      var iconWrap = document.createElement('span');
      iconWrap.className = 'gtb-app-nav-icon';
      iconWrap.innerHTML = makeSVG(item.icon);
      a.appendChild(iconWrap);

      var label = document.createElement('span');
      label.textContent = item.label;
      a.appendChild(label);

      nav.appendChild(a);
    });

    return nav;
  }

  function init() {
    injectStyles();
    document.body.appendChild(buildNav());
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
