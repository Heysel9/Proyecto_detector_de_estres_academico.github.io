const express = require('express');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

// Ruta de prueba para que tu formulario no de error 404
app.post('/registro', (req, res) => {
    console.log("Datos recibidos en el servidor:", req.body);
    res.json({ mensaje: "El servidor recibió los datos, pero la base de datos está desactivada." });
});

// Ruta básica para ver si el servidor está vivo
app.get('/', (req, res) => {
    res.send("Servidor de StressAlert funcionando sin base de datos.");
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Servidor corriendo en puerto: ${PORT}`);
});