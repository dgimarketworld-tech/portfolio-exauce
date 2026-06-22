<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Configuration globale
 *  Fichier centralisant toutes les constantes du projet.
 * ════════════════════════════════════════════════════════════════
 */

// ── ENVIRONNEMENT ───────────────────────────────────────────────
define('GTB_ENV', getenv('GTB_ENV') ?: 'development'); // development | production
define('GTB_DEBUG', GTB_ENV === 'development');

// ── ERREURS ─────────────────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', GTB_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// ── TIMEZONE ────────────────────────────────────────────────────
date_default_timezone_set('Europe/Paris');

// ── PROJET ──────────────────────────────────────────────────────
define('GTB_NAME',        'Global Trust Bank');
define('GTB_SHORT',       'GTB');
define('GTB_TAGLINE',     'La banque d\'un monde qui change');
define('GTB_BIC',         'GTBKFRPPXXX');
define('GTB_BANK_CODE',   '30004');  // 5 chiffres
define('GTB_BRANCH_CODE', '00001');  // 5 chiffres
define('GTB_COUNTRY',     'FR');
define('GTB_CURRENCY',    'EUR');

define('GTB_BASE_URL', (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://'
    . ($_SERVER['HTTP_HOST'] ?? 'localhost')
));

// ── BASE DE DONNÉES ─────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT')     ?: '3306');
define('DB_NAME',     getenv('DB_NAME')     ?: 'gtb');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

// ── SÉCURITÉ ────────────────────────────────────────────────────
// Clé secrète appli (utilisée pour HMAC, signatures, etc.)
// ⚠️ EN PRODUCTION : changer cette valeur et la stocker hors du repo
define('APP_SECRET', getenv('APP_SECRET') ?: 'CHANGE_ME_IN_PRODUCTION_a8f5f167f44f4964e6c998dee827110c');

// Sessions
define('SESSION_NAME',        'GTBSESSID');
define('SESSION_LIFETIME',    3600);   // 1h d'inactivité
define('SESSION_REMEMBER',    60 * 60 * 24 * 30); // 30 jours
define('SESSION_REGEN_EVERY', 300);    // Régénération ID toutes les 5 min

// CSRF
define('CSRF_TOKEN_NAME',  '_csrf');
define('CSRF_LIFETIME',    3600);

// Hash mots de passe
define('PASSWORD_ALGO',    PASSWORD_ARGON2ID);
define('PASSWORD_OPTIONS', [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 2,
]);

// Connexion — limites anti-brute force
define('LOGIN_MAX_ATTEMPTS',  5);
define('LOGIN_LOCK_MINUTES',  15);

// OTP
define('OTP_LENGTH',     6);
define('OTP_LIFETIME',   300);  // 5 minutes
define('OTP_MAX_TRIES',  5);

// Cookies
define('COOKIE_SECURE',   !GTB_DEBUG); // true en HTTPS prod
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');

// ── PLAFONDS BANCAIRES (par défaut) ─────────────────────────────
define('TRANSFER_LIMIT_DAILY',      10000.00);   // 10 000 €/jour
define('TRANSFER_LIMIT_MONTHLY',    50000.00);
define('TRANSFER_LIMIT_INSTANT',     5000.00);   // Plafond virement instantané
define('TRANSFER_FEE_SEPA',             0.00);
define('TRANSFER_FEE_INSTANT',          1.00);
define('TRANSFER_FEE_INTERNATIONAL',    8.00);

define('OVERDRAFT_DEFAULT',           500.00);

// ── EMAIL — Brevo ───────────────────────────────────────────────
define('MAIL_FROM',      'noreply@globaltrust-b.com');
define('MAIL_FROM_NAME', 'Global Trust Bank');
define('MAIL_SUPPORT',   'akp00965@gmail.com');
define('BREVO_API_KEY',  getenv('BREVO_API_KEY') ?: '');
if (BREVO_API_KEY === '') {
    error_log('[GTB] AVERTISSEMENT : BREVO_API_KEY non définie — les emails ne seront pas envoyés.');
}

// ── CHEMINS ─────────────────────────────────────────────────────
define('GTB_ROOT',     dirname(__DIR__));
define('UPLOAD_PATH',  GTB_ROOT . '/uploads');
define('LOG_PATH',     GTB_ROOT . '/logs');

// Création des dossiers nécessaires
foreach ([UPLOAD_PATH, UPLOAD_PATH . '/kyc', UPLOAD_PATH . '/avatars', LOG_PATH] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}
// ── ALIASES / COMPATIBILITÉ ─────────────────────────────────────
if (!defined('APP_ENV'))            define('APP_ENV',            GTB_ENV);
if (!defined('BASE_PATH'))          define('BASE_PATH',          '');
if (!defined('OTP_VALIDITY'))       define('OTP_VALIDITY',       OTP_LIFETIME);
if (!defined('LOGIN_THROTTLE_MAX')) define('LOGIN_THROTTLE_MAX', LOGIN_MAX_ATTEMPTS);
if (!defined('LOGIN_THROTTLE_WIN')) define('LOGIN_THROTTLE_WIN', LOGIN_LOCK_MINUTES * 60);
