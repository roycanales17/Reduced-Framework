import {construct} from "./stream-wire.js";

class StreamListener {

	static registeredListener = {};

	constructor(id) {
		this.id = id;
		this.stream = construct(this.id);
	}

	processing(callback) {
		const int = (str) => {
			let hash = 0;
			for (let i = 0; i < str.length; i++) {
				hash = (hash << 5) - hash + str.charCodeAt(i);
				hash |= 0;
			}

			return Math.abs(hash);
		}
		const key = `wire-processing-${int(this.id)}`;

		if (!StreamListener.registeredListener[key]) {
			StreamListener.registeredListener[key] = (event) => callback(event.detail);
			window.addEventListener(key, StreamListener.registeredListener[key]);
		}
	}

	submit(payload, target, overwrite = 0) {
		return this.stream.submit(payload, target, overwrite)
	}

	execute(action, params = [], overwrite = 0) {
		let payload = {};
		payload['_method'] = `${action}(${params.join(', ')})`;
		return this.stream.submit(payload, '', overwrite)
	}

	target(action, params = [], target, overwrite = 0) {
		let payload = {};
		payload['_method'] = `${action}(${params.join(', ')})`;
		return this.stream.submit(payload, target, overwrite)
	}

	static init(id) {
		return new StreamListener(id);
	}
}

// Export a function that takes an ID and returns an instance
export default function init(id) {
	return StreamListener.init(id);
}