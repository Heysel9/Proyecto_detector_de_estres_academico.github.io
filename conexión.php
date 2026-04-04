<?php

// =============================================
//  Configuración de conexión a PostgreSQL
// =============================================

define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'Detector');
define('DB_USER',     'postgres');   // Usuario por defecto de pgAdmin
define('DB_PASSWORD', 'canelita20');

// =============================================
//  Función para obtener la conexión
// =============================================

function conectarDB(): PDO {
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opciones);
        return $pdo;
    } catch (PDOException $e) {
        // En producción, evita mostrar el mensaje de error directamente
        die('Error de conexión: ' . $e->getMessage());
    }
}

// =============================================
//  Ejemplo de uso
// =============================================

try {
    $db = conectarDB();
    echo '✅ Conexión exitosa a la base de datos "' . DB_NAME . '"';
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage();
}