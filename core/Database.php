<?php
// TravelVista - database access. One PDO connection per request, reused by the
// run/all/one/value/insert helpers below. All of them use prepared statements.

class Database
{
    private static $pdo = null;

    // Open the connection on first use, then return the same object.
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
                 . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                self::fail($e);
            }
        }
        return self::$pdo;
    }

    // Run a statement with bound parameters and return it (for writes or rowCount()).
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    // All matching rows.
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    // The first matching row, or null.
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    // The first column of the first row (for COUNT(*) and similar).
    public static function value(string $sql, array $params = [])
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    // Run an INSERT and return the new row id.
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    // Run several writes as one transaction.
    public static function transaction(callable $work)
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $work($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Show a readable setup message instead of a stack trace.
    private static function fail(PDOException $e): void
    {
        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        $css = (defined('BASE_URL') ? BASE_URL : '') . '/public/css/global.css';
        echo '<!doctype html><meta charset="utf-8">'
           . '<title>Database unavailable</title>'
           . '<link rel="stylesheet" href="' . e($css) . '">'
           . '<div class="db-down">'
           . '<h1>Database unavailable</h1>'
           . '<p>TravelVista could not reach MySQL at <code>' . e(DB_HOST . ':' . DB_PORT)
           . '</code> using the database <code>' . e(DB_NAME) . '</code>.</p>'
           . '<p>Start MySQL, then import <code>database/schema.sql</code> and '
           . '<code>database/seed.sql</code>. Connection details live in '
           . '<code>config/config.php</code>.</p>'
           . '<p class="db-down__detail">Server said: ' . e($e->getMessage()) . '</p>'
           . '</div>';
        exit;
    }
}
