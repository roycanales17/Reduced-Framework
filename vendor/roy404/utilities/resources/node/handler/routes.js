const Bootstrap = require("./bootstrap");

function routes(app) {

    // Health Check / Status Page
    app.get("/", (req, res) => {
        res.send("✅ Socket.IO server is running");
    });

    // Health check (used by Docker/K8s probes)
    app.get("/health", (req, res) => {
        res.send("ok");
    });

    // Info / diagnostics
    app.get("/info", (req, res) => {
        res.json({
            app: process.env.APP_NAME || "Socket Server",
            env: process.env.APP_ENV || "development",
            port: process.env.SOCKET_PORT || 3000,
            uptime: process.uptime()
        });
    });

    // ✅ Total active connections
    app.get("/connections", (req, res) => {
        res.json({
            total_connections: Bootstrap.getConnectionCount()
        });
    });
}

module.exports = routes;
