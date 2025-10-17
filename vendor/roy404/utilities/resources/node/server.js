// -----------------------------------------------------------------------------
// Load Environment Variables
// -----------------------------------------------------------------------------
require("dotenv").config();

// -----------------------------------------------------------------------------
// Module Imports
// -----------------------------------------------------------------------------
// Core/Third-party modules
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

// Local modules (via barrel file)
const { Logger, routes, connections } = require("./handler/_modules");

// -----------------------------------------------------------------------------
// Server Configuration
// -----------------------------------------------------------------------------
const app = express();
const port = process.env.SOCKET_PORT || 3000;
const server = http.createServer(app);

// Logger instance
const logger = new Logger({
    level: Logger.LEVELS.INFO,
    logDir: "logs",
    fileName: "socket.log"
});

// Socket.IO with CORS
const io = new Server(server, {
    cors: { origin: "*", methods: ["GET", "POST"] }
});

// -----------------------------------------------------------------------------
// HTTP Routes
// -----------------------------------------------------------------------------
routes(app, process);

// -----------------------------------------------------------------------------
// Socket.IO Connections
// -----------------------------------------------------------------------------
io.on("connection", (socket) => {
    logger.info(`Client connected: ${socket.id}`);
    connections(socket, logger);
});

// -----------------------------------------------------------------------------
// Error Handling
// -----------------------------------------------------------------------------
io.engine.on("connection_error", (err) => {
    logger.error(`Connection error: code=${err.code}, message=${err.message}`);
});

server.on("error", (err) => {
    logger.error(`HTTP Server error: ${err.message}`);
});

process.on("uncaughtException", (err) => {
    logger.error(`Uncaught exception: ${err.stack || err.message}`);
});

process.on("unhandledRejection", (reason, p) => {
    logger.error(`Unhandled rejection: ${reason} (Promise: ${p})`);
});

// -----------------------------------------------------------------------------
// Graceful Shutdown
// -----------------------------------------------------------------------------
process.on("SIGINT", () => {
    logger.info("Shutting down server...");
    server.close(() => {
        logger.info("Server closed");
        process.exit(0);
    });
});

// -----------------------------------------------------------------------------
// Start Server
// -----------------------------------------------------------------------------
server.listen(port, () => {
    logger.info(`Socket.IO server running on port ${port}`);
});
