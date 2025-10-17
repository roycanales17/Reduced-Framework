class Bootstrap {

    static CONNECTIONS = new Set();

    /**
     * @param {import("socket.io").Socket} socket - Connected socket instance
     * @param {Object} logger - Logger instance
     */
    constructor(socket, logger) {
        this.socket = socket;
        this.logger = logger;
        this.events = new Map();
    }

    listen(event, handler) {
        if (this.events.has(event)) {
            this.logger.warn(`Event '${event}' already registered.`);
            return;
        }

        const wrappedHandler = this.#safeHandler(event, handler);
        this.socket.on(event, wrappedHandler);
        this.events.set(event, wrappedHandler);
        this.logger.debug(`Listening to event: '${event}'`);
    }

    trigger(event, data, options = {}) {
        const { broadcast = false } = options;

        if (!this.events.has(event)) {
            this.logger.debug(`Emitting unregistered event '${event}'`);
        }

        if (broadcast && this.socket.broadcast) {
            this.socket.broadcast.emit(event, data);
        } else {
            this.socket.emit(event, data);
        }

        this.logger.debug(
            `${broadcast ? "Broadcasted" : "Triggered"} event '${event}' with data:`,
            data
        );
    }

    addConnection(id) {
        Bootstrap.CONNECTIONS.add(id);
        this.logger.info(`Client connected: ${id}. Total: ${Bootstrap.CONNECTIONS.size}`);
    }

    removeConnection(id) {
        Bootstrap.CONNECTIONS.delete(id);
        this.logger.info(`Client disconnected: ${id}. Total: ${Bootstrap.CONNECTIONS.size}`);
    }

    static getConnectionCount() {
        return Bootstrap.CONNECTIONS.size;
    }

    #safeHandler(event, handler) {
        return async (...args) => {
            try {
                await handler(...args);
            } catch (err) {
                this.logger.error(
                    `Unhandled error in socket event '${event}': ${err.stack || err.message}`
                );
                this.trigger('server_error', {
                    code: 500,
                    message: err.message,
                });
            }
        };
    }

    dispose() {
        for (const [event, handler] of this.events.entries()) {
            this.socket.off(event, handler);
        }
        this.events.clear();
        this.logger.info("Bootstrap disposed: all events and socket listeners cleared.");
    }
}

module.exports = Bootstrap;
