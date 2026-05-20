<?php

// Obtenemos los valores de las variables de entorno (que configuraremos en Render)
// Si no existen (como en tu PC), usamos los valores por defecto que ya tenías
define('DB_HOST',     getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT') ?: '5432');
define('DB_NAME',     getenv('DB_NAME') ?: 'Detector');
define('DB_USER',     getenv('DB_USER') ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'Adwyack104');

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