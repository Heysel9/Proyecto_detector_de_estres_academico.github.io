// 1. CARGAR VARIABLES DE ENTORNO (Debe ser lo primero)
require('dotenv').config(); 

const express = require('express');
const cors = require('cors');
const bcrypt = require('bcrypt');
const pool = require('./db'); // Aquí ya van los datos de Railway cargados
const app = express();

// 2. MIDDLEWARES
app.use(cors());
app.use(express.json());

// 3. RUTA DE REGISTRO
app.post('/registro', async (req, res) => {
    // IMPORTANTE: Asegúrate de que desde el frontend envíes 'nombre'
    const { nombre, email, password } = req.body; 
    
    try {
        // Encriptamos la contraseña
        const salt = await bcrypt.genSalt(10);
        const hashedPass = await bcrypt.hash(password, salt);
        
        // Insertamos en la tabla 'usuarios'
        const nuevoUsuario = await pool.query(
            "INSERT INTO usuarios (nombre_completo, email, password_hash) VALUES ($1, $2, $3) RETURNING *",
            [nombre, email, hashedPass]
        );
        
        res.json({ mensaje: "¡Cuenta creada con éxito!", usuario: nuevoUsuario.rows[0] });
    } catch (err) {
        console.error("Error en servidor:", err.message);
        res.status(500).json({ error: "Error al registrar: " + err.message });
    }
});

// 4. PUERTO DINÁMICO (Para Railway)
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Servidor de Equilibrio Académico corriendo en puerto: ${PORT}`);
});