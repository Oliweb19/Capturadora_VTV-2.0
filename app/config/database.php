<?php
require_once __DIR__ . '/config.php';

/**
 * Retorna una instancia PDO (singleton).
 * Usa variable estática para reutilizar la conexión en la misma petición.
 */
function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En producción no exponer detalles del error
            http_response_code(500);
            die('Error de conexión a la base de datos.');
        }
    }

    return $pdo;
}
