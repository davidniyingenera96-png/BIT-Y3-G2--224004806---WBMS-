<?php
class Message extends Model {
    static function create(array $d): void { self::q('INSERT INTO contact_messages (name,email,subject,message,ip_address) VALUES (?,?,?,?,?)', [$d['name'], $d['email'], $d['subject'], $d['message'], $_SERVER['REMOTE_ADDR'] ?? null]); }
    static function recent(int $n = 100): array { return self::q('SELECT * FROM contact_messages ORDER BY message_id DESC LIMIT ' . (int)$n)->fetchAll(); }
    static function markAllRead(): void { self::q('UPDATE contact_messages SET is_read=1 WHERE is_read=0'); }

    static function delete(int $id): void { self::q('DELETE FROM contact_messages WHERE message_id=?', [$id]); audit('MESSAGE_DELETED', 'contact_message', $id); }
}
