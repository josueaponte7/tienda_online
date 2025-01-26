const redis = require('redis');
const { createServer } = require('http');
const { Server } = require('socket.io');

// Configuración del servidor HTTP y Socket.IO
const httpServer = createServer();
const io = new Server(httpServer, {
    cors: {
        origin: "http://tienda-online.local", // Tu dominio local
        methods: ["GET", "POST"]
    }
});

// Configuración de Redis
const subscriber = redis.createClient({
    url: 'redis://127.0.0.1:6379' // Usamos la URL de conexión
});

// Manejo de eventos de conexión de Redis
subscriber.on('connect', () => {
    console.log('Conectado a Redis correctamente.');
});

subscriber.on('error', (err) => {
    console.error('Error en la conexión con Redis:', err);
});

// Conectar al cliente de Redis
(async () => {
    try {
        await subscriber.connect(); // Conectar explícitamente
        console.log('Cliente Redis conectado.');

        // Suscribirse al canal después de conectarse
        await subscriber.subscribe('user-notifications', (message) => {
            console.log(`Mensaje recibido en el canal user-notifications: ${message}`);
            io.emit('notification', message);
        });
    } catch (err) {
        console.error('Error al conectar con Redis:', err);
    }
})();

// Inicia el servidor
httpServer.listen(3000, () => {
    console.log('Servidor WebSocket corriendo en http://localhost:3000');
});
