<?php
require_once __DIR__ . '/../../../backend/admin_required.php';
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { json_response(['success'=>true,'clients',[]]); }

$like = '%'.$q.'%';
$rows = DB::all(
    "SELECT u.id, u.first_name, u.last_name, u.email, u.status, u.plan, u.client_number,
            c.iban, c.bic, c.solde, c.devise
     FROM users u
     LEFT JOIN comptes c ON c.user_id = u.id AND c.statut='actif'
     WHERE u.role='user'
       AND (u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q
            OR u.client_number LIKE :q OR c.iban LIKE :q
            OR CONCAT(u.first_name,' ',u.last_name) LIKE :q)
     ORDER BY u.created_at DESC LIMIT 20",
    ['q' => $like]
);

require_once __DIR__ . '/../../../backend/iban.php';
$clients = array_map(function($r) {
    $iban_raw = $r['iban'] ?? '';
    return [
        'id'       => $r['id'],
        'name'     => trim(($r['first_name']??'').' '.($r['last_name']??'')),
        'email'    => $r['email'],
        'status'   => $r['status'],
        'plan'     => $r['plan'],
        'client_number' => $r['client_number'],
        'iban_raw' => $iban_raw,
        'iban_fmt' => $iban_raw ? IBAN::format($iban_raw) : '',
        'iban_mask'=> $iban_raw ? IBAN::mask($iban_raw)  : '',
        'bic'      => $r['bic'] ?? '',
        'solde'    => (float)($r['solde'] ?? 0),
        'devise'   => $r['devise'] ?? 'EUR',
        'initials' => strtoupper(substr($r['first_name']??'?',0,1).substr($r['last_name']??'?',0,1)),
    ];
}, $rows);

json_response(['success'=>true,'clients'=>$clients]);
