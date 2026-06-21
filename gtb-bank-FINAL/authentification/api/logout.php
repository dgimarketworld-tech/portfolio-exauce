<?php
require_once __DIR__ . '/../../backend/config.php';
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/helpers.php';

Session::start();
Session::destroy();
redirect(GTB_BASE_URL . '/authentification/login.php?reason=logout');
