const Bootstrap = require("./bootstrap");

/**
 * Socket.IO event handlers for each client connection.
 *
 * @param {import("socket.io").Socket} socket - The connected socket instance
 * @param {Object} logger - Custom logger instance
 */
const connections = (socket, logger) => {
    // Create a Bootstrap instance for this socket
    const bootstrap = new Bootstrap(socket, logger);

    // -------------------------------------------------------------------------
    // Register new connection
    // -------------------------------------------------------------------------
    bootstrap.addConnection(socket.id);

    // -------------------------------------------------------------------------
    // Event: "message"
    // -------------------------------------------------------------------------
    bootstrap.listen("message", async (msg) => {
        if (typeof msg !== "string") {
            throw new Error(`Invalid message type from ${socket.id}: ${typeof msg}`);
        }

        // Echo the message back to the client
        bootstrap.trigger("message", `Server echo: ${msg}`);
    });

    // -------------------------------------------------------------------------
    // Event: "disconnect"
    // -------------------------------------------------------------------------
    socket.on("disconnect", (reason) => {
        bootstrap.removeConnection(socket.id);

        if (reason === "io client disconnect") {
            logger.info(`Client ${socket.id} disconnected normally.`);
        } else {
            logger.warn(`Client ${socket.id} disconnected unexpectedly: ${reason}`);
        }

        bootstrap.dispose();
    });

    // -------------------------------------------------------------------------
    // Event: Catch-All (any event not explicitly handled)
    // -------------------------------------------------------------------------
    socket.onAny((event, ...args) => {
        if (!bootstrap.events.has(event)) {
            logger.warn(
                `Unhandled event "${event}" from ${socket.id} with args: ${JSON.stringify(args)}`
            );
        }
    });
};

module.exports = connections;
