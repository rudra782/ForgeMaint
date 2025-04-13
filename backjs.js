const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const path = require('path');

const app = express();
const server = http.createServer(app);
const io = socketIo(server);

// Serve frontend files from /public
app.use(express.static(path.join(__dirname, 'public')));

// Socket.IO logic
io.on('connection', (socket) => {
  console.log('A client connected');

  const sendUpdates = () => {
    const healthData = {
      healthy: Array(6).fill().map(() => Math.floor(Math.random() * 20 + 10)),
      warning: Array(6).fill().map(() => Math.floor(Math.random() * 6)),
      critical: Array(6).fill().map(() => Math.floor(Math.random() * 3))
    };

    const total = 100;
    const low = Math.floor(Math.random() * 50 + 30);
    const medium = Math.floor(Math.random() * 20 + 10);
    const high = total - low - medium;

    socket.emit('update', {
      healthData,
      failureData: [low, medium, high]
    });
  };

  const interval = setInterval(sendUpdates, 5000);
  sendUpdates(); // send initial

  socket.on('disconnect', () => {
    clearInterval(interval);
    console.log('Client disconnected');
  });
});

// Start server
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
