<?php
/**
 * ════════════════════════════════════════════════════════════════
 *  GTB BANK — Connexion à la base de données (PDO singleton)
 * ════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';

final class DB
{
    private static ?PDO $pdo = null;

    /**
     * Retourne l'instance unique PDO (lazy).
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, "
                  . "time_zone = '+01:00', "
                  . "sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'",
            ];

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (GTB_DEBUG) {
                    die('Erreur de connexion DB : ' . $e->getMessage());
                }
                error_log('[DB] ' . $e->getMessage());
                http_response_code(503);
                die('Service momentanément indisponible.');
            }
        }

        return self::$pdo;
    }

    /** Exécute une requête préparée et retourne le statement. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Première ligne ou null. */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** Toutes les lignes. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Première colonne de la première ligne. */
    public static function scalar(string $sql, array $params = []): mixed
    {
        $stmt = self::run($sql, $params);
        $val  = $stmt->fetchColumn();
        return $val === false ? null : $val;
    }

    /** INSERT et retourne lastInsertId (cast en int). */
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    /** Helper INSERT par tableau associatif. */
    public static function insertInto(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`,`', $cols),
            implode(',', array_map(fn($c) => ':' . $c, $cols))
        );
        return self::insert($sql, $data);
    }

    /** UPDATE et retourne le nombre de lignes affectées. */
    public static function update(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    /** Transactions */
    public static function begin(): void   { self::pdo()->beginTransaction(); }
    public static function commit(): void  { self::pdo()->commit(); }
    public static function rollback(): void
    {
        if (self::pdo()->inTransaction()) self::pdo()->rollBack();
    }

    /** Exécute une closure dans une transaction. */
    public static function transaction(callable $fn): mixed
    {
        self::begin();
        try {
            $result = $fn();
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            self::rollback();
            throw $e;
        }
    }
}