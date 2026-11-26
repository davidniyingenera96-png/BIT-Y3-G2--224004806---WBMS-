<?php
class Customer extends Model {
    static function all(string $q = '', int $active = 1): array {
        $l = '%' . addcslashes($q, '%_\\') . '%';
        return self::q('SELECT * FROM customers WHERE is_active=? AND (full_name LIKE ? OR meter_no LIKE ?) ORDER BY full_name LIMIT 100', [$active, $l, $l])->fetchAll();
    }
    static function find(int $id): ?array { return self::q('SELECT * FROM customers WHERE customer_id=?', [$id])->fetch() ?: null; }
    static function byUser(int $uid): ?array { return self::q('SELECT * FROM customers WHERE user_id=?', [$uid])->fetch() ?: null; }
    /** Returns [errors, cleanData]. */
    static function validate(array $in, ?int $id = null): array {
        $d = ['full_name' => trim($in['full_name'] ?? ''), 'meter_no' => strtoupper(trim($in['meter_no'] ?? '')), 'address' => trim($in['address'] ?? ''), 'phone' => trim($in['phone'] ?? '')]; $e = [];
        if (mb_strlen($d['full_name']) < 2 || mb_strlen($d['full_name']) > 120) $e[] = 'Full name must be 2-120 characters.';
        if (!preg_match('/^[A-Z0-9-]{3,30}$/', $d['meter_no'])) $e[] = 'Meter number: 3-30 letters, digits or hyphens.';
        if ($d['address'] === '' || mb_strlen($d['address']) > 255) $e[] = 'Address is required (max 255 characters).';
        if ($d['phone'] !== '' && !preg_match('/^\+?[0-9 ]{7,20}$/', $d['phone'])) $e[] = 'Phone number is invalid.';
        if (!$e && self::q('SELECT 1 FROM customers WHERE meter_no=? AND customer_id<>?', [$d['meter_no'], $id ?? 0])->fetch()) $e[] = 'Meter number already exists.';
        return [$e, $d];
    }
    static function create(array $d): int { self::q('INSERT INTO customers (full_name,meter_no,address,phone) VALUES (?,?,?,?)', [$d['full_name'], $d['meter_no'], $d['address'], $d['phone'] ?: null]); return (int)db()->lastInsertId(); }
    static function update(int $id, array $d): void { self::q('UPDATE customers SET full_name=?,meter_no=?,address=?,phone=? WHERE customer_id=?', [$d['full_name'], $d['meter_no'], $d['address'], $d['phone'] ?: null, $id]); }
    static function deactivate(int $id): void { self::q('UPDATE customers SET is_active=0 WHERE customer_id=?', [$id]); } // soft delete keeps financial history

    static function updateOwn(int $id, string $phone, string $address): array {
        $e = [];
        if ($phone !== '' && !preg_match('/^\+?[0-9 ]{7,20}$/', $phone)) $e[] = 'Phone number is invalid.';
        if ($address === '' || mb_strlen($address) > 255) $e[] = 'Address is required (max 255 characters).';
        if (!$e) self::q('UPDATE customers SET phone=?, address=? WHERE customer_id=?', [$phone ?: null, $address, $id]);
        return $e;
    }

    static function activate(int $id): void { self::q('UPDATE customers SET is_active=1 WHERE customer_id=?', [$id]); }
    /** Hard delete only when the customer has no readings or bills; otherwise deactivate (financial history is kept). */
    static function delete(int $id): ?string {
        $c = self::find($id); if (!$c) return 'Customer not found.';
        if ((int)self::scalar('SELECT (SELECT COUNT(*) FROM meter_readings WHERE customer_id=?) + (SELECT COUNT(*) FROM bills WHERE customer_id=?)', [$id, $id]) > 0) return 'This customer has readings or bills, so it cannot be deleted. Deactivate it instead (history is kept).';
        self::q('DELETE FROM customers WHERE customer_id=?', [$id]); audit('CUSTOMER_DELETED', 'customer', $id, $c['meter_no']); return null;
    }
}
