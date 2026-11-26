<?php // GET index.php?r=api/search&q=...  -> {"success":true,"data":[...]} (staff/admin only, limited fields)
class ApiController extends Controller {
    function search() {
        $u = user(); if (!$u) $this->json(401, ['success' => false, 'error' => 'Not authenticated']);
        if (!in_array($u['role'], ['admin', 'staff'], true)) $this->json(403, ['success' => false, 'error' => 'Forbidden']);
        $q = trim((string)($_GET['q'] ?? '')); if (mb_strlen($q) < 2 || mb_strlen($q) > 60) $this->json(422, ['success' => false, 'error' => 'Query must be 2-60 characters']);
        try { $this->json(200, ['success' => true, 'data' => array_map(fn($r) => ['customer_id' => (int)$r['customer_id'], 'full_name' => $r['full_name'], 'meter_no' => $r['meter_no'], 'address' => $r['address']], array_slice(Customer::all($q), 0, 20))]); }
        catch (PDOException $e) { error_log($e->getMessage()); $this->json(500, ['success' => false, 'error' => 'Server error']); }
    }
}
