function stream(identifier) {
	const getModule = (() => {
		let modulePromise;
		return () => {
			if (!modulePromise) {
				modulePromise = import("../streamdom/stream-wire.js");
			}
			return modulePromise;
		};
	})();

	return getModule().then(module => module.default(identifier));
}

function StreamListener(identifier = '') {
	const getModule = (() => {
		let modulePromise;
		return () => {
			if (!modulePromise) {
				modulePromise = import("../streamdom/stream-listener.js");
			}
			return modulePromise;
		};
	})();

	return getModule().then(module => module.default(identifier));
}