/**
 * GTB Icon Engine v1.0
 * Auto-remplace les emojis par des SVG Line Art premium
 * Stroke: 1.5px | ViewBox: 24×24 | Style: Lucide/Heroicons minimal
 *
 * NOUVELLES CLASSES D'ANIMATION CRÉÉES :
 *   .gtb-icon          — icône SVG de base (currentColor, 1.5px stroke)
 *   .gtb-icon-wrap     — conteneur avec micro-interaction hover
 *   .gtb-icon-bounce   — rebond léger au hover
 *   .gtb-icon-spin     — rotation au hover
 *   .gtb-icon-pulse    — battement continu
 *   .gtb-icon-shake    — secousse au hover (erreur / alerte)
 *   .gtb-icon-pop      — scale pop au hover
 *   .gtb-icon-glow     — halo doré au hover
 */

(function () {
  'use strict';

  /* ── SVG REGISTRY ────────────────────────────────────────────── */
  const ICONS = {
    /* Navigation & UI */
    '🏠': '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/>',
    '🏡': '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/><path d="M2 22h20"/>',
    '🏢': '<rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22V12h6v10"/><path d="M8 7h.01M12 7h.01M16 7h.01M8 11h.01M16 11h.01"/>',
    '🏦': '<path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>',
    '🏛': '<path d="M2 22h20M4 22V10M20 22V10M12 2 2 10h20z"/><path d="M8 22V14h8v8"/>',
    '🏥': '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/>',
    '🏧': '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 12h.01M12 12h.01M17 9v6"/>',
    '🏨': '<rect x="2" y="3" width="20" height="18" rx="1"/><path d="M2 9h20M12 3v18M7 15h.01M7 12h.01M17 15h.01M17 12h.01"/>',
    '🏪': '<path d="M2 7h20v15H2zM4 2h16l2 5H2z"/><path d="M8 22v-7h8v7"/>',
    /* Finance & Paiement */
    '💳': '<rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/><path d="M7 15h.01M11 15h4"/>',
    '💰': '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v12M9 8.5c0-1.38 1.34-2.5 3-2.5s3 1.12 3 2.5-1.34 2.5-3 2.5-3 1.12-3 2.5S10.66 18 12 18s3-1.12 3-2.5"/>',
    '💶': '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M8 12h8M8 15h5"/>',
    '💸': '<path d="M2 8h20M2 16h20"/><rect x="6" y="4" width="12" height="16" rx="1"/><circle cx="12" cy="12" r="2"/>',
    '💹': '<path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 4-4"/>',
    '💎': '<path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M12 3 6 9M12 3l6 6"/>',
    '💼': '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M12 12v.01"/>',
    /* Sécurité & Confiance */
    '🔒': '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    '🔐': '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><circle cx="12" cy="16" r="1"/>',
    '🔑': '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>',
    '🛡': '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    '⚠': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    '🚨': '<circle cx="12" cy="13" r="8"/><path d="M12 5V3M5 13H3M21 13h-2M6.3 7.3l-1.4-1.4M18.1 5.9l-1.4 1.4"/><path d="M12 9v4"/>',
    /* Communication */
    '📧': '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    '✉': '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    '📨': '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M12 20v-6"/>',
    '📬': '<path d="M2 7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7z"/><path d="M16 12h.01M12 12h.01M8 12h.01"/>',
    '📞': '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
    '💬': '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    '📣': '<path d="M18 8a4 4 0 0 0 0-8v8z"/><path d="M6 8H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2l2 4V4L6 8z"/>',
    /* Documents & Données */
    '📄': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
    '📑': '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7L15 2z"/><path d="M14 2v5h5M10 9h4M8 13h8M8 17h8"/>',
    '📝': '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
    '✏': '<path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
    '📋': '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/>',
    '📦': '<path d="M12 3 2 7.5l10 4.5 10-4.5z"/><path d="M2 7.5v9l10 4.5 10-4.5v-9"/><path d="M12 12v9M7 9.5l5-2 5 2"/>',
    /* Analytics & Charts */
    '📊': '<rect x="18" y="3" width="4" height="18"/><rect x="10" y="8" width="4" height="13"/><rect x="2" y="13" width="4" height="8"/>',
    '📈': '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
    '📉': '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>',
    '💹': '<path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 4-4"/>',
    /* Personnes */
    '👤': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    '👥': '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    '👶': '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/>',
    '👋': '<path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>',
    '👑': '<path d="M2 20h20M5 20V8l7-5 7 5v12"/><path d="M12 3v17"/>',
    '👁': '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
    '🤝': '<path d="m4 13 4 4M4 13l4-4 4.5 4.5M8 17l8-8m0 0-4-4m4 4 4-4"/>',
    '🎓': '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
    /* Localisation */
    '📍': '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    '🌍': '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    '🌐': '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    /* Transports */
    '🚗': '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="m16 8 4 2v5h-4V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    '🚘': '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="m16 8 4 2v5h-4V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    '🚇': '<rect x="4" y="2" width="16" height="16" rx="2"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><path d="M8 20l4-4 4 4M4 18v4M20 18v4"/><path d="M8 7h8"/>',
    '🚴': '<circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/><path d="M12 2a1 1 0 1 0 2 0 1 1 0 0 0-2 0"/><path d="M9 18 7 10M16 18 12 7 7 10M7 10h8"/>',
    '✈': '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 3 21 3s-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
    /* Tech & Devices */
    '📱': '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
    '🖥': '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
    '💾': '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
    '🖼': '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
    '🔧': '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
    '⚙': '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    '🔍': '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    '🔄': '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
    '🔋': '<rect x="2" y="7" width="16" height="10" rx="2"/><path d="M22 11v2M6 11v2"/>',
    /* Actions & Status */
    '✅': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    '✓': '<polyline points="20 6 9 17 4 12"/>',
    '✔': '<polyline points="20 6 9 17 4 12"/>',
    '❌': '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
    '✕': '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    '✗': '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    '❓': '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    '🔔': '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
    '🚀': '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m3.5 14.5 1.4 1.4M4.5 16.5l2.71-2.71M9.84 2.55 20.55 13.26a1 1 0 0 1 0 1.42l-3.12 3.12-9-9L11.55 5.7a1 1 0 0 0 0-1.42L9.84 2.55a1 1 0 0 0-1.41 0L6 5l3 3 1.41-1.41M12 13l1 1"/>',
    '🎯': '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
    '⭐': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    '★': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    '🏆': '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/>',
    '🎓': '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
    '💡': '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6M10 22h4"/>',
    '✨': '<path d="M12 3v1M3 12h1M20 12h1M12 20v1M5.6 5.6l.7.7M17.7 5.6l-.7.7M5.6 18.4l.7-.7M17.7 18.4l-.7-.7"/><circle cx="12" cy="12" r="4"/>',
    '🌟': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/><circle cx="12" cy="12" r="3"/>',
    '🔥': '<path d="M8.5 14.5A4.5 4.5 0 0 0 12 19a4.5 4.5 0 0 0 3.5-4.5c0-1-.5-2-1.5-3L12 9l-2 2.5c-1 1-1.5 2-1.5 3z"/><path d="M12 2s2 2 2 5-2 5-2 5-2-2-2-5 2-5 2-5z"/>',
    '💫': '<path d="M12 3v1M3 12h1M20 12h1M12 20v1"/><circle cx="12" cy="12" r="4"/><path d="m4.9 4.9.7.7M18.4 4.9l-.7.7M4.9 19.1l.7-.7M18.4 19.1l-.7-.7"/>',
    '⚡': '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
    '🎨': '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
    '🛒': '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
    '🗑': '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
    '⚖': '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"/><path d="M7 21h10M12 3v18M3 7h2c2 0 4-1 6-2 2 1 4 2 6 2h2"/>',
    '💚': '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    '❤': '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    '🍪': '<circle cx="12" cy="12" r="10"/><path d="M8.5 8.5v.01M16 15.5v.01M12 12v.01M11 17v.01M7 14v.01"/>',
    '🍽': '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
  };

  /* ── HELPERS ─────────────────────────────────────────────────── */
  /**
   * Crée un élément SVG avec les paths de l'icône.
   * @param {string} paths — contenu SVG interne
   * @param {string} label — aria-label accessible
   * @param {string} cls   — classes CSS supplémentaires
   */
  function makeSVG(paths, label, cls) {
    const ns = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('width', '1em');
    svg.setAttribute('height', '1em');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.5');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-label', label || '');
    svg.setAttribute('role', 'img');
    svg.setAttribute('class', 'gtb-icon' + (cls ? ' ' + cls : ''));
    svg.innerHTML = paths;
    return svg;
  }

  /**
   * Construit un regex correspondant à tous les emojis connus.
   */
  function buildPattern() {
    const keys = Object.keys(ICONS)
      .map(k => k.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
      .sort((a, b) => b.length - a.length); // longest first
    return new RegExp(keys.join('|'), 'g');
  }

  /**
   * Remplace les emojis dans un nœud texte en le splitant.
   */
  function replaceInTextNode(node, pattern) {
    const text = node.textContent;
    if (!pattern.test(text)) return;
    pattern.lastIndex = 0;

    const frag = document.createDocumentFragment();
    let last = 0, m;

    while ((m = pattern.exec(text)) !== null) {
      if (m.index > last) {
        frag.appendChild(document.createTextNode(text.slice(last, m.index)));
      }
      const emoji = m[0];
      const svg = makeSVG(ICONS[emoji], emoji);
      frag.appendChild(svg);
      last = pattern.lastIndex;
    }
    if (last < text.length) {
      frag.appendChild(document.createTextNode(text.slice(last)));
    }
    node.parentNode.replaceChild(frag, node);
  }

  /**
   * Parcourt le DOM de façon récursive.
   */
  function walk(node, pattern) {
    if (node.nodeType === Node.TEXT_NODE) {
      replaceInTextNode(node, pattern);
    } else if (
      node.nodeType === Node.ELEMENT_NODE &&
      !['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA', 'INPUT'].includes(node.tagName)
    ) {
      // Copier la liste des enfants (peut muter pendant walk)
      Array.from(node.childNodes).forEach(child => walk(child, pattern));
    }
  }

  /* ── MICRO-INTERACTIONS ──────────────────────────────────────── */
  /**
   * Ajoute la classe d'animation automatique selon le contexte parent.
   *
   * Règles :
   *  - Dans .feat-card, .saving-card, .step  → .gtb-icon-pop
   *  - Dans .hero                             → .gtb-icon-glow
   *  - Dans .btn, button, a                  → .gtb-icon-bounce
   *  - Dans .rate-badge, .pel-stat            → .gtb-icon-pulse
   *  - Dans ⚠, .alert                        → .gtb-icon-shake
   */
  function autoAnimate() {
    document.querySelectorAll('.gtb-icon').forEach(icon => {
      const el = icon.closest('.feat-card, .saving-card, .step, .rate-card');
      const inBtn = icon.closest('a, button, .btn');
      const inHero = icon.closest('.hero');
      const inBadge = icon.closest('.rate-badge, .pel-stat, .cat-stat');
      const label = icon.getAttribute('aria-label') || '';
      const isAlert = ['⚠', '🚨', '❌'].includes(label);

      if (isAlert) {
        icon.classList.add('gtb-icon-shake');
      } else if (inBtn) {
        icon.classList.add('gtb-icon-bounce');
      } else if (inBadge) {
        icon.classList.add('gtb-icon-pulse');
      } else if (inHero) {
        icon.classList.add('gtb-icon-glow');
      } else if (el) {
        icon.classList.add('gtb-icon-pop');
      }

      /* Wrap automatique pour hover parent */
      const parent = icon.parentElement;
      if (parent && !parent.classList.contains('gtb-icon-wrap')) {
        parent.classList.add('gtb-icon-wrap');
      }
    });
  }

  /* ── INIT ────────────────────────────────────────────────────── */
  function init() {
    const pattern = buildPattern();
    walk(document.body, pattern);
    autoAnimate();

    /* Mutation observer: gère les éléments ajoutés dynamiquement */
    const observer = new MutationObserver(mutations => {
      mutations.forEach(m => {
        m.addedNodes.forEach(node => {
          walk(node, buildPattern());
          autoAnimate();
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
