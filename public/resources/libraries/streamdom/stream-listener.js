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

	submit(payload, target) {
		return this.stream.submit(payload, target)
	}

	static init(id) {
		return new StreamListener(id);
	}
}

export default function init(id) {
	return StreamListener.init(id);
}