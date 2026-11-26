<?php
class Reading extends Model {
    static function last(int $cid): ?array { return self::q("SELECT * FROM meter_readings WHERE customer_id=? AND status='Accepted' ORDER BY reading_date DESC, reading_id DESC LIMIT 1", [$cid])->fetch() ?: null; }
    static function customersWithLast(): array {
        return self::q("SELECT c.customer_id,c.full_name,c.meter_no,(SELECT meter_value FROM meter_readings r WHERE r.customer_id=c.customer_id AND r.status='Accepted' ORDER BY reading_date DESC, reading_id DESC LIMIT 1) AS last_value FROM customers c WHERE c.is_active=1 ORDER BY c.full_name")->fetchAll();
    }
    static function recent(int $n = 30, ?int $cid = null, string $from = '', string $to = ''): array {
        $w = []; $p = [];
        if ($cid) { $w[] = 'r.customer_id=?'; $p[] = $cid; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $w[] = 'r.reading_date>=?'; $p[] = $from; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) { $w[] = 'r.reading_date<=?'; $p[] = $to; }
        return self::q("SELECT r.*,c.full_name,c.meter_no,u.username reader,(SELECT MAX(x.reading_id) FROM meter_readings x WHERE x.customer_id=r.customer_id AND x.status='Accepted') latest_id,(SELECT COUNT(*) FROM bills bb WHERE bb.reading_id=r.reading_id) billed FROM meter_readings r JOIN customers c USING(customer_id) JOIN users u ON u.user_id=r.reader_id" . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY r.reading_date DESC, r.reading_id DESC LIMIT ' . (int)$n, $p)->fetchAll();
    }
    /** Cumulative reading rule: value must not be lower than previous accepted reading. Returns error list. */
    static function record(int $cid, string $date, string $value, int $by): array {
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt || $dt->format('Y-m-d') !== $date || $date > date('Y-m-d')) return ['Enter a valid reading date (not in the future).'];
        if (!is_numeric($value) || (float)$value < 0 || (float)$value > 9999999999) return ['Meter value must be a positive number.'];
        $pdo = db();
        try {
            $pdo->beginTransaction();
            if (!self::q('SELECT customer_id FROM customers WHERE customer_id=? AND is_active=1 FOR UPDATE', [$cid])->fetch()) { $pdo->rollBack(); return ['Customer not found.']; }
            $p = self::last($cid); $v = round((float)$value, 2);
            if ($p && $v < (float)$p['meter_value']) { $pdo->rollBack(); return ['Reading ' . $v . ' is lower than the previous accepted reading (' . $p['meter_value'] . '). A meter-reset requires an authorized correction.']; }
            if ($p && $date < $p['reading_date']) { $pdo->rollBack(); return ['Reading date is earlier than the previous reading (' . $p['reading_date'] . ').']; }
            self::q('INSERT INTO meter_readings (customer_id,reading_date,meter_value,units_used,reader_id) VALUES (?,?,?,?,?)', [$cid, $date, $v, $p ? $v - (float)$p['meter_value'] : 0, $by]);
            $id = (int)$pdo->lastInsertId(); $pdo->commit(); audit('READING_RECORDED', 'meter_reading', $id, "customer $cid value $v"); return [];
        } catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); return ['Could not save reading.']; }
    }

    static function forCustomer(int $cid, int $n = 24): array { return self::q('SELECT * FROM meter_readings WHERE customer_id=? ORDER BY reading_date DESC, reading_id DESC LIMIT ' . (int)$n, [$cid])->fetchAll(); }

    static function find(int $id): ?array { return self::q('SELECT r.*,c.full_name,c.meter_no FROM meter_readings r JOIN customers c USING(customer_id) WHERE r.reading_id=?', [$id])->fetch() ?: null; }
    /** Correctable/deletable only if it is the customer's latest accepted reading and no bill uses it (keeps usage chronology and financial records safe). */
    static function editable(array $r): bool {
        $l = self::last((int)$r['customer_id']);
        return $l && (int)$l['reading_id'] === (int)$r['reading_id'] && !(int)self::scalar('SELECT COUNT(*) FROM bills WHERE reading_id=?', [$r['reading_id']]);
    }
    static function update(int $id, string $date, string $value): array {
        $r = self::find($id); if (!$r) return ['Reading not found.'];
        if (!self::editable($r)) return ['Only the latest reading that has not been billed can be corrected.'];
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt || $dt->format('Y-m-d') !== $date || $date > date('Y-m-d')) return ['Enter a valid reading date (not in the future).'];
        if (!is_numeric($value) || (float)$value < 0 || (float)$value > 9999999999) return ['Meter value must be a positive number.'];
        $p = self::q("SELECT meter_value, reading_date FROM meter_readings WHERE customer_id=? AND status='Accepted' AND reading_id<? ORDER BY reading_date DESC, reading_id DESC LIMIT 1", [$r['customer_id'], $id])->fetch();
        $v = round((float)$value, 2);
        if ($p && $v < (float)$p['meter_value']) return ['Reading ' . $v . ' is lower than the previous accepted reading (' . $p['meter_value'] . ').'];
        if ($p && $date < $p['reading_date']) return ['Reading date is earlier than the previous reading (' . $p['reading_date'] . ').'];
        self::q('UPDATE meter_readings SET reading_date=?, meter_value=?, units_used=? WHERE reading_id=?', [$date, $v, $p ? $v - (float)$p['meter_value'] : 0, $id]);
        audit('READING_CORRECTED', 'meter_reading', $id, 'was ' . $r['meter_value'] . ' now ' . $v); return [];
    }
    static function delete(int $id): ?string {
        $r = self::find($id); if (!$r) return 'Reading not found.';
        if (!self::editable($r)) return 'Only the latest reading that has not been billed can be deleted.';
        self::q('DELETE FROM meter_readings WHERE reading_id=?', [$id]); audit('READING_DELETED', 'meter_reading', $id, $r['meter_no'] . ' value ' . $r['meter_value']); return null;
    }
}
