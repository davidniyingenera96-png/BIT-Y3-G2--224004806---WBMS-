<?php
class User extends Model {
    static function byLogin(string $id): ?array { return self::q('SELECT user_id,username,role,password_hash,is_active FROM users WHERE username=? OR email=? LIMIT 1', [$id, $id])->fetch() ?: null; }
    /** Customer self-registration. If staff already registered the meter, the name must match and the login is linked to that record;
     *  otherwise a new customer profile is created. One transaction. Returns error list. */
    static function registerCustomer(array $d): array {
        if (self::q('SELECT 1 FROM users WHERE username=? OR email=?', [$d['username'], $d['email']])->fetch()) return ['Username or email is already registered.'];
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $c = self::q('SELECT customer_id, full_name, user_id, is_active FROM customers WHERE meter_no=? FOR UPDATE', [$d['meter_no']])->fetch();
            if ($c && ($c['user_id'] !== null || !$c['is_active'])) { $pdo->rollBack(); return ['This meter number already has an online account or is inactive. Please contact the office.']; }
            if ($c && strcasecmp(trim($c['full_name']), $d['full_name']) !== 0) { $pdo->rollBack(); return ['The name does not match our records for this meter number. Please contact the office.']; }
            self::q("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,'customer')", [$d['username'], $d['email'], password_hash($d['password'], PASSWORD_DEFAULT)]);
            $uid = (int)$pdo->lastInsertId();
            if ($c) self::q('UPDATE customers SET user_id=? WHERE customer_id=?', [$uid, $c['customer_id']]);
            else self::q('INSERT INTO customers (user_id,full_name,meter_no,address,phone) VALUES (?,?,?,?,?)', [$uid, $d['full_name'], $d['meter_no'], $d['address'], $d['phone'] ?: null]);
            $pdo->commit(); return [];
        } catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); return ['Registration failed (the meter number may already be in use). Please try again.']; }
    }
    static function all(string $role = '', string $q = ''): array {
        $w = []; $p = [];
        if (in_array($role, ['admin', 'staff', 'customer'], true)) { $w[] = 'role=?'; $p[] = $role; }
        if ($q !== '') { $l = '%' . addcslashes($q, '%_\\') . '%'; $w[] = '(username LIKE ? OR email LIKE ?)'; array_push($p, $l, $l); }
        return self::q('SELECT user_id,username,email,role,is_active,created_at FROM users' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY role, username LIMIT 200', $p)->fetchAll();
    }
    static function find(int $id): ?array { return self::q('SELECT user_id,username,email,role,is_active FROM users WHERE user_id=?', [$id])->fetch() ?: null; }
    static function strong(string $pw): bool { return strlen($pw) >= 8 && preg_match('/\d/', $pw) && preg_match('/[A-Za-z]/', $pw); }
    /** Admin form validation. Customer accounts keep the customer role; others may be admin/staff. Returns [errors, data]. */
    static function validate(array $in, ?int $id): array {
        $cur = $id ? self::find($id) : null;
        $d = ['username' => trim($in['username'] ?? ''), 'email' => trim($in['email'] ?? ''), 'password' => (string)($in['password'] ?? ''),
              'role' => ($cur && $cur['role'] === 'customer') ? 'customer' : (string)($in['role'] ?? '')]; $e = [];
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $d['username'])) $e[] = 'Username: 3-50 letters, numbers, . _ -';
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || strlen($d['email']) > 120) $e[] = 'Enter a valid email address.';
        if (!in_array($d['role'], ['admin', 'staff', 'customer'], true)) $e[] = 'Choose a role.';
        if (($id === null || $d['password'] !== '') && !self::strong($d['password'])) $e[] = 'Password needs 8+ characters with letters and numbers.';
        if (!$e && self::q('SELECT 1 FROM users WHERE (username=? OR email=?) AND user_id<>?', [$d['username'], $d['email'], $id ?? 0])->fetch()) $e[] = 'Username or email already in use.';
        return [$e, $d];
    }
    static function create(array $d): int { self::q('INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,?)', [$d['username'], $d['email'], password_hash($d['password'], PASSWORD_DEFAULT), $d['role']]); return (int)db()->lastInsertId(); }
    static function update(int $id, array $d): void {
        self::q('UPDATE users SET username=?,email=?,role=? WHERE user_id=?', [$d['username'], $d['email'], $d['role'], $id]);
        if ($d['password'] !== '') self::q('UPDATE users SET password_hash=? WHERE user_id=?', [password_hash($d['password'], PASSWORD_DEFAULT), $id]);
    }
    static function setActive(int $id, int $a): void { self::q('UPDATE users SET is_active=? WHERE user_id=?', [$a, $id]); }
    /** Delete a customer login only when it has no protected activity; the customer profile is kept via ON DELETE SET NULL. */
    static function deleteCustomerAccount(int $id): ?string {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $u = self::q('SELECT username,role FROM users WHERE user_id=? FOR UPDATE', [$id])->fetch();
            if (!$u) { $pdo->rollBack(); return 'Account not found.'; }
            if ($u['role'] !== 'customer') { $pdo->rollBack(); return 'Only customer accounts can be deleted here.'; }
            if ((int)self::scalar('SELECT (SELECT COUNT(*) FROM meter_readings WHERE reader_id=?) + (SELECT COUNT(*) FROM payments WHERE recorded_by=?)', [$id, $id]) > 0) {
                $pdo->rollBack(); return 'This account has linked readings or payments. Deactivate it instead.';
            }
            audit('CUSTOMER_ACCOUNT_DELETED', 'user', $id, $u['username']);
            self::q("DELETE FROM users WHERE user_id=? AND role='customer'", [$id]);
            $pdo->commit();
            return null;
        } catch (PDOException $x) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($x->getMessage());
            return 'Could not delete this account. Deactivate it instead or try again.';
        }
    }
    static function changePassword(int $id, string $cur, string $new, string $conf): ?string {
        $h = self::scalar('SELECT password_hash FROM users WHERE user_id=?', [$id]);
        if (!$h || !password_verify($cur, $h)) return 'Current password is incorrect.';
        if ($new !== $conf) return 'New passwords do not match.';
        if (!self::strong($new)) return 'Password needs 8+ characters with letters and numbers.';
        self::q('UPDATE users SET password_hash=? WHERE user_id=?', [password_hash($new, PASSWORD_DEFAULT), $id]); return null;
    }
    static function setEmail(int $id, string $email): ?string {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) return 'Enter a valid email address.';
        if (self::q('SELECT 1 FROM users WHERE email=? AND user_id<>?', [$email, $id])->fetch()) return 'That email is already in use.';
        self::q('UPDATE users SET email=? WHERE user_id=?', [$email, $id]); return null;
    }
}
