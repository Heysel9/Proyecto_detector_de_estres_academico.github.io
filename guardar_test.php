<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
require_once 'conexion.php'; 

try {
    $pdo = conectarDB(); 
    
    $id_usuario = $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 6; 

    // --- 1. PROCESAR CUESTIONARIO PSS-10 ---
    $puntos_pss = 0;
    
    // Corregido: Iteramos de 1 a 10 para coincidir con el estándar de nombres de inputs HTML comunes (pss_q1 a pss_q10)
    // Si tus inputs en el HTML realmente empiezan desde pss_q0, cambia el rango del for a (0; $i < 10; $i++)
    for ($i = 1; $i <= 10; $i++) {
        $campo = 'pss_q' . $i;
        $valor_input = $_POST[$campo] ?? $_REQUEST[$campo] ?? null;
        
        if ($valor_input !== null) {
            $valor = (int)$valor_input;
            
            // Invertir respuestas inversas reales (Preguntas 4, 5, 7 y 8 del test)
            if (in_array($i, [4, 5, 7, 8])) { 
                $valor = 4 - $valor; 
            }
            $puntos_pss += $valor;
        }
    }

    // --- 2. PROCESAR CUESTIONARIO DASS-21 ---
    $puntos_dass = 0;
    // Asumiendo que tus inputs van de dass_q1 a dass_q21
    for ($i = 1; $i <= 21; $i++) {
        $campo = 'dass_q' . $i;
        $valor_input = $_POST[$campo] ?? $_REQUEST[$campo] ?? null;
        
        // RESPALDO: Por si en tu HTML empezaban desde 0, busca también con base 0
        if ($valor_input === null) {
            $campo_cero = 'dass_q' . ($i - 1);
            $valor_input = $_POST[$campo_cero] ?? $_REQUEST[$campo_cero] ?? null;
        }

        if ($valor_input !== null) { 
            $puntos_dass += (int)$valor_input; 
        }
    }

    if ($puntos_pss === 0 && $puntos_dass === 0 && $_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.php?seccion=inicio");
        exit();
    }

    // --- CORRECCIÓN CLAVE: Convertir los puntos a PORCENTAJE (0-100) para evaluar la recomendación ---
    $porcentaje_pss = round(($puntos_pss / 40) * 100);
    if ($porcentaje_pss > 100) $porcentaje_pss = 100;
    $porcentaje_int = (int)$porcentaje_pss;

    // --- 3. BÚSQUEDA DE RECOMENDACIÓN BASADA EN EL PORCENTAJE REAL ---
    $sql_rec = 'SELECT "id_recomendacion" 
                FROM "recomendaciones" 
                WHERE :porcentaje BETWEEN "rango_min" AND "rango_max" 
                LIMIT 1';
    
    $stmt_rec = $pdo->prepare($sql_rec);
    $stmt_rec->execute([':porcentaje' => $porcentaje_int]); // Pasamos el porcentaje matemático, no los puntos brutos
    $recomendacion = $stmt_rec->fetch(PDO::FETCH_ASSOC);

    $id_recomendacion = $recomendacion ? (int)$recomendacion['id_recomendacion'] : null;
    
    // Determinación estática del nivel según el puntaje directo acumulado
    $nivel_pss = "Estrés Moderado";
    if ($puntos_pss >= 27) { 
        $nivel_pss = "Estrés Alto"; 
    } elseif ($puntos_pss <= 13) { 
        $nivel_pss = "Estrés Bajo"; 
    }

    // --- 4. ALMACENAMIENTO EN BASE DE DATOS (POSTGRESQL) ---
    
    // Registro del test PSS-10
    $sql1 = 'INSERT INTO "resultados_tests" ("id_usuario", "tipo_test", "puntaje_total", "nivel_detectado", "id_recomendacion", "fecha_realizacion") 
             VALUES (:id, \'pss10\', :p, :n, :id_rec, NOW())';
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([
        ':id'     => $id_usuario, 
        ':p'      => $puntos_pss, // Se guarda el puntaje acumulado real en la BD (0-40)
        ':n'      => $nivel_pss,
        ':id_rec' => $id_recomendacion
    ]);

    // Registro del test DASS-21
    $nivel_dass = ($puntos_dass > 30) ? "Nivel Elevado" : "Nivel Normal";
    $sql2 = 'INSERT INTO "resultados_tests" ("id_usuario", "tipo_test", "puntaje_total", "nivel_detectado", "fecha_realizacion") 
             VALUES (:id, \'dass21\', :p, :n, NOW())';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        ':id' => $id_usuario, 
        ':p'  => $puntos_dass, 
        ':n'  => $nivel_dass
    ]);

    // --- 5. REDIRECCIÓN EXITOSA ---
    header("Location: exito.php");
    exit();

} catch (PDOException $e) {
    echo "<h3>Falla de Base de Datos en SIBE:</h3>";
    die("Detalle técnico de PostgreSQL: " . $e->getMessage());
} catch (Exception $e) {
    die("Error general del sistema: " . $e->getMessage());
}