<?php
require_once __DIR__ . '/../../../backend/admin_required.php';
$count = (int) DB::scalar("SELECT COUNT(*) FROM transactions WHERE certification_status='running' AND admin_alerted=0") ?: 0;
$items = DB::all("SELECT t.id, t.montant, t.devise, t.cree_le, u.first_name, u.last_name, u.email
    FROM transactions t
    JOIN comptes c ON c.id=t.compte_id
    JOIN users u ON u.id=c.user_id
    WHERE t.certification_status='running' AND t.admin_alerted=0
    ORDER BY t.cree_le DESC LIMIT 10");
json_response(['success'=>true,'count'=>$count,'items'=>$items]);
