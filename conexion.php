<?php

define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'Detector');
define('DB_USER',     'postgres');
define('DB_PASSWORD', 'canelita20');

function conectarDB(): PDO {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASSWORD, $opciones);
    } catch (PDOException $e) {
        die('Error de conexión: ' . $e->getMessage());
    }
}