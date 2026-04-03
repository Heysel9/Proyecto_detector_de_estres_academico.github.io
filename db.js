// Archivo de conexión - StressAlert
// Por ahora, la conexión está desactivada para evitar errores en Railway.

/* const { Pool } = require('pg');

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: {
    rejectUnauthorized: false
  }
});

module.exports = pool; 
*/

// Exportamos un objeto vacío temporalmente para que server.js no explote al importar
module.exports = {};