import '../morphdom/morphdom-umd.min.js';
import {load} from "./wire-directives.js";

class stream {

	constructor(identifier) {
		this.container = 'fragment';
		this.component = document.querySelector('[data-component="'+ identifier +'"]');
		this.identifier = identifier;
		this.token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
	}

	wire(directive, callback, externals = []) {
		if (!this.component)
			return;

		const baseSelector = this.escape(directive);

		if (externals.length) {
			this.wire(directive, callback);

			const combinations = this.getCombinationsOnly(externals
				.filter(val => this.component.outerHTML.toLowerCase().includes(val.toLowerCase()))
				.map(val => '.' + val));

			combinations.forEach(mods => {
				const suffix = mods.length ? mods.join('') : '';
				const fullDirective = directive + suffix;
				const selector = this.escape(fullDirective);

				this.getScopedElements(selector).forEach(element => {
					this.perform({
						element: element,
						directive: fullDirective,
						fragment: this.component,
						identifier: this.identifier,
						expression: element.getAttribute(fullDirective)
					}, callback);
				});
			});
		} else {
			this.getScopedElements(baseSelector).forEach(element =>
				this.perform({
					element: element,
					directive: directive,
					fragment: this.component,
					identifier: this.identifier,
					expression: element.getAttribute(directive)
				}, callback)
			);
		}
	}

	submit(payload, target, overwrite = 0, skipRequest = false) {
		if (!this.component)
			return;

		const models = {};
		const compiled = {};
		const ex_loads = {};
		const form = new FormData();
		const previousIdentifier = this.identifier;

		// Update target component and identifier if provided
		if (target) {

			// Fetch external payloads for target request
			for (const [directive, names] of Object.entries(JSON.parse(this.component.getAttribute('data-exopayloads') || '{}'))) {
				names.forEach(name => {
					const model = this.component.querySelector(`[${CSS.escape(directive)}='${name}']`);
					models[name] = model.value;
				});
			}

			this.component = document.querySelector('[data-component="'+ target +'"]');
			this.identifier = target;
		}

		let response = null;
		let compiledComponents = this.getAllDataComponentElements(this.component);
		let properties = this.component.getAttribute('data-properties');
		let payloads = JSON.parse(this.component.getAttribute('data-payloads') || '{}');

		// Initial trigger(s)
		if (target) {
			this.trigger({ status: false, response, duration: 0 }, previousIdentifier);
		}

		this.trigger({ status: false, response, duration: 0 }, target, 'wire-processing');
		this.trigger({ status: false, response, duration: 0 });

		// Capture compiled fragments
		this.component.querySelectorAll(this.container).forEach(fragment => {
			const comp = fragment.getAttribute("data-component");
			compiled[comp] = fragment.outerHTML;
		});

		// Collect models from payloads
		for (const [directive, names] of Object.entries(payloads)) {
			names.forEach(name => {
				const model = this.component.querySelector(`[${CSS.escape(directive)}='${name}']`);
				models[name] = model.value;
			});
		}

		// Append payload to FormData
		for (const [key, value] of Object.entries(payload)) {
			form.append(key, value);
		}

		// Append to default payload
		if (models) {
			for (let key in models) {
				form.append(key, models[key]);
			}
		}

		// Append meta info to FormData
		form.append('_component', this.identifier);
		form.append('_properties', properties);
		form.append('_models', JSON.stringify({...models, ...ex_loads}));
		form.append('_compiled', JSON.stringify(compiled));

		// Submit via fetch
		return new Promise((resolve, reject) => {
			let response = null;
			let aborted = false;
			let isJson = false;
			let timeStarted = performance.now();

			// Abort previous request if still running
			if (this.currentController) {
				this.currentController.abort();
			}

			// Create and store new controller
			const controller = new AbortController();
			this.currentController = controller;

			// Final request handler
			const rebuild = () => {
				let totalMs = performance.now() - timeStarted;

				if (aborted) return;

				if (target)
					this.trigger({ status: true, response: response, duration: totalMs }, previousIdentifier);

				this.trigger({ status: true, response: response, duration: totalMs });
				this.trigger({ status: true, response: response, duration: totalMs }, target, 'wire-processing');

				this.recompile(compiledComponents, response);
				this.component.setAttribute('data-payloads', JSON.stringify(payloads));

				if (target) {
					setTimeout(() => {
						this.component = document.querySelector('[data-component="' + previousIdentifier + '"]');
						this.identifier = previousIdentifier;
					}, 0);
				}

				resolve({ status: true, response: response, duration: totalMs });
			};

			if (skipRequest) {
				rebuild();
				return;
			}

			fetch(`/api/stream-wire/${this.identifier}`, {
				method: "POST",
				headers: {
					"X-STREAM-WIRE": true,
					"X-CSRF-TOKEN": this.token,
					"HTTP_X_REQUESTED_WITH": 'xmlhttprequest'
				},
				body: form,
				signal: controller.signal
			})
				.then(res => {
					const contentType = res.headers.get("Content-Type") || "";

					if (!res.ok) {
						console.error(
							`%c❌ HTTP ERROR! %cStatus: ${res.status} 🚫`,
							'color: red; font-weight: bold;',
							'color: orange;'
						);

						if (res.status === 500 && contentType.includes("text/html")) {
							return res.text().then(errorHtml => {
								this.component.innerHTML += errorHtml;
								resolve(null);
								return null;
							});
						}

						resolve(null);
						return null;
					}

					if (contentType.includes("application/json")) {
						isJson = true;
						return res.json();
					} else if (contentType.includes("text/html")) {
						return res.text();
					} else {
						console.warn("⚠️ Unknown content type:", contentType);
						resolve(null);
						return null;
					}
				})
				.then(data => {
					const performMorph = (oldElement, newElement) => {
						morphdom(oldElement, newElement, {
							getNodeKey: node => (node.nodeType === 1 ? node.getAttribute("data-key") || node.getAttribute("data-component") || node.id : null),
							onBeforeElUpdated: (fromEl, toEl) => !fromEl.isEqualNode(toEl),
							onBeforeNodeDiscarded: () => true
						});
					};

					if (isJson) {
						if (data.redirect !== undefined) {
							window.location.href = data.redirect;
						} else {
							let newContent = data.content;
							let extender = data.extender || {};

							if (!newContent) {
								console.warn("Updated component not found in response.");
							}

							switch (overwrite) {
								case 1:
									this.hardSwap(this.component, newContent);
									break;
								case 2:
									performMorph(this.component, newContent);
									this.executeScriptsIn(this.component, false);
									break;
								case 3:
									performMorph(this.component, newContent);
									this.executeScriptsIn(this.component, true, false);
									break;
								default:
									performMorph(this.component, newContent);
									break;
							}

							response = newContent;
							if (Array.isArray(extender)) {
								(async () => {
									for (const item of extender) {
										const target = item.target;
										const action = item.method;
										const target_identifier = document.querySelector(`[data-id="${target}"]`);
										if (target_identifier) {
											const target_component = target_identifier.getAttribute('data-component');
											const instance = await StreamListener(target_component);
											await instance.submit({'_method': action }, false, 3);
										}
									}
								})();
							}
						}
					}
				})
				.catch(error => {
					if (error.name === 'AbortError') {
						aborted = true;
						resolve({ status: true, response: null, duration: 0 });
					} else {
						console.error("Error submitting request:", error);
						reject(error);
					}
				})
				.finally(() => rebuild());
		});
	}

