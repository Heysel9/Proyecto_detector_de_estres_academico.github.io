<?php
require_once 'conexion.php';
session_start();

// ─── Variables globales ────────────────────────────────────────────────────
$error_login    = '';
$error_registro = '';
$login_exitoso  = false;
$recovery_msg   = '';
$msg_estres     = '';
$error_estres   = '';

// ─── POST handler ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // LOGIN
    if (($_POST['action'] ?? '') === 'login') {
        $emailUsuario = trim($_POST['email_usuario'] ?? '');
        $contrasena   = $_POST['contrasena'] ?? '';

        if ($emailUsuario && $contrasena) {
            try {
                $db   = conectarDB();
                $stmt = $db->prepare(
                    "SELECT id, nombre, contrasena FROM usuarios
                     WHERE email = :eu OR usuario = :eu2 LIMIT 1"
                );
                $stmt->execute([':eu' => $emailUsuario, ':eu2' => $emailUsuario]);
                $usuario = $stmt->fetch();

                if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                    $_SESSION['usuario_id']     = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $login_exitoso = true;
                } else {
                    $error_login = 'Credenciales incorrectas. Inténtalo de nuevo.';
                }
            } catch (Exception $e) {
                $error_login = 'Error al conectar con la base de datos.';
            }
        } else {
            $error_login = 'Por favor completa todos los campos.';
        }
    }

    // REGISTRO
    if (($_POST['action'] ?? '') === 'registro') {
        $nombre     = trim($_POST['nombre']     ?? '');
        $email      = trim($_POST['email']      ?? '');
        $contrasena = $_POST['contrasena']      ?? '';

        if ($nombre && $email && $contrasena) {
            try {
                $db    = conectarDB();
                $check = $db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
                $check->execute([':email' => $email]);

                if ($check->fetch()) {
                    $error_registro = 'Este correo ya está registrado.';
                } else {
                    $hash = password_hash($contrasena, PASSWORD_BCRYPT);
                    $ins  = $db->prepare(
                        "INSERT INTO usuarios (nombre, email, contrasena, created_at)
                         VALUES (:nombre, :email, :contrasena, NOW())"
                    );
                    $ins->execute([
                        ':nombre'     => $nombre,
                        ':email'      => $email,
                        ':contrasena' => $hash,
                    ]);
                    $_SESSION['usuario_id']     = $db->lastInsertId();
                    $_SESSION['usuario_nombre'] = $nombre;
                    $login_exitoso = true;
                }
            } catch (Exception $e) {
                $error_registro = 'Error al registrar: ' . $e->getMessage();
            }
        } else {
            $error_registro = 'Por favor completa todos los campos.';
        }
    }

    // RECUPERAR CONTRASEÑA
    if (($_POST['action'] ?? '') === 'recovery') {
        $recovery_email = trim($_POST['recovery_email'] ?? '');
        $recovery_msg   = '¡Instrucciones enviadas a ' . htmlspecialchars($recovery_email) . '!';
    }

    // REGISTRAR ESTRÉS
    if (($_POST['action'] ?? '') === 'registrar_estres' && isset($_SESSION['usuario_id'])) {
        $uid           = $_SESSION['usuario_id'];
        $nivel_estres  = (int)($_POST['nivel_estres']  ?? 0);
        $horas_sueno   = (float)($_POST['horas_sueno']   ?? 0);
        $horas_estudio = (float)($_POST['horas_estudio'] ?? 0);
        $estado_animo  = $_POST['estado_animo'] ?? 'regular';
        $notas         = trim($_POST['notas'] ?? '');
        $materias_sel  = $_POST['materias'] ?? [];

        if ($nivel_estres < 1 || $nivel_estres > 10) {
            $error_estres = 'El nivel de estrés debe estar entre 1 y 10.';
        } else {
            try {
                $db = conectarDB();
                $db->beginTransaction();

                $ins = $db->prepare("
                    INSERT INTO registros_estres
                        (usuario_id, nivel_estres, horas_sueno, horas_estudio, estado_animo, notas)
                    VALUES (:uid, :ne, :hs, :he, :ea, :notas)
                ");
                $ins->execute([
                    ':uid'   => $uid,
                    ':ne'    => $nivel_estres,
                    ':hs'    => $horas_sueno,
                    ':he'    => $horas_estudio,
                    ':ea'    => $estado_animo,
                    ':notas' => $notas,
                ]);
                $registro_id = $db->lastInsertId();

                $ins_m = $db->prepare("
                    INSERT INTO estres_materias (registro_id, materia_id, nivel_preocupacion)
                    VALUES (:rid, :mid, :np)
                ");
                foreach ($materias_sel as $materia_id => $nivel_preo) {
                    $ins_m->execute([
                        ':rid' => $registro_id,
                        ':mid' => (int)$materia_id,
                        ':np'  => min(5, max(1, (int)$nivel_preo)),
                    ]);
                }

                $rec = $db->prepare("
                    SELECT id FROM recomendaciones
                    WHERE nivel_min <= :ne AND nivel_max >= :ne
                ");
                $rec->execute([':ne' => $nivel_estres]);
                $recs = $rec->fetchAll();

                $ins_r = $db->prepare("
                    INSERT INTO recomendaciones_usuario (usuario_id, recomendacion_id)
                    SELECT :uid, :rid FROM DUAL
                    WHERE NOT EXISTS (
                        SELECT 1 FROM recomendaciones_usuario
                        WHERE usuario_id = :uid2
                          AND recomendacion_id = :rid2
                          AND DATE(fecha_enviada) = CURDATE()
                    )
                ");
                foreach ($recs as $r) {
                    $ins_r->execute([
                        ':uid'  => $uid, ':rid'  => $r['id'],
                        ':uid2' => $uid, ':rid2' => $r['id'],
                    ]);
                }

                $db->commit();
                $msg_estres = '¡Registro guardado! Revisa tus recomendaciones en Bienestar.';
                $_SESSION['seccion_activa'] = 'registrar';

            } catch (Exception $e) {
                $db->rollBack();
                $error_estres = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }
}

// ─── GET: marcar recomendación como vista ──────────────────────────────────
if (isset($_GET['vista']) && isset($_SESSION['usuario_id'])) {
    try {
        $db  = conectarDB();
        $upd = $db->prepare("
            UPDATE recomendaciones_usuario
            SET vista = TRUE
            WHERE id = :id AND usuario_id = :uid
        ");
        $upd->execute([':id' => (int)$_GET['vista'], ':uid' => $_SESSION['usuario_id']]);
        header('Location: index.php?seccion=bienestar');
        exit;
    } catch (Exception $e) {}
}

// ─── Sesión activa ─────────────────────────────────────────────────────────
if (isset($_SESSION['usuario_id'])) {
    $login_exitoso = true;
}

// ─── Datos del dashboard ───────────────────────────────────────────────────
$nombre_usuario    = $_SESSION['usuario_nombre'] ?? 'Estudiante';
$ultimo_registro   = null;
$recomendaciones   = [];
$materias          = [];
$chart_labels      = [];
$chart_data        = [];
$stats_pendientes  = 0;
$seccion_activa    = $_GET['seccion'] ?? ($_SESSION['seccion_activa'] ?? 'dashboard');
unset($_SESSION['seccion_activa']);

if ($login_exitoso) {
    try {
        $db         = conectarDB();
        $uid        = $_SESSION['usuario_id'];

        $stmt = $db->prepare("
            SELECT nivel_estres, horas_sueno, horas_estudio, estado_animo, fecha
            FROM registros_estres
            WHERE usuario_id = :uid
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':uid' => $uid]);
        $ultimo_registro = $stmt->fetch();

        $stmt    = $db->query("SELECT id, nombre, color_hex FROM materias ORDER BY nombre");
        $materias = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT ru.id AS ru_id, r.categoria, r.titulo, r.descripcion, ru.vista
            FROM recomendaciones_usuario ru
            JOIN recomendaciones r ON r.id = ru.recomendacion_id
            WHERE ru.usuario_id = :uid AND DATE(ru.fecha_enviada) = CURDATE()
            ORDER BY ru.vista ASC, ru.fecha_enviada DESC
        ");
        $stmt->execute([':uid' => $uid]);
        $recomendaciones = $stmt->fetchAll();
        $stats_pendientes = count(array_filter($recomendaciones, fn($r) => !$r['vista']));

        $stmt = $db->prepare("
            SELECT DATE_FORMAT(fecha, '%d/%m') AS dia, AVG(nivel_estres) AS promedio
            FROM registros_estres
            WHERE usuario_id = :uid AND fecha >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            GROUP BY fecha ORDER BY fecha ASC
        ");
        $stmt->execute([':uid' => $uid]);
        $chart_rows = $stmt->fetchAll();
        foreach ($chart_rows as $row) {
            $chart_labels[] = $row['dia'];
            $chart_data[]   = round((float)$row['promedio'], 1);
        }

    } catch (Exception $e) {
        // Sin datos disponibles
    }
}

$iconos_categoria = [
    'sueño'    => '😴',
    'estudio'  => '📚',
    'descanso' => '🧘',
    'social'   => '🤝',
];
$iconos_animo = ['bien' => '😊', 'regular' => '😐', 'mal' => '😔'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equilibrio Académico</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forgot-password.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
        /* ══ LOGIN — tema fijo claro (por defecto) ══════════════════════════ */
        #main-wrapper {
            background: #111 !important;
        }
        #main-wrapper .forms-container,
        #main-wrapper .auth-form,
        #main-wrapper .sign-in-container,
        #main-wrapper .sign-up-container {
            background: #ffffff !important;
            color: #333333 !important;
        }
        #main-wrapper .welcome-msg,
        #main-wrapper h2 {
            color: #2d8a8a !important;
        }
        #main-wrapper p,
        #main-wrapper label,
        #main-wrapper small {
            color: #555555 !important;
        }
        #main-wrapper a.forgot-link,
        #main-wrapper a {
            color: #2d9596 !important;
        }
        #main-wrapper input[type="text"],
        #main-wrapper input[type="email"],
        #main-wrapper input[type="password"] {
            background: #f5f7f7 !important;
            color: #333333 !important;
            border: 1px solid #d0d8d8 !important;
        }
        #main-wrapper input::placeholder {
            color: #aaa !important;
        }
        #main-wrapper .btn-toggle {
            background: transparent !important;
            color: #666 !important;
        }
        #main-wrapper .btn-toggle.active {
            background: #e6f4f4 !important;
            color: #2d8a8a !important;
        }
        #main-wrapper .overlay-panel h1,
        #main-wrapper .overlay-panel p {
            color: #ffffff !important;
        }
        /* Modal — claro por defecto */
        #modal-forgot .modal-content {
            background: #ffffff !important;
            color: #333333 !important;
        }
        #modal-forgot .modal-content h2,
        #modal-forgot .modal-content p {
            color: #333333 !important;
        }
        #modal-forgot .modal-content input[type="email"] {
            background: #f5f7f7 !important;
            color: #333333 !important;
            border: 1px solid #d0d8d8 !important;
        }
        #modal-forgot .modal-content input::placeholder {
            color: #aaa !important;
        }

        /* ══ LOGIN — versión modo descanso (oscuro elegante) ════════════════ */
        body.modo-descanso #main-wrapper {
            background: #0a0a0f !important;
        }
        body.modo-descanso #main-wrapper .forms-container,
        body.modo-descanso #main-wrapper .auth-form,
        body.modo-descanso #main-wrapper .sign-in-container,
        body.modo-descanso #main-wrapper .sign-up-container {
            background: #16161f !important;
            color: #e0e0e0 !important;
            border-right: 1px solid rgba(255,255,255,0.06) !important;
        }
        body.modo-descanso #main-wrapper .welcome-msg,
        body.modo-descanso #main-wrapper h2 {
            color: #5bbfbf !important;
        }
        body.modo-descanso #main-wrapper p,
        body.modo-descanso #main-wrapper label,
        body.modo-descanso #main-wrapper small {
            color: #aaaaaa !important;
        }
        body.modo-descanso #main-wrapper a.forgot-link,
        body.modo-descanso #main-wrapper a {
            color: #5bbfbf !important;
        }
        body.modo-descanso #main-wrapper input[type="text"],
        body.modo-descanso #main-wrapper input[type="email"],
        body.modo-descanso #main-wrapper input[type="password"] {
            background: #0f0f18 !important;
            color: #e0e0e0 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        body.modo-descanso #main-wrapper input::placeholder {
            color: #555 !important;
        }
        body.modo-descanso #main-wrapper .btn-toggle {
            background: transparent !important;
            color: #888 !important;
        }
        body.modo-descanso #main-wrapper .btn-toggle.active {
            background: rgba(91,191,191,0.15) !important;
            color: #5bbfbf !important;
        }
        body.modo-descanso #main-wrapper .overlay-panel h1,
        body.modo-descanso #main-wrapper .overlay-panel p {
            color: #ffffff !important;
        }
        /* Modal — oscuro en modo descanso */
        body.modo-descanso #modal-forgot .modal-content {
            background: #16161f !important;
            color: #e0e0e0 !important;
        }
        body.modo-descanso #modal-forgot .modal-content h2,
        body.modo-descanso #modal-forgot .modal-content p {
            color: #e0e0e0 !important;
        }
        body.modo-descanso #modal-forgot .modal-content input[type="email"] {
            background: #0f0f18 !important;
            color: #e0e0e0 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        body.modo-descanso #modal-forgot .modal-content input::placeholder {
            color: #555 !important;
        }

        /* ── Secciones del dashboard ── */
        .section-panel { display: none; }
        .section-panel.activa { display: block; }

        /* ── Mini-cards con datos reales ── */
        .mini-card { display: flex; flex-direction: column; gap: 6px; }
        .mini-card .card-valor { font-size: 2rem; font-weight: 700; }
        .mini-card .card-label { font-size: 0.8rem; opacity: 0.7; }
        .card-estres  .card-valor { color: #e74c3c; }
        .card-sueno   .card-valor { color: #3498db; }
        .card-recs    .card-valor { color: #2ecc71; }

        /* ── Formulario de estrés ── */
        .form-estres { max-width: 620px; padding: 24px 0; }
        .form-estres h3 { margin-bottom: 20px; font-size: 1.3rem; }
        .form-estres label { display: block; font-size: 0.85rem; margin-bottom: 4px; opacity: 0.75; }
        .slider-group { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .slider-group input[type="range"] { flex: 1; }
        .slider-group span {
            min-width: 32px; text-align: center; font-size: 1.4rem;
            font-weight: 700; color: #e74c3c;
        }
        .input-row { display: flex; gap: 16px; margin-bottom: 20px; }
        .input-row label { flex: 1; }
        .input-row input { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.07); color: inherit; }
        .animo-group { display: flex; gap: 20px; margin-bottom: 20px; }
        .animo-group label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.95rem; opacity: 1; }
        .materias-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .materia-item { background: rgba(255,255,255,0.05); border-radius: 10px; padding: 10px 14px; }
        .materia-item label { display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 6px; opacity: 1; }
        .materia-item input[type="range"] { width: 100%; transition: opacity 0.2s; }
        .form-estres textarea {
            width: 100%; padding: 10px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.07);
            color: inherit; resize: vertical; margin-bottom: 20px;
        }
        .msg-exito { color: #2ecc71; margin-bottom: 16px; }
        .msg-error { color: #e74c3c; margin-bottom: 16px; }

        /* ── Recomendaciones ── */
        .bienestar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .rec-card {
            border-radius: 14px; padding: 18px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex; gap: 14px; transition: transform 0.2s;
        }
        .rec-card:hover { transform: translateY(-2px); }
        .rec-card.nueva { border-left: 3px solid #2ecc71; }
        .rec-card.vista  { opacity: 0.5; }
        .rec-icono { font-size: 1.8rem; flex-shrink: 0; }
        .rec-contenido strong { display: block; margin-bottom: 6px; }
        .rec-contenido p { font-size: 0.85rem; opacity: 0.75; margin-bottom: 10px; }
        .btn-visto {
            font-size: 0.78rem; padding: 4px 10px;
            border-radius: 20px; background: rgba(46,204,113,0.15);
            color: #2ecc71; border: 1px solid rgba(46,204,113,0.3);
            text-decoration: none; display: inline-block;
        }
        .btn-visto:hover { background: rgba(46,204,113,0.25); }
        .sin-registros { text-align: center; padding: 40px; opacity: 0.6; }
        .sin-registros a { color: #2ecc71; }

        /* ── Gráfica ── */
        .chart-container { position: relative; max-width: 680px; margin: 20px 0; }
        .chart-empty { text-align: center; padding: 60px; opacity: 0.5; }

        /* ── Ajustes ── */
        .ajustes-section { max-width: 480px; padding: 10px 0; }
        .ajustes-section h4 { margin: 24px 0 10px; font-size: 0.95rem; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.05em; }
        .ajuste-fila { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .btn-danger { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid rgba(231,76,60,0.3); padding: 8px 16px; border-radius: 8px; cursor: pointer; }
        .badge-pendiente {
            background: #e74c3c; color: #fff; border-radius: 50%;
            font-size: 0.7rem; padding: 1px 6px; margin-left: 4px;
            display: inline-block; line-height: 1.5;
        }

        /* ══ MODO DESCANSO / DARK MODE ══════════════════════════════════════ */
        body {
            transition: background 0.4s ease, color 0.4s ease;
        }

        body.modo-descanso {
            background: #0f0f13 !important;
            color: #e0e0e0 !important;
        }

        /* Todos los textos en modo descanso */
        body.modo-descanso h1,
        body.modo-descanso h2,
        body.modo-descanso h3,
        body.modo-descanso h4,
        body.modo-descanso h5,
        body.modo-descanso h6 {
            color: #f0f0f0 !important;
        }
        body.modo-descanso p,
        body.modo-descanso span:not(.badge-pendiente),
        body.modo-descanso label,
        body.modo-descanso small,
        body.modo-descanso li,
        body.modo-descanso td,
        body.modo-descanso th {
            color: #d0d0d0 !important;
        }
        body.modo-descanso .profile-info h3,
        body.modo-descanso .profile-info p {
            color: #e0e0e0 !important;
        }
        body.modo-descanso .card-label {
            color: #aaa !important;
            opacity: 1 !important;
        }
        body.modo-descanso .card-valor {
            opacity: 1 !important;
        }
        body.modo-descanso .cal-dow {
            color: #888 !important;
            opacity: 1 !important;
        }
        body.modo-descanso .cal-day {
            color: #ccc !important;
        }
        body.modo-descanso .cal-day.otro-mes {
            color: #555 !important;
            opacity: 1 !important;
        }
        body.modo-descanso .cal-day.hoy {
            color: #fff !important;
        }
        body.modo-descanso .rec-contenido strong {
            color: #f0f0f0 !important;
        }
        body.modo-descanso .rec-contenido p {
            color: #aaa !important;
        }
        body.modo-descanso .ajustes-section h4 {
            color: #888 !important;
            opacity: 1 !important;
        }
        body.modo-descanso .ajuste-fila span {
            color: #ccc !important;
        }
        body.modo-descanso .form-estres label {
            color: #bbb !important;
            opacity: 1 !important;
        }

        /* Contenedores */
        body.modo-descanso .dashboard-container {
            background: #0f0f13 !important;
        }
        body.modo-descanso .sidebar {
            background: #1a1a24 !important;
            border-right: 1px solid rgba(255,255,255,0.06) !important;
        }
        body.modo-descanso .main-content {
            background: #0f0f13 !important;
        }
        body.modo-descanso .hero-module,
        body.modo-descanso .hero-container {
            background: #1a1a24 !important;
            border: 1px solid rgba(255,255,255,0.06) !important;
            border-radius: 16px !important;
        }
        body.modo-descanso .cards-grid .mini-card {
            background: #1a1a24 !important;
            border: 1px solid rgba(255,255,255,0.06) !important;
        }
        body.modo-descanso .calendar-card {
            background: #1a1a24 !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
        }
        body.modo-descanso .search-bar {
            background: #1a1a24 !important;
            color: #e0e0e0 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        body.modo-descanso .menu-item {
            color: #ccc !important;
        }
        body.modo-descanso .menu-item .text {
            color: #ccc !important;
        }
        body.modo-descanso .menu-item:hover,
        body.modo-descanso .menu-item.active {
            background: rgba(255,255,255,0.08) !important;
        }
        body.modo-descanso .rec-card {
            background: #1e1e2e !important;
            border-color: rgba(255,255,255,0.08) !important;
        }
        body.modo-descanso .materia-item {
            background: #1e1e2e !important;
        }
        body.modo-descanso .form-estres textarea,
        body.modo-descanso .input-row input {
            background: #1e1e2e !important;
            border-color: rgba(255,255,255,0.08) !important;
            color: #e0e0e0 !important;
        }
        body.modo-descanso .btn-rest {
            background: #2a2a3a !important;
            color: #e0e0e0 !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        body.modo-descanso .ajuste-fila {
            border-bottom-color: rgba(255,255,255,0.06) !important;
        }

        /* ══ CALENDARIO ═════════════════════════════════════════════════════ */
        .calendar-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 16px;
            min-width: 260px;
            user-select: none;
        }
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .calendar-header h3 {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            margin: 0;
            text-transform: capitalize;
        }
        .calendar-header button {
            background: rgba(255,255,255,0.07);
            border: none;
            color: inherit;
            border-radius: 8px;
            width: 28px; height: 28px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.15s;
            display: flex; align-items: center; justify-content: center;
        }
        .calendar-header button:hover { background: rgba(255,255,255,0.15); }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }
        .cal-dow {
            font-size: 0.65rem;
            text-align: center;
            opacity: 0.4;
            font-weight: 600;
            padding: 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .cal-day {
            font-size: 0.8rem;
            text-align: center;
            padding: 5px 2px;
            border-radius: 8px;
            cursor: default;
            transition: background 0.15s;
            line-height: 1;
        }
        .cal-day.otro-mes { opacity: 0.2; }
        .cal-day.hoy {
            background: #e74c3c;
            color: #fff;
            font-weight: 700;
            border-radius: 50%;
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto;
        }
        .cal-day:not(.otro-mes):not(.hoy):hover {
            background: rgba(255,255,255,0.1);
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php if (!$login_exitoso): ?>

<!-- ════════════════════════════════
     LOGIN / REGISTRO
════════════════════════════════ -->
<div class="main-container" id="main-wrapper" style="display:none; opacity:0;">
    <div class="forms-container">

        <!-- LOGIN -->
        <form id="login-form" class="auth-form sign-in-container" method="POST" action="">
            <input type="hidden" name="action" value="login">
            <h2 class="welcome-msg">¡Nos alegra verte!</h2>

            <div class="toggle-group">
                <button type="button" class="btn-toggle active" id="btn-login-swap">Login</button>
                <button type="button" class="btn-toggle" id="btn-signup-swap">Registrarse</button>
            </div>

            <?php if ($error_login): ?>
                <p style="color:#e74c3c; font-size:.85rem; margin:0 0 8px;">
                    ⚠️ <?= htmlspecialchars($error_login) ?>
                </p>
            <?php endif; ?>

            <div class="input-group">
                <input type="text"     name="email_usuario" placeholder="Email o Usuario" required>
                <input type="password" name="contrasena"    placeholder="Contraseña"      required>
            </div>
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
            <button type="submit" class="btn-submit">ENTRAR</button>
        </form>

        <!-- REGISTRO -->
        <form id="signup-form" class="auth-form sign-up-container" method="POST" action="">
            <input type="hidden" name="action" value="registro">
            <h2 class="welcome-msg">¡Crea tu cuenta!</h2>

            <div class="toggle-group">
                <button type="button" class="btn-toggle"        id="btn-login-swap-2">Login</button>
                <button type="button" class="btn-toggle active" id="btn-signup-swap-2">Registrarse</button>
            </div>

            <?php if ($error_registro): ?>
                <p style="color:#e74c3c; font-size:.85rem; margin:0 0 8px;">
                    ⚠️ <?= htmlspecialchars($error_registro) ?>
                </p>
            <?php endif; ?>

            <div class="input-group">
                <input type="text"     name="nombre"     placeholder="Nombre completo" required>
                <input type="email"    name="email"      placeholder="Email"           required>
                <input type="password" name="contrasena" placeholder="Contraseña"      required>
            </div>
            <button type="submit" class="btn-submit">REGISTRARSE</button>
        </form>
    </div>

    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>¿Ya tienes cuenta?</h1>
                <p>Para mantenerte conectado, inicia sesión con tus datos personales</p>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>¡Hola de nuevo!</h1>
                <p>Ingresa tus datos y comienza tu viaje con nosotros</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RECUPERAR CONTRASEÑA -->
<div id="modal-forgot" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="close-forgot">&times;</span>
        <h2>¿Perdiste tu contraseña?</h2>
        <p>No te preocupes, dinos tu correo y te enviaremos las instrucciones de recuperación.</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="recovery">
            <div class="input-group">
                <input type="email" name="recovery_email" placeholder="Tu correo electrónico" required>
            </div>
            <?php if ($recovery_msg): ?>
                <p style="color:#2ecc71;"><?= htmlspecialchars($recovery_msg) ?></p>
            <?php endif; ?>
            <button type="submit" class="btn-submit">ENVIAR INSTRUCCIONES</button>
        </form>
    </div>
</div>

<?php else: ?>

<!-- ════════════════════════════════
     DASHBOARD
════════════════════════════════ -->
<div class="dashboard-container" id="dashboard-view" style="display:flex; opacity:1;">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="profile-section">
            <div class="avatar">🤖</div>
            <div class="profile-info">
                <h3>Bienestar Estudiantil</h3>
                <p>Perfil del estudiante</p>
            </div>
        </div>

        <button class="menu-item <?= $seccion_activa === 'dashboard'  ? 'active' : '' ?>"
                onclick="mostrarSeccion('dashboard')">
            <span class="icon">🏠</span><span class="text">Dashboard</span>
        </button>

        <button class="menu-item <?= $seccion_activa === 'registrar'  ? 'active' : '' ?>"
                onclick="mostrarSeccion('registrar')">
            <span class="icon">➕</span><span class="text">Registrar</span>
        </button>

        <button class="menu-item <?= $seccion_activa === 'progreso'   ? 'active' : '' ?>"
                onclick="mostrarSeccion('progreso')">
            <span class="icon">📊</span><span class="text">Gráfica</span>
        </button>

        <button class="menu-item <?= $seccion_activa === 'bienestar'  ? 'active' : '' ?>"
                onclick="mostrarSeccion('bienestar')">
            <span class="icon">🧘</span>
            <span class="text">Bienestar</span>
            <?php if ($stats_pendientes > 0): ?>
                <span class="badge-pendiente"><?= $stats_pendientes ?></span>
            <?php endif; ?>
        </button>

        <button class="menu-item <?= $seccion_activa === 'ajustes'    ? 'active' : '' ?>"
                onclick="mostrarSeccion('ajustes')">
            <span class="icon">⚙️</span><span class="text">Ajustes</span>
        </button>

        <button class="menu-item" onclick="window.location.href='logout.php'">
            <span class="icon">🚪</span><span class="text">Cerrar Sesión</span>
        </button>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <button id="btn-eye-care" class="btn-rest">
            <span class="text">Modo Descanso</span>
        </button>
        <input type="text" class="search-bar" placeholder="Buscar...">

        <!-- ── SECCIÓN: DASHBOARD ─────────────────────────────── -->
        <div id="section-dashboard" class="section-panel <?= $seccion_activa === 'dashboard' ? 'activa' : '' ?>">

            <div class="hero-module">
                <div class="hero-container" style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="hero-left">
                        <h2>¡Bienvenido, <?= htmlspecialchars($nombre_usuario) ?>!</h2>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <iframe src="lampara.html" style="width:120px; height:140px; border:none; background:transparent;" scrolling="no"></iframe>
                            <div>
                                <p id="daily-proverb" style="font-style:italic; margin:0; opacity:0; transition:opacity .3s;"></p>
                                <small style="color:#888;">Haz clic en la lámpara para iluminar tu día.</small>
                            </div>
                        </div>
                    </div>

                    <div class="hero-calendar">
                        <div class="calendar-card">
                            <div class="calendar-header">
                                <button id="prev-month">&lt;</button>
                                <h3 id="month-display"></h3>
                                <button id="next-month">&gt;</button>
                            </div>
                            <div id="calendar-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MINI-CARDS con datos reales -->
            <div class="cards-grid">
                <div class="mini-card card-estres">
                    <?php if ($ultimo_registro): ?>
                        <span class="card-valor"><?= $ultimo_registro['nivel_estres'] ?>/10</span>
                        <span class="card-label">Estrés último registro</span>
                        <small style="opacity:.5;"><?= $iconos_animo[$ultimo_registro['estado_animo']] ?? '' ?> <?= ucfirst($ultimo_registro['estado_animo']) ?></small>
                    <?php else: ?>
                        <span class="card-valor">—</span>
                        <span class="card-label">Sin registros aún</span>
                        <a href="#" onclick="mostrarSeccion('registrar')" style="font-size:.8rem; color:#2ecc71;">Registrar ahora</a>
                    <?php endif; ?>
                </div>

                <div class="mini-card card-sueno">
                    <?php if ($ultimo_registro): ?>
                        <span class="card-valor"><?= $ultimo_registro['horas_sueno'] ?>h</span>
                        <span class="card-label">Horas de sueño</span>
                        <small style="opacity:.5;">📅 <?= date('d/m', strtotime($ultimo_registro['fecha'])) ?></small>
                    <?php else: ?>
                        <span class="card-valor">—</span>
                        <span class="card-label">Horas de sueño</span>
                    <?php endif; ?>
                </div>

                <div class="mini-card card-recs">
                    <span class="card-valor"><?= $stats_pendientes ?></span>
                    <span class="card-label">Recomendaciones pendientes</span>
                    <?php if ($stats_pendientes > 0): ?>
                        <a href="#" onclick="mostrarSeccion('bienestar')" style="font-size:.8rem; color:#2ecc71;">Ver ahora</a>
                    <?php else: ?>
                        <small style="opacity:.5;">✓ Al día</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── SECCIÓN: REGISTRAR ESTRÉS ─────────────────────── -->
        <div id="section-registrar" class="section-panel <?= $seccion_activa === 'registrar' ? 'activa' : '' ?>">
            <div class="form-estres">
                <h3>¿Cómo te sientes hoy?</h3>

                <?php if ($msg_estres): ?>
                    <p class="msg-exito">✅ <?= htmlspecialchars($msg_estres) ?></p>
                <?php endif; ?>
                <?php if ($error_estres): ?>
                    <p class="msg-error">⚠️ <?= htmlspecialchars($error_estres) ?></p>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="registrar_estres">

                    <label>Nivel de estrés (1 = tranquilo &nbsp;·&nbsp; 10 = al límite)</label>
                    <div class="slider-group">
                        <input type="range" name="nivel_estres" min="1" max="10" value="5"
                               oninput="document.getElementById('val-estres').textContent = this.value">
                        <span id="val-estres">5</span>
                    </div>

                    <div class="input-row">
                        <label>Horas de sueño
                            <input type="number" name="horas_sueno" min="0" max="24" step="0.5" value="7">
                        </label>
                        <label>Horas de estudio
                            <input type="number" name="horas_estudio" min="0" max="24" step="0.5" value="2">
                        </label>
                    </div>

                    <label>Estado de ánimo</label>
                    <div class="animo-group">
                        <label><input type="radio" name="estado_animo" value="bien"> 😊 Bien</label>
                        <label><input type="radio" name="estado_animo" value="regular" checked> 😐 Regular</label>
                        <label><input type="radio" name="estado_animo" value="mal"> 😔 Mal</label>
                    </div>

                    <label>¿Qué materias te preocupan hoy?</label>
                    <div class="materias-grid">
                        <?php foreach ($materias as $m): ?>
                            <div class="materia-item">
                                <label>
                                    <input type="checkbox"
                                           onchange="toggleSlider(this, 'sl-<?= $m['id'] ?>')">
                                    <span style="color:<?= htmlspecialchars($m['color_hex']) ?>; font-weight:600;">
                                        <?= htmlspecialchars($m['nombre']) ?>
                                    </span>
                                </label>
                                <input type="range" id="sl-<?= $m['id'] ?>"
                                       name="materias[<?= $m['id'] ?>]"
                                       min="1" max="5" value="3" disabled
                                       style="opacity:.3; width:100%;">
                                <small style="opacity:.5; font-size:.75rem;">Preocupación: 1–5</small>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <label>Notas (opcional)
                        <textarea name="notas" rows="3"
                                  placeholder="¿Qué te tiene estresado hoy? Escríbelo aquí..."></textarea>
                    </label>

                    <button type="submit" class="btn-submit">Guardar registro</button>
                </form>
            </div>
        </div>

        <!-- ── SECCIÓN: GRÁFICA ───────────────────────────────── -->
        <div id="section-progreso" class="section-panel <?= $seccion_activa === 'progreso' ? 'activa' : '' ?>">
            <h3 style="margin-bottom:20px;">Tu progreso — últimos 14 días</h3>

            <?php if (empty($chart_data)): ?>
                <div class="chart-empty">
                    <p>📊 Aún no hay datos suficientes para mostrar la gráfica.</p>
                    <p><a href="#" onclick="mostrarSeccion('registrar')" style="color:#2ecc71;">
                        Empieza registrando tu estrés de hoy.
                    </a></p>
                </div>
            <?php else: ?>
                <div class="chart-container">
                    <canvas id="chart-estres" height="300"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── SECCIÓN: BIENESTAR ─────────────────────────────── -->
        <div id="section-bienestar" class="section-panel <?= $seccion_activa === 'bienestar' ? 'activa' : '' ?>">
            <div class="bienestar-header">
                <h3>Tus recomendaciones de hoy</h3>
                <?php if ($stats_pendientes > 0): ?>
                    <span style="font-size:.85rem; color:#e67e22;">
                        <?= $stats_pendientes ?> pendiente<?= $stats_pendientes > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($recomendaciones)): ?>
                <div class="sin-registros">
                    <p>🌱 Aún no tienes recomendaciones para hoy.</p>
                    <p><a href="#" onclick="mostrarSeccion('registrar')">
                        Registra tu estrés del día
                    </a> para recibirlas.</p>
                </div>
            <?php else: ?>
                <div class="rec-grid">
                    <?php foreach ($recomendaciones as $rec): ?>
                        <div class="rec-card <?= $rec['vista'] ? 'vista' : 'nueva' ?>">
                            <span class="rec-icono"><?= $iconos_categoria[$rec['categoria']] ?? '💡' ?></span>
                            <div class="rec-contenido">
                                <strong><?= htmlspecialchars($rec['titulo']) ?></strong>
                                <p><?= htmlspecialchars($rec['descripcion']) ?></p>
                                <?php if (!$rec['vista']): ?>
                                    <a href="index.php?vista=<?= $rec['ru_id'] ?>" class="btn-visto">
                                        Marcar como visto ✓
                                    </a>
                                <?php else: ?>
                                    <small style="opacity:.5;">✓ Vista</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── SECCIÓN: AJUSTES ───────────────────────────────── -->
        <div id="section-ajustes" class="section-panel <?= $seccion_activa === 'ajustes' ? 'activa' : '' ?>">
            <h3 style="margin-bottom:4px;">Ajustes</h3>
            <p style="opacity:.5; font-size:.85rem; margin-bottom:20px;">
                Cuenta: <?= htmlspecialchars($nombre_usuario) ?>
            </p>

            <div class="ajustes-section">
                <h4>Apariencia</h4>
                <div class="ajuste-fila">
                    <span>Modo Descanso</span>
                    <button id="btn-eye-care-2" class="btn-rest" style="font-size:.85rem; padding:6px 14px;">
                        Activar
                    </button>
                </div>

                <h4>Cuenta</h4>
                <div class="ajuste-fila">
                    <span>Cerrar sesión</span>
                    <button onclick="window.location.href='logout.php'" class="btn-danger">
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>

<?php endif; ?>

<!-- ─── Scripts externos ───────────────────────────────────────────────── -->
<script src="script.js"></script>
<script src="dashboard-interactivo.js"></script>
<script src="navegación.js"></script>
<script src="forgot-password.js"></script>

<!-- ─── Script principal del dashboard ────────────────────────────────── -->
<script>
// ── Navegación entre secciones ────────────────────────────────────────────
function mostrarSeccion(nombre) {
    document.querySelectorAll('.section-panel').forEach(el => el.classList.remove('activa'));
    document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));

    const target = document.getElementById('section-' + nombre);
    if (target) target.classList.add('activa');

    const botones = { dashboard: 0, registrar: 1, progreso: 2, bienestar: 3, ajustes: 4 };
    const items = document.querySelectorAll('.menu-item');
    if (botones[nombre] !== undefined && items[botones[nombre]]) {
        items[botones[nombre]].classList.add('active');
    }
}

// ── Toggle slider de materias ─────────────────────────────────────────────
function toggleSlider(checkbox, sliderId) {
    const slider = document.getElementById(sliderId);
    if (!slider) return;
    slider.disabled = !checkbox.checked;
    slider.style.opacity = checkbox.checked ? '1' : '0.3';
}

// ══ MODO DESCANSO / DARK MODE ══════════════════════════════════════════════
(function () {
    const CLAVE = 'equilibrio_descanso';
    const body  = document.body;

    function setModo(activo) {
        body.classList.toggle('modo-descanso', activo);
        localStorage.setItem(CLAVE, activo ? '1' : '0');

        // Actualizar texto de todos los botones de modo descanso
        document.querySelectorAll('#btn-eye-care, #btn-eye-care-2').forEach(btn => {
            const span = btn.querySelector('.text');
            const label = activo ? 'Modo Normal' : 'Modo Descanso';
            if (span) span.textContent = label;
            else btn.textContent = label;
        });
    }

    // Restaurar preferencia guardada al cargar la página
    if (localStorage.getItem(CLAVE) === '1') setModo(true);

    // Escuchar clics en ambos botones
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#btn-eye-care, #btn-eye-care-2');
        if (btn) setModo(!body.classList.contains('modo-descanso'));
    });
})();

// ══ CALENDARIO ═════════════════════════════════════════════════════════════
(function () {
    const MESES = [
        'Enero','Febrero','Marzo','Abril','Mayo','Junio',
        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
    ];
    const DIAS = ['Do','Lu','Ma','Mi','Ju','Vi','Sa'];

    let hoy    = new Date();
    let actual = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

    function render() {
        const display  = document.getElementById('month-display');
        const content  = document.getElementById('calendar-content');
        if (!display || !content) return;

        display.textContent = MESES[actual.getMonth()] + ' ' + actual.getFullYear();

        const primerDia   = actual.getDay();
        const diasMes     = new Date(actual.getFullYear(), actual.getMonth() + 1, 0).getDate();
        const diasPrevios = primerDia;
        const mesAnterior = new Date(actual.getFullYear(), actual.getMonth(), 0);
        const totalPrev   = mesAnterior.getDate();

        let html = '<div class="cal-grid">';
        DIAS.forEach(d => html += `<div class="cal-dow">${d}</div>`);

        for (let i = diasPrevios - 1; i >= 0; i--) {
            html += `<div class="cal-day otro-mes">${totalPrev - i}</div>`;
        }
        for (let d = 1; d <= diasMes; d++) {
            const esHoy = (
                d === hoy.getDate() &&
                actual.getMonth()    === hoy.getMonth() &&
                actual.getFullYear() === hoy.getFullYear()
            );
            html += `<div class="cal-day${esHoy ? ' hoy' : ''}">${d}</div>`;
        }

        const total = diasPrevios + diasMes;
        const resto = total % 7 === 0 ? 0 : 7 - (total % 7);
        for (let d = 1; d <= resto; d++) {
            html += `<div class="cal-day otro-mes">${d}</div>`;
        }
        html += '</div>';
        content.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        const prev = document.getElementById('prev-month');
        const next = document.getElementById('next-month');
        if (prev) prev.addEventListener('click', function () {
            actual = new Date(actual.getFullYear(), actual.getMonth() - 1, 1);
            render();
        });
        if (next) next.addEventListener('click', function () {
            actual = new Date(actual.getFullYear(), actual.getMonth() + 1, 1);
            render();
        });
    });
})();

// ── Gráfica de estrés (Chart.js) ─────────────────────────────────────────
<?php if (!empty($chart_data)): ?>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chart-estres');
    if (!ctx) return;

    const labels = <?= json_encode($chart_labels) ?>;
    const data   = <?= json_encode($chart_data)   ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nivel de estrés',
                data: data,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#e74c3c',
                pointRadius: 4,
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Estrés: ' + ctx.parsed.y + '/10'
                    }
                }
            },
            scales: {
                y: {
                    min: 1, max: 10,
                    ticks: { color: 'rgba(255,255,255,0.5)', stepSize: 1 },
                    grid:  { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: 'rgba(255,255,255,0.5)' },
                    grid:  { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

</body>
</html>