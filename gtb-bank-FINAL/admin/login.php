<?php
/**
 * GTB BANK — Admin Login
 * Redirige vers authentification/login.php (même page, role détecté automatiquement)
 */
header('Location: ../authentification/login.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;
