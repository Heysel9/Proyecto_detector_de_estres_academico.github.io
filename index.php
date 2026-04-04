<?php
// =============================================
//  Conexión a la base de datos
// =============================================
require_once 'conexion.php';

// Iniciar sesión PHP
session_start();

// =============================================
//  Lógica de Login
// =============================================
$error_login    = '';
$error_registro = '';
$login_exitoso  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- INICIO DE SESIÓN ---
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $emailUsuario = trim($_POST['email_usuario'] ?? '');
        $contrasena   = $_POST['contrasena'] ?? '';

        if ($emailUsuario && $contrasena) {
            try {
                $db   = conectarDB();
                $stmt = $db->prepare(
                    "SELECT id, nombre, contrasena FROM usuarios
                     WHERE email = :eu OR usuario = :eu2
                     LIMIT 1"
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

    // --- REGISTRO ---
    if (isset($_POST['action']) && $_POST['action'] === 'registro') {
        $nombre     = trim($_POST['nombre']     ?? '');
        $email      = trim($_POST['email']      ?? '');
        $contrasena = $_POST['contrasena']      ?? '';

        if ($nombre && $email && $contrasena) {
            try {
                $db   = conectarDB();

                // Verificar si el email ya existe
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
                    // Auto-login tras registro
                    $_SESSION['usuario_id']     = $db->lastInsertId();
                    $_SESSION['usuario_nombre'] = $nombre;
                    $login_exitoso = true;
                }
            } catch (Exception $e) {
                $error_registro = 'Error al registrar. Inténtalo de nuevo.';
            }
        } else {
            $error_registro = 'Por favor completa todos los campos.';
        }
    }

    // --- RECUPERAR CONTRASEÑA ---
    if (isset($_POST['action']) && $_POST['action'] === 'recovery') {
        $recovery_email = trim($_POST['recovery_email'] ?? '');
        // Aquí puedes agregar lógica de envío de correo
        // Por ahora solo se valida que el email exista
        $recovery_msg = '¡Instrucciones enviadas a ' . htmlspecialchars($recovery_email) . '!';
    }
}

// Si ya hay sesión activa, marcar login exitoso
if (isset($_SESSION['usuario_id'])) {
    $login_exitoso = true;
}

$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Estudiante';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equilibrio Académico - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forgot-password.css">
</head>
<body>

<?php if (!$login_exitoso): ?>
<!-- ===================================================
     SECCIÓN: FORMULARIOS DE LOGIN / REGISTRO
====================================================== -->
<div class="main-container" id="main-wrapper" style="display: none; opacity: 0;">
    <div class="forms-container">

        <!-- FORMULARIO LOGIN -->
        <form id="login-form" class="auth-form sign-in-container" method="POST" action="">
            <input type="hidden" name="action" value="login">
            <h2 class="welcome-msg">¡Nos alegra verte!</h2>

            <div class="toggle-group">
                <button type="button" class="btn-toggle active" id="btn-login-swap">Login</button>
                <button type="button" class="btn-toggle" id="btn-signup-swap">Registrarse</button>
            </div>

            <?php if ($error_login): ?>
                <p style="color:#e74c3c; font-size:0.85rem; margin:0 0 8px;">
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

        <!-- FORMULARIO REGISTRO -->
        <form id="signup-form" class="auth-form sign-up-container" method="POST" action="">
            <input type="hidden" name="action" value="registro">
            <h2 class="welcome-msg">¡Crea tu cuenta!</h2>

            <div class="toggle-group">
                <button type="button" class="btn-toggle"        id="btn-login-swap-2">Login</button>
                <button type="button" class="btn-toggle active" id="btn-signup-swap-2">Registrarse</button>
            </div>

            <?php if ($error_registro): ?>
                <p style="color:#e74c3c; font-size:0.85rem; margin:0 0 8px;">
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
                <p>Para mantenerte conectado, por favor inicia sesión con tus datos personales</p>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>¡Hola de nuevo!</h1>
                <p>Ingresa tus datos personales y comienza tu viaje con nosotros</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RECUPERAR CONTRASEÑA -->
<div id="modal-forgot" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <span class="close-modal" id="close-forgot">&times;</span>
        <h2>¿Perdiste tu contraseña?</h2>
        <p>No te preocupes, dinos tu correo y te enviaremos las instrucciones de recuperación.</p>

        <form method="POST" action="">
            <input type="hidden" name="action" value="recovery">
            <div class="input-group">
                <input type="email" name="recovery_email" id="recovery-email"
                       placeholder="Tu correo electrónico" required>
            </div>
            <?php if (!empty($recovery_msg)): ?>
                <p style="color:#2ecc71;"><?= htmlspecialchars($recovery_msg) ?></p>
            <?php endif; ?>
            <button type="submit" class="btn-submit">ENVIAR INSTRUCCIONES</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ===================================================
     SECCIÓN: DASHBOARD (usuario autenticado)
====================================================== -->
<div class="dashboard-container" id="dashboard-view">
    <aside class="sidebar">

        <!-- PERFIL -->
        <div class="profile-section">
            <div class="avatar">🤖</div>
            <div class="profile-info">
                <h3>Bienestar Estudiantil</h3>
                <p>Perfil del estudiante</p>
            </div>
        </div>

        <!-- MENÚ -->
        <button id="btn-dashboard" class="menu-item">
            <span class="icon">🏠</span>
            <span class="text">Dashboard</span>
        </button>

        <button id="btn-progreso" class="menu-item">
            <span class="icon">📊</span>
            <span class="text">Grafica</span>
        </button>

        <button id="btn-bienestar" class="menu-item">
            <span class="icon">🧘</span>
            <span class="text">Bienestar</span>
        </button>

        <button id="btn-confi" class="menu-item">
            <span class="icon">⚙️</span>
            <span class="text">Ajustes</span>
        </button>

        <!-- Cerrar Sesión vía PHP -->
        <form method="POST" action="logout.php" style="margin:0;">
            <button type="submit" class="menu-item" style="width:100%; text-align:left;">
                <span class="icon">🚪</span>
                <span class="text">Cerrar Sesión</span>
            </button>
        </form>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <button id="btn-eye-care" class="btn-rest">
            <span class="text">Modo Descanso</span>
        </button>

        <input type="text" class="search-bar" placeholder="Buscar...">

        <div class="hero-module">
            <div class="hero-container" style="display:flex; justify-content:space-between; align-items:center;">

                <div class="hero-left">
                    <!-- Saludo personalizado con nombre de la sesión PHP -->
                    <h2 id="welcome-title">
                        ¡Bienvenido, <?= htmlspecialchars($nombre_usuario) ?>!
                    </h2>

                    <div style="display:flex; align-items:center; gap:15px;">
                        <iframe src="lampara.html"
                                style="width:120px; height:140px; border:none; background:transparent;"
                                scrolling="no">
                        </iframe>
                        <div>
                            <p id="daily-proverb"
                               style="font-style:italic; margin:0; opacity:0; transition:opacity 0.3s ease;">
                            </p>
                            <small style="color:#888;">Haz clic en la lámpara para iluminar tu día.</small>
                        </div>
                    </div>
                </div>

                <!-- CALENDARIO -->
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

        <div class="cards-grid">
            <div class="mini-card">Card 1</div>
            <div class="mini-card">Card 2</div>
            <div class="mini-card">Card 3</div>
        </div>
    </main>
</div>

<?php endif; ?>

<!-- SCRIPTS -->
<script src="script.js"></script>
<script src="dashboard-interactivo.js"></script>
<script src="navegación.js"></script>
<script src="forgot-password.js"></script>

</body>
</html>