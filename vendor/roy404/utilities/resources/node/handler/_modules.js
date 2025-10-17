/**
 * Barrel file that exports all local modules in the `handler` directory.
 */
module.exports = {
    Logger: require("./logger"),
    routes: require("./routes"),
    connections: require("./connections"),
};