	// Adds a payload name under a directive key in the component's [data-payloads] attribute.
	payload(directive, name, external = false) {
		const el = this.component;
		if (!el) return;

		let modelKey = 'data-payloads';
		if (external)
			modelKey = 'data-exopayloads';

		const currentPayloads = JSON.parse(el.getAttribute(modelKey) || '{}');

		if (currentPayloads[directive] === undefined)
			currentPayloads[directive] = [];

		currentPayloads[directive].push(name);
		el.setAttribute(modelKey, JSON.stringify(currentPayloads));
	}

	// Finds an element with the specified attribute and retrieves its data-component ID,
	// then invokes a callback with the found identifier (or false if not found).
	findTheID(element, search, callback) {
		if (element.hasAttribute(search)) {
			const targetID = element.getAttribute(search);
			const targetElement = document.querySelector(`[data-id="${targetID}"]`);

			if (targetElement && targetElement.hasAttribute('data-component')) {
				const identifier = targetElement.getAttribute('data-component');
				callback(identifier);
				return;
			}
		}

		callback(false);
	}

	// Escapes special characters (: and .) in a string for use in CSS selectors.
	// Optionally wraps the result in brackets unless skipBracket is true.
	escape(str, skipBracket = false) {
		const escaped = str
			.replace(/:/g, '\\:')
			.replace(/\./g, '\\.');

		if (skipBracket)
			return escaped;

		return `[${escaped}]`;
	}

	// Executes a given function with parameters mapped from an object.
	// The parameter names are extracted from the function definition.
	perform(params, action) {
		const paramNames = this.getParamNames(action);
		const args = paramNames.map(name => params[name]);

		action(...args);
	}

	// Compares two sets of [data-component] elements and executes scripts in those that have changed.
	recompile(compiled, updated) {
		const parser = new DOMParser();
		const doc = parser.parseFromString(updated, 'text/html');
		const modified = doc.querySelectorAll('[data-component]');

		const modifiedValues = Array.from(modified).map(el => el.getAttribute('data-component'));
		const originalValues = Array.from(compiled).map(el => el.getAttribute('data-component'));

		const unique = [
			...modifiedValues.filter(val => !originalValues.includes(val)),
			...originalValues.filter(val => !modifiedValues.includes(val))
		];

		unique.forEach(identifier => {
			const isExist = document.querySelector('[data-component="'+ identifier +'"]');
			if (isExist)
				this.executeScriptsIn(isExist);
		});
	}

	// Replaces an old DOM element with a new one parsed from an HTML string,
	// and executes any scripts found inside the new element.
	hardSwap(oldEl, html) {
		const tpl = document.createElement('template');
		tpl.innerHTML = html.trim();
		const newEl = tpl.content.firstElementChild;

		oldEl.replaceWith(newEl);
		this.executeScriptsIn(newEl);
		return newEl;
	}

	// Respects container scoping and allows skipping fragments by ID.
	executeScriptsIn(container, includeFragment = true, includeScript = true) {
		const scripts = container.querySelectorAll('script');

		scripts.forEach(script => {
			// Skip script if it's outside the target container
			const parentFragment = script.closest(this.container);
			if (parentFragment && parentFragment !== container) return;

			const isFragmentScript = script.id === `__${this.container}__`;

			// Skip fragment scripts if includeFragment is false
			if (isFragmentScript && !includeFragment) return;

			// Skip standard scripts if includeScript is false
			if (!isFragmentScript && !includeScript) return;

			// Clone the script
			const newScript = document.createElement('script');

			if (script.src) {
				newScript.src = script.src;
			} else {
				newScript.textContent = script.textContent;
			}

			// Copy all attributes from the original script
			Array.from(script.attributes).forEach(attr => {
				newScript.setAttribute(attr.name, attr.value);
			});

			// Replace the old script with the new one to trigger execution
			script.parentNode.replaceChild(newScript, script);
		});
	}

	// Retrieves all elements with the [data-component] attribute under the given root.
	// If the root itself has [data-component], it is included at the start of the result.
	getAllDataComponentElements(root) {
		const elements = Array.from(root.querySelectorAll('[data-component]'));
		if (root.hasAttribute('data-component')) {
			elements.unshift(root);
		}
		return elements;
	}

	// Finds all elements matching the selector within the component,
	// excluding those that are nested inside a specific container tag.
	getScopedElements(selector) {
		const root = this.component;
		if (!root) return [];

		const excludeTag = this.container;
		const elements = root.querySelectorAll(selector);

		return Array.from(elements).filter(el => {
			let current = el.parentElement;
			while (current && current !== root) {
				if (current.tagName.toLowerCase() === excludeTag.toLowerCase()) {
					return false;
				}
				current = current.parentElement;
			}
			return true;
		});
	}

	// Extracts parameter names from a function as an array of strings.
	getParamNames(func) {
		const fnStr = func.toString().replace(/\/\/.*$|\/\*[\s\S]*?\*\//gm, ''); 	// Remove comments
		const result = fnStr.slice(fnStr.indexOf('(') + 1, fnStr.indexOf(')')).match(/([^\s,]+)/g);
		return result === null ? [] : result;
	}

	// Generates all non-empty combinations of elements from an array.
	getCombinationsOnly(array) {
		const results = [];

		const recurse = (prefix, rest) => {
			if (prefix.length > 0) results.push([...prefix]);
			for (let i = 0; i < rest.length; i++) {
				recurse([...prefix, rest[i]], rest.slice(i + 1));
			}
		};

		recurse([], array);
		return results;
	}

	// Converts a string into a positive integer hash code (used for event identifiers).
	stringToIntId(str) {
		let hash = 0;
		for (let i = 0; i < str.length; i++) {
			hash = (hash << 5) - hash + str.charCodeAt(i);
			hash |= 0; // Convert to 32bit integer
		}
		return Math.abs(hash);
	}

	// Subscribes a callback to a custom event using a hashed identifier.
	ajax(callback, identifier = '') {
		let target = this.identifier;
		if (identifier)
			target = identifier;

		window.addEventListener(
			`wire-loader-${this.stringToIntId(target)}`,
			(event) => callback(event.detail)
		);
	}

	// Dispatches a custom event with data using a hashed identifier.
	trigger(data, identifier = '', customKey = 'wire-loader') {
		let target = this.identifier;
		if (identifier)
			target = identifier;

		let key = `${customKey}-${this.stringToIntId(target)}`;
		window.dispatchEvent(new CustomEvent(key, { detail: data }));
	}

	// Initializes the stream component by calling a global load function with a new stream instance.
	static init(component) {
		load(new stream(component));
	}
}

export function construct(id) {
	return new stream(id);
}

export default function init(id) {
	return stream.init(id);
}
