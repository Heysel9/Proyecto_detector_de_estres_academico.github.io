<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start(); 
require_once 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = conectarDB(); 
        
        $id_usuario = $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 6; 

        // --- 1. PROCESAR PSS-10 ---
        $puntos_pss = 0;
        for ($i = 0; $i < 10; $i++) {
            $campo = 'pss_q' . $i;
            if (isset($_POST[$campo])) {
                $valor = (int)$_POST[$campo];
                // Invertir preguntas 4, 5, 7, y 8 (índices 3, 4, 6, 7)
                if (in_array($i, [3, 4, 6, 7])) { $valor = 4 - $valor; }
                $puntos_pss += $valor;
            }
        }

        // --- 2. PROCESAR DASS-21 ---
        $puntos_dass = 0;
        for ($i = 0; $i < 21; $i++) {
            $campo = 'dass_q' . $i;
            if (isset($_POST[$campo])) { $puntos_dass += (int)$_POST[$campo]; }
        }

        // --- 3. BUSCAR RECOMENDACIÓN INTELIGENTE ---
        // IMPORTANTE: Agregamos "categoria" al SELECT para que PHP la reconozca
        $sql_rec = 'SELECT "mensaje", "sugerencia_accion", "categoria" 
                    FROM "recomendaciones" 
                    WHERE :puntaje BETWEEN "rango_min" AND "rango_max" 
                    LIMIT 1';
        
        $stmt_rec = $pdo->prepare($sql_rec);
        $stmt_rec->execute([':puntaje' => $puntos_pss]);
        $recomendacion = $stmt_rec->fetch(PDO::FETCH_ASSOC);

        // Si no encuentra nada en la BD, ponemos valores de respaldo
        $nivel_pss = $recomendacion['categoria'] ?? "Evaluado";
        $mensaje_final = $recomendacion['mensaje'] ?? "Tu nivel de estrés ha sido registrado.";
        $accion_final = $recomendacion['sugerencia_accion'] ?? "Sigue monitoreando tu bienestar.";

        // --- 4. GUARDAR EN POSTGRESQL ---
        
        // Guardar PSS-10
        $sql1 = 'INSERT INTO "resultados_tests" ("id_usuario", "tipo_test", "puntaje_total", "nivel_detectado", "fecha_realizacion") 
                 VALUES (:id, \'pss10\', :p, :n, NOW())';
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id_usuario, ':p' => $puntos_pss, ':n' => $nivel_pss]);

        // Guardar DASS-21
        $nivel_dass = ($puntos_dass > 30) ? "Nivel Elevado" : "Nivel Normal";
        $sql2 = 'INSERT INTO "resultados_tests" ("id_usuario", "tipo_test", "puntaje_total", "nivel_detectado", "fecha_realizacion") 
                 VALUES (:id, \'dass21\', :p, :n, NOW())';
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':id' => $id_usuario, ':p' => $puntos_dass, ':n' => $nivel_dass]);

        // --- 5. REDIRIGIR CON LOS DATOS REALES ---
        $msg = urlencode($mensaje_final);
        $acc = urlencode($accion_final);

        header("Location: index.php?seccion=resultados&success=true&m=$msg&a=$acc");
        exit();

    } catch (Exception $e) {
        die("Error en SIBE: " . $e->getMessage());
    }
}