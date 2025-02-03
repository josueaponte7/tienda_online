const redis = require('redis');
const { createServer } = require('http');
const { Server } = require('socket.io');
const amqp = require('amqplib');

// Configuración del servidor HTTP y Socket.IO
const httpServer = createServer();
const io = new Server(httpServer, {
    cors: {
        origin: ["http://localhost", "http://tienda-online.local"],
        methods: ["GET", "POST"],
        allowedHeaders: ["my-custom-header"],
        credentials: true
    }
});

// Evento de conexión de Socket.IO
io.on('connection', (socket) => {
    console.log('Nuevo cliente conectado:', socket.id);

    socket.on('disconnect', () => {
        console.log('Cliente desconectado:', socket.id);
    });
});

// === Configuración de Redis ===
const subscriber = redis.createClient({
    url: 'redis://127.0.0.1:6379'
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
        await subscriber.connect();
        console.log('Cliente Redis conectado.');

        // Suscribirse al canal de notificaciones
        await subscriber.subscribe('user-notifications', (message) => {
        
            // Emitir mensaje estructurado a través de Socket.IO
            io.emit('notification', {
                type: 'redis',
                message: message
            });
        });
    } catch (err) {
        console.error('Error al conectar con Redis:', err);
    }
})();

// === Configuración de RabbitMQ ===
async function connectRabbitMQ() {
    try {
        const connection = await amqp.connect('amqp://guest:guest@localhost:5672');
        const channel = await connection.createChannel();

        // Asegurar que la cola existe
        await channel.assertQueue('user-notifications');

        // Consumir mensajes de RabbitMQ
        channel.consume('user-notifications', (msg) => {
            if (msg !== null) {
                let data;
                try {
                    data = JSON.parse(msg.content.toString());
                    console.log(`RABBITMQ: Mensaje recibido en la cola user-notifications:`, data.message);

                    // Emitir mensaje estructurado a través de Socket.IO
                    io.emit('notification', {
                        type: 'rabbitmq',
                        message: data.message || 'Sin contenido'
                    });

                    channel.ack(msg);
                } catch (error) {
                    console.error('Error al parsear el mensaje de RabbitMQ:', error);
                }
            }
        });

        console.log('Conectado a RabbitMQ y escuchando mensajes en user-notifications');
    } catch (error) {
        console.error('Error al conectar con RabbitMQ:', error);
    }
}
connectRabbitMQ();

// === Iniciar el servidor ===
httpServer.listen(3000, () => {
    console.log('Servidor WebSocket corriendo en http://localhost:3000');
});
