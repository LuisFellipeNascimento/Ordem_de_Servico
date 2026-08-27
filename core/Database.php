<?php

/**
 * Core/Database — conexão PDO única (Singleton).
 *
 * POR QUE SINGLETON? Abrir múltiplas conexões MySQL por requisição desperdiça
 * recursos. Garantimos UMA conexão por requisição reaproveitando a instância.
 * Com ERRMODE_EXCEPTION, qualquer erro de SQL vira exceção (mais fácil de
 * lidar/registrar do que warning silencioso).
 */
class Database {
    private static $instance = null;

    public static function getConnection() {
        if (!self::$instance) {
            // Lê as credenciais do config.php (host, dbname, user, password).
            $config = include(__DIR__ . '/../config/config.php');

            // charset=utf8 evita problemas de acentuação (ç, ã, etc.).
            self::$instance = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
                $config['user'],
                $config['password']
            );
            // Exceções em vez de avisos — facilita depuração e tratamento.
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instance;
    }
}
