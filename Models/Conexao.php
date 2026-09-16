<?php

// Abre a conexão com o banco uma vez só e devolve sempre a mesma.

class Conexao
{
    private static ?PDO $unica = null;

    private function __construct()
    {
    }

    public static function conectar(): PDO
    {
        if (self::$unica === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            self::$unica = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$unica;
    }
}
