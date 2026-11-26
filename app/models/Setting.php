<?php // Admin-editable branding & contact details (key/value), with safe defaults
class Setting extends Model {
    private static ?array $c = null;
    const DEFAULTS = ['site_name' => 'Water Bill Management System', 'tagline' => 'Clean Water, Better Life', 'logo' => '', 'email' => 'support@example.com', 'phone' => '+250 000 000 000', 'address' => 'Kigali, Rwanda', 'hours' => 'Mon – Fri, 08:00 – 17:00'];
    static function all(): array {
        if (self::$c === null) { try { self::$c = self::q('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR); } catch (Throwable $e) { self::$c = []; } }
        return self::$c + self::DEFAULTS;
    }
    static function save(array $kv): void {
        foreach ($kv as $k => $v) self::q('INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)', [$k, $v]);
        self::$c = null;
    }
}
