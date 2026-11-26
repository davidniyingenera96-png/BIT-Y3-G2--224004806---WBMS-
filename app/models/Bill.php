<?php
class Bill extends Model {
    static function settings(): array { return self::q('SELECT * FROM billing_settings ORDER BY setting_id DESC LIMIT 1')->fetch() ?: []; }
    static function list(?int $cid = null, string $status = ''): array {
        $w = []; $p = [];
        if ($cid) { $w[] = 'b.customer_id=?'; $p[] = $cid; }
        if (in_array($status, ['Unpaid', 'Partially Paid', 'Paid', 'Cancelled'], true)) { $w[] = 'b.status=?'; $p[] = $status; }
        return self::q('SELECT b.*,c.full_name,c.meter_no FROM bills b JOIN customers c USING(customer_id)' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY b.billing_month DESC, b.bill_id DESC LIMIT 200', $p)->fetchAll();
    }
    static function find(int $id): ?array { return self::q('SELECT b.*,c.full_name,c.meter_no,c.address,c.user_id FROM bills b JOIN customers c USING(customer_id) WHERE b.bill_id=?', [$id])->fetch() ?: null; }
    static function payments(int $billId): array { return self::q('SELECT p.*,u.username FROM payments p JOIN users u ON u.user_id=p.recorded_by WHERE p.bill_id=? ORDER BY p.payment_id', [$billId])->fetchAll(); }
    static function paymentsForCustomer(int $cid): array { return self::q("SELECT p.* FROM payments p JOIN bills b USING(bill_id) WHERE b.customer_id=? AND p.status='Completed' ORDER BY p.payment_date DESC, p.payment_id DESC LIMIT 10", [$cid])->fetchAll(); }

    /** Usage = reading in month minus previous accepted reading; total = (usage*rate + fixed) + tax%. Rates come from admin-set billing_settings. Returns [errors, billId]. */
    static function generate(int $cid, string $month, int $by): array {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) return [['Billing month must be YYYY-MM.'], null];
        $s = self::settings(); if (!$s) return [['Billing settings are not configured.'], null];
        $r = self::q("SELECT * FROM meter_readings WHERE customer_id=? AND status='Accepted' AND reading_date BETWEEN ? AND LAST_DAY(?) ORDER BY reading_date DESC, reading_id DESC LIMIT 1", [$cid, "$month-01", "$month-01"])->fetch();
        if (!$r) return [["No accepted reading for this customer in $month."], null];
        $p = self::q("SELECT meter_value FROM meter_readings WHERE customer_id=? AND status='Accepted' AND (reading_date<? OR (reading_date=? AND reading_id<?)) ORDER BY reading_date DESC, reading_id DESC LIMIT 1", [$cid, $r['reading_date'], $r['reading_date'], $r['reading_id']])->fetchColumn();
        $prev = $p === false ? (float)$r['meter_value'] : (float)$p; $units = (float)$r['meter_value'] - $prev;
        if ($units < 0) return [['Negative usage detected; correct the reading first.'], null];
        $sub = round($units * (float)$s['unit_rate'], 2) + (float)$s['fixed_charge'];
        $tax = round($sub * (float)$s['tax_rate'] / 100, 2); $total = $sub + $tax;
        $due = date('Y-m-d', strtotime("$month-01 +1 month -1 day +" . (int)$s['due_days'] . ' days'));
        try {
            self::q('INSERT INTO bills (customer_id,reading_id,billing_month,previous_reading,current_reading,units_used,unit_rate,fixed_charge,tax_amount,total_amount,due_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [$cid, $r['reading_id'], $month, $prev, $r['meter_value'], $units, $s['unit_rate'], $s['fixed_charge'], $tax, $total, $due]);
            $id = (int)db()->lastInsertId(); audit('BILL_GENERATED', 'bill', $id, "$month total $total"); return [[], $id];
        } catch (PDOException $x) {
            if (($x->errorInfo[1] ?? 0) === 1062) return [["A bill already exists for this customer and $month."], null];
            error_log($x->getMessage()); return [['Could not generate bill.'], null];
        }
    }
    /** Record a payment in one transaction; rejects overpayment; recalculates status. */
    static function pay(int $billId, string $amt, string $date, string $method, string $ref, int $by): array {
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt || $dt->format('Y-m-d') !== $date || $date > date('Y-m-d')) return ['Enter a valid payment date (not in the future).'];
        if (!in_array($method, ['Cash', 'Mobile Money', 'Bank Transfer', 'Card'], true)) return ['Choose a payment method.'];
        if (!is_numeric($amt) || (float)$amt <= 0) return ['Amount must be greater than zero.'];
        if (mb_strlen($ref) > 60) return ['Reference is too long.'];
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $b = self::q('SELECT * FROM bills WHERE bill_id=? FOR UPDATE', [$billId])->fetch();
            if (!$b || $b['status'] === 'Cancelled') { $pdo->rollBack(); return ['Bill not found or cancelled.']; }
            $bal = round((float)$b['total_amount'] - (float)$b['amount_paid'], 2); $a = round((float)$amt, 2);
            if ($a > $bal) { $pdo->rollBack(); return ['Overpayment rejected. Outstanding balance is ' . number_format($bal, 2) . '.']; }
            self::q('INSERT INTO payments (bill_id,amount_paid,payment_date,payment_method,transaction_reference,recorded_by) VALUES (?,?,?,?,?,?)', [$billId, $a, $date, $method, $ref !== '' ? $ref : null, $by]);
            $pid = (int)$pdo->lastInsertId(); self::recalc($billId); $pdo->commit(); audit('PAYMENT_RECORDED', 'payment', $pid, "bill $billId amount $a"); return [];
        } catch (PDOException $x) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return [($x->errorInfo[1] ?? 0) === 1062 ? 'Transaction reference already used.' : 'Could not record payment.'];
        }
    }
    /** Controlled reversal: payments are voided, never deleted. */
    static function voidPayment(int $pid): bool {
        $pdo = db(); $pdo->beginTransaction();
        $p = self::q("SELECT * FROM payments WHERE payment_id=? AND status='Completed' FOR UPDATE", [$pid])->fetch();
        if (!$p) { $pdo->rollBack(); return false; }
        self::q("UPDATE payments SET status='Voided' WHERE payment_id=?", [$pid]); self::recalc((int)$p['bill_id']); $pdo->commit();
        audit('PAYMENT_VOIDED', 'payment', $pid, 'bill ' . $p['bill_id']); return true;
    }
    private static function recalc(int $billId): void {
        $paid = round((float)self::scalar("SELECT COALESCE(SUM(amount_paid),0) FROM payments WHERE bill_id=? AND status='Completed'", [$billId]), 2);
        $t = round((float)self::scalar('SELECT total_amount FROM bills WHERE bill_id=?', [$billId]), 2);
        self::q('UPDATE bills SET amount_paid=?, status=? WHERE bill_id=?', [$paid, $paid >= $t ? 'Paid' : ($paid > 0 ? 'Partially Paid' : 'Unpaid'), $billId]);
    }

    static function paymentList(array $f): array {
        $w = []; $p = [];
        if (($f['q'] ?? '') !== '') { $l = '%' . addcslashes($f['q'], '%_\\') . '%'; $w[] = '(c.full_name LIKE ? OR c.meter_no LIKE ? OR p.transaction_reference LIKE ?)'; array_push($p, $l, $l, $l); }
        if (in_array($f['method'] ?? '', ['Cash', 'Mobile Money', 'Bank Transfer', 'Card'], true)) { $w[] = 'p.payment_method=?'; $p[] = $f['method']; }
        if (in_array($f['status'] ?? '', ['Completed', 'Pending', 'Rejected', 'Voided'], true)) { $w[] = 'p.status=?'; $p[] = $f['status']; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['from'] ?? '')) { $w[] = 'p.payment_date>=?'; $p[] = $f['from']; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['to'] ?? '')) { $w[] = 'p.payment_date<=?'; $p[] = $f['to']; }
        return self::q('SELECT p.*,b.billing_month,c.full_name,c.meter_no,u.username FROM payments p JOIN bills b USING(bill_id) JOIN customers c ON c.customer_id=b.customer_id JOIN users u ON u.user_id=p.recorded_by' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . " ORDER BY (p.status='Pending') DESC, p.payment_date DESC, p.payment_id DESC LIMIT 200", $p)->fetchAll();
    }
    static function paymentsAll(int $cid): array { return self::q('SELECT p.*,b.billing_month FROM payments p JOIN bills b USING(bill_id) WHERE b.customer_id=? ORDER BY p.payment_id DESC LIMIT 100', [$cid])->fetchAll(); }
    static function pendingCount(): int { return (int)self::scalar("SELECT COUNT(*) FROM payments WHERE status='Pending'"); }
    static function monthly(): array {
        return self::q("SELECT billing_month, COUNT(*) n, SUM(total_amount) billed, SUM(amount_paid) collected, SUM(total_amount-amount_paid) outstanding, SUM(status='Paid') paid_n, SUM(status IN ('Unpaid','Partially Paid')) open_n FROM bills WHERE status<>'Cancelled' GROUP BY billing_month ORDER BY billing_month DESC LIMIT 24")->fetchAll();
    }
    /** Controlled cancellation: only bills without completed payments (void payments first). */
    static function cancel(int $id): ?string {
        $b = self::find($id); if (!$b) return 'Bill not found.';
        if ($b['status'] === 'Cancelled') return 'Bill is already cancelled.';
        if ((float)$b['amount_paid'] > 0) return 'This bill has payments. Void them first, then cancel.';
        self::q("UPDATE bills SET status='Cancelled' WHERE bill_id=?", [$id]); audit('BILL_CANCELLED', 'bill', $id, $b['billing_month']); return null;
    }
    /** Customer-submitted payment: stored as Pending, no effect on the balance until staff/admin confirm it. */
    static function submit(int $billId, int $cid, string $amt, string $method, string $ref, int $uid): array {
        if (!in_array($method, ['Mobile Money', 'Bank Transfer', 'Card'], true)) return ['Choose a payment method.'];
        if (!is_numeric($amt) || (float)$amt <= 0) return ['Amount must be greater than zero.'];
        if (strlen($ref) < 3 || strlen($ref) > 60) return ['Enter the transaction reference (3-60 characters).'];
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $b = self::q('SELECT * FROM bills WHERE bill_id=? AND customer_id=? FOR UPDATE', [$billId, $cid])->fetch();
            if (!$b || !in_array($b['status'], ['Unpaid', 'Partially Paid'], true)) { $pdo->rollBack(); return ['This bill cannot be paid.']; }
            $pend = (float)self::scalar("SELECT COALESCE(SUM(amount_paid),0) FROM payments WHERE bill_id=? AND status='Pending'", [$billId]);
            $room = round((float)$b['total_amount'] - (float)$b['amount_paid'] - $pend, 2); $a = round((float)$amt, 2);
            if ($a > $room) { $pdo->rollBack(); return ['Amount exceeds the balance still payable (' . number_format(max($room, 0), 2) . ').']; }
            self::q("INSERT INTO payments (bill_id,amount_paid,payment_date,payment_method,transaction_reference,recorded_by,status) VALUES (?,?,CURDATE(),?,?,?,'Pending')", [$billId, $a, $method, $ref, $uid]);
            $pid = (int)$pdo->lastInsertId(); $pdo->commit(); audit('PAYMENT_SUBMITTED', 'payment', $pid, "bill $billId amount $a"); return [];
        } catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); return [($x->errorInfo[1] ?? 0) === 1062 ? 'That transaction reference was already used.' : 'Could not submit payment.']; }
    }
    static function confirm(int $pid): ?string {
        $pdo = db(); $pdo->beginTransaction();
        $p = self::q("SELECT * FROM payments WHERE payment_id=? AND status='Pending' FOR UPDATE", [$pid])->fetch();
        if (!$p) { $pdo->rollBack(); return 'Payment not found or already processed.'; }
        $b = self::q('SELECT * FROM bills WHERE bill_id=? FOR UPDATE', [$p['bill_id']])->fetch();
        if (!$b || $b['status'] === 'Cancelled' || round((float)$p['amount_paid'], 2) > round((float)$b['total_amount'] - (float)$b['amount_paid'], 2)) { $pdo->rollBack(); return 'Cannot confirm: bill is cancelled or the amount exceeds the balance.'; }
        self::q("UPDATE payments SET status='Completed' WHERE payment_id=?", [$pid]); self::recalc((int)$p['bill_id']); $pdo->commit();
        audit('PAYMENT_CONFIRMED', 'payment', $pid, 'bill ' . $p['bill_id']); return null;
    }
    static function reject(int $pid): bool { $n = self::q("UPDATE payments SET status='Rejected' WHERE payment_id=? AND status='Pending'", [$pid])->rowCount(); if ($n) audit('PAYMENT_REJECTED', 'payment', $pid); return $n > 0; }

    static function updateDue(int $id, string $date): ?string {
        $b = self::find($id); if (!$b) return 'Bill not found.'; if ($b['status'] === 'Cancelled') return 'Cancelled bills cannot be changed.';
        $dt = DateTime::createFromFormat('Y-m-d', $date); if (!$dt || $dt->format('Y-m-d') !== $date) return 'Enter a valid due date.';
        self::q('UPDATE bills SET due_date=? WHERE bill_id=?', [$date, $id]); audit('BILL_DUE_DATE_CHANGED', 'bill', $id, $b['due_date'] . ' -> ' . $date); return null;
    }
    static function findPayment(int $pid): ?array { return self::q('SELECT p.*,b.billing_month,c.full_name,c.meter_no FROM payments p JOIN bills b USING(bill_id) JOIN customers c ON c.customer_id=b.customer_id WHERE p.payment_id=?', [$pid])->fetch() ?: null; }
    /** Permitted correction of a completed payment: date, method and reference (amount changes need void + re-record). */
    static function editPayment(int $pid, string $date, string $method, string $ref): ?string {
        $p = self::findPayment($pid); if (!$p || $p['status'] !== 'Completed') return 'Only completed payments can be edited.';
        $dt = DateTime::createFromFormat('Y-m-d', $date); if (!$dt || $dt->format('Y-m-d') !== $date || $date > date('Y-m-d')) return 'Enter a valid payment date (not in the future).';
        if (!in_array($method, ['Cash', 'Mobile Money', 'Bank Transfer', 'Card'], true)) return 'Choose a payment method.';
        if (mb_strlen($ref) > 60) return 'Reference is too long.';
        try { self::q('UPDATE payments SET payment_date=?, payment_method=?, transaction_reference=? WHERE payment_id=?', [$date, $method, $ref !== '' ? $ref : null, $pid]); }
        catch (PDOException $x) { return ($x->errorInfo[1] ?? 0) === 1062 ? 'Transaction reference already used.' : 'Could not update payment.'; }
        audit('PAYMENT_EDITED', 'payment', $pid, 'bill ' . $p['bill_id']); return null;
    }
}
