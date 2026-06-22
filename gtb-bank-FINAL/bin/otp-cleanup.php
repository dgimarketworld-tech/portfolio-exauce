<?php
/**
 * GTB BANK — Nettoyage des OTP expirés
 * Cron : 0 3 * * * php /var/www/gtb-bank-FINAL/bin/otp-cleanup.php
 */
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/security.php';

$n = Security::purgeExpiredOtp();
echo date('[Y-m-d H:i:s]') . " OTP cleanup : $n enregistrement(s) supprimé(s).\n";
