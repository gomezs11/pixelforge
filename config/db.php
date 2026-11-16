<?php
declare(strict_types=1);

/** Conexao PDO unica (singleton) usando credenciais do .env. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', 'localhost'), env('DB_PORT', '3306'), env('DB_NAME', '')
        );
        try {
            $pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('Falha ao conectar ao banco de dados. Verifique o arquivo .env.');
        }
    }
    return $pdo;
}
