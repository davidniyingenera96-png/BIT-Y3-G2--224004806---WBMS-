<?php
abstract class Model {
    /** All SQL goes through PDO prepared statements. */
    public static function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
    public static function scalar(string $sql, array $p = []) { return self::q($sql, $p)->fetchColumn(); }
}
