<?php

/**
 * Connection
 *
 * Responsabilidade única: estabelecer e fornecer a conexão PDO com o banco.
 * Não contém SQL nem regras de negócio.
 */
class Connection
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Impede instanciação direta.
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$instance;
    }
}
