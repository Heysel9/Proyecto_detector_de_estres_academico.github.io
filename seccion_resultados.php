<?php
/**
 * SECCIÓN DE RESULTADOS DINÁMICOS - SIBE
 * Este archivo procesa los mensajes que vienen desde guardar_test.php
 */

// 1. Atrapamos los datos de la URL y limpiamos el formato (+ por espacios, % por tildes)
$mensaje_final = isset($_GET['m']) ? urldecode($_GET['m']) : null;
$accion_final  = isset($_GET['a']) ? urldecode($_GET['a']) : null;
$es_exito      = isset($_GET['success']) && $_GET['success'] === 'true';

// Mensajes por defecto si la URL viene vacía (solo por seguridad)
if (!$mensaje_final) {
    $mensaje_final = "Tu perfil de bienestar ha sido actualizado con éxito.";
}
if (!$accion_final) {
    $accion_final = "El SIBE ya está procesando tus datos para el historial.";
}
?>

<div style="
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center; 
    height: 100%; 
    min-height: 500px;
    animation: fadeIn 0.8s ease-out;">

    <div style="
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 30px;
        padding: 50px;
        text-align: center;
        max-width: 550px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);">
        
        <div style="
            font-size: 4rem; 
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.8));">
            ✨
        </div>

        <h2 style="
            color: #1e293b; 
            font-size: 2rem; 
            font-weight: 800; 
            margin-bottom: 15px;
            letter-spacing: -0.5px;">
            <?php echo $es_exito ? '¡Evaluación Guardada!' : 'Resultados SIBE'; ?>
        </h2>

        <div style="color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 35px;">
            
            <div style="background: rgba(255,255,255,0.4); padding: 20px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.5);">
                
                <p style="font-weight: 800; color: #1e293b; margin-bottom: 15px; font-size: 1.2rem;">
                    <?php echo htmlspecialchars($mensaje_final); ?>
                </p>
                
                <div style="font-size: 1rem; color: #334155; padding: 15px; background: rgba(59, 130, 246, 0.1); border-radius: 15px; text-align: left;">
                    <span style="color: #2563eb; font-weight: bold;">💡 Sugerencia:</span><br>
                    <span style="font-style: italic;"><?php echo htmlspecialchars($accion_final); ?></span>
                </div>

            </div>
        </div>

        <a href="index.php" style="
            display: inline-block;
            background: linear-gradient(135deg, #6b46c1 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);">
            Volver al Inicio
        </a>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>