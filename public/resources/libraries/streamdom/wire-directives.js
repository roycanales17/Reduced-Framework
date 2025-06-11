export function load(stream)
{
	stream.wire('wire:model', function(element, directive, expression) {
		element.addEventListener('change', function () {
			if (element.type === 'checkbox') {
				element.value = element.checked ? '1' : '0';
			}
		});
		stream.payload(directive, expression);
	});

	stream.wire('wire:x-model', function(directive, expression) {
		stream.payload(directive, expression, true);
	});

	stream.wire('wire:click', function(element, expression, directive, identifier) {
		element.addEventListener('click', (e) => {
			let overwrite = 0;
			if (directive.includes('.prevent'))
				e.preventDefault();

			if (directive.includes('.refresh'))
				overwrite = 1;

			if (directive.includes('.rebind'))
				overwrite = 2;

			if (directive.includes('.inactive') && element.classList.contains('active'))
				return;

			stream.findTheID(element, 'wire:target', function(target) {
				if (target)
					identifier = target;

				stream.submit({'_method': expression}, identifier, overwrite);
			});
		});
	}, ['prevent', 'refresh', 'rebind', 'inactive']);

	stream.wire('wire:submit', function(element, expression, directive) {
		element.addEventListener("submit", (e) => {
			let overwrite = 0;
			if (directive.includes('.prevent'))
				e.preventDefault();

			if (directive.includes('.refresh'))
				overwrite = 1;

			if (directive.includes('.rebind'))
				overwrite = 2;

			stream.submit({'_method': expression}, false, overwrite);
		});
	}, ['prevent', 'refresh', 'rebind']);

	stream.wire('wire:keydown.keypress', function (element, expression, directive, identifier) {
		let debounceTimer = null;

		element.addEventListener('input', (e) => {
			if (debounceTimer)
				clearTimeout(debounceTimer);

			let activeEl = document.activeElement;
			let value = e.target.value;
			let action = expression;
			let overwrite = 0;

			if (action.includes("event.target.value"))
				action = action.replace("event.target.value", `'${value}'`);

			if (directive.includes('.refresh'))
				overwrite = 1;

			if (directive.includes('.rebind'))
				overwrite = 2;

			const delays = {
				'100ms': 100,
				'300ms': 300,
				'500ms': 500,
				'1000ms': 1000,
				'1300ms': 1300,
				'1500ms': 1500,
				'2000ms': 2000
			};

			const matchedDelay = Object.keys(delays).find(key => directive.includes(key));
			const perform = () => {
				stream.findTheID(element, 'wire:target', function(target) {
					if (target)
						identifier = target;

					stream.submit({ '_method': action }, identifier, overwrite);
					stream.ajax((res) => {
						const hasTarget = element.getAttribute('wire:target');
						if (directive.includes('.clear')) element.value = '';

						if (res.status && res.duration >= 1000 && !hasTarget) {
							if (activeEl === element) element.focus();
							return;
						}

						if (res.status) {
							const escapedAttr = stream.escape(directive, true);
							const selector = `${escapedAttr}="${expression}"`;
							const newEl = document.querySelector(`[${selector}]`);
							if (newEl)
								newEl.focus();
						}
					}, identifier);
				});
			};

			if (matchedDelay) {
				debounceTimer = setTimeout(() => perform(), delays[matchedDelay]);
			} else {
				perform()
			}
		});
	}, ['100ms', '300ms', '500ms', '1000ms', '1300ms', '1500ms', '2000ms', 'clear', 'refresh', 'rebind']);

	stream.wire('wire:keydown.enter', function (element, expression, directive, identifier) {
		element.addEventListener('keydown', (e) => {
			let hasTarget = element.getAttribute('wire:target');
			let activeEl = document.activeElement;
			let pressedKey = e.key.toLowerCase();
			let action = expression;
			let overwrite = 0;

			if (pressedKey === 'enter') {
				if (directive.includes('.prevent'))
					e.preventDefault();

				if (directive.includes('.refresh'))
					overwrite = 1;

				if (directive.includes('.rebind'))
					overwrite = 2;

				if (action.includes("event.target.value"))
					action = action.replace("event.target.value", `'${element.value}'`);

				stream.findTheID(element, 'wire:target', function(target) {
					if (target)
						identifier = target;

					stream.submit({'_method': action}, identifier, overwrite);
					stream.ajax(({ status }) => {
						if (status && directive.includes('.clear'))
							element.value = '';

						if (hasTarget) {
							const escapedAttr = stream.escape(directive, true);
							const selector = `${escapedAttr}="${expression}"`;
							const newEl = document.querySelector(`[${selector}]`);
							if (status && newEl)
								newEl.focus();
						} else {
							if (activeEl === element)
								element.focus();
						}
					}, identifier);
				});
			}
		});
	}, ['clear', 'prevent', 'refresh', 'rebind']);

	stream.wire('wire:keydown.escape', function (element, expression, directive) {
		element.addEventListener('keydown', (e) => {
			let pressedKey = e.key.toLowerCase();
			let action = expression;
			let overwrite = 0;

			if (pressedKey === 'escape') {
				if (directive.includes('.prevent'))
					e.preventDefault();

				if (directive.includes('.refresh'))
					overwrite = 1;

				if (directive.includes('.rebind'))
					overwrite = 2;

				if (action.includes("event.target.value"))
					action = action.replace("event.target.value", `'${element.value}'`);

				stream.submit({'_method': action}, false, overwrite);
				stream.ajax(({ status }) => {
					if (status && directive.includes('.clear'))
						element.value = '';
				});
			}
		});
	}, ['clear', 'prevent', 'refresh', 'rebind']);

	stream.wire('wire:keydown.backspace', function (element, expression, directive) {
		element.addEventListener('keydown', (e) => {
			let pressedKey = e.key.toLowerCase();
			let action = expression;
			let overwrite = 0;

			if (pressedKey === 'backspace') {
				if (directive.includes('.prevent'))
					e.preventDefault();

				if (directive.includes('.refresh'))
					overwrite = 1;

				if (directive.includes('.rebind'))
					overwrite = 2;

				if (action.includes("event.target.value"))
					action = action.replace("event.target.value", `'${element.value}'`);

				stream.submit({'_method': action}, false, overwrite);
			}
		});
	}, ['prevent', 'refresh', 'rebind']);

	stream.wire('wire:keydown.tab', function (element, expression, directive) {
		element.addEventListener('keydown', (e) => {
			let pressedKey = e.key.toLowerCase();
			let action = expression;
			let overwrite = 0;

			if (pressedKey === 'tab') {
				if (directive.includes('.prevent'))
					e.preventDefault();

				if (directive.includes('.refresh'))
					overwrite = 1;

				if (directive.includes('.rebind'))
					overwrite = 2;

				if (action.includes("event.target.value"))
					action = action.replace("event.target.value", `'${element.value}'`);

				stream.submit({'_method': action}, false, overwrite);
				stream.ajax(({ status }) => {
					if (status && directive.includes('.clear'))
						element.value = '';
				});
			}
		});
	}, ['clear','prevent', 'refresh', 'rebind']);

	stream.wire('wire:keydown.delete', function (element, expression, directive) {
		element.addEventListener('keydown', (e) => {
			let pressedKey = e.key.toLowerCase();
			let action = expression;
			let overwrite = 0;

			if (pressedKey === 'delete') {
				if (directive.includes('.prevent'))
					e.preventDefault();

				if (directive.includes('.refresh'))
					overwrite = 1;

				if (directive.includes('.rebind'))
					overwrite = 2;

				if (action.includes("event.target.value"))
					action = action.replace("event.target.value", `'${element.value}'`);

				stream.submit({'_method': action}, false, overwrite);
			}
		});
	}, ['prevent', 'refresh', 'rebind']);

	stream.wire('wire:loader', function (element, directive, expression) {
		stream.ajax(({ status }) => {

			if (directive.includes('.classList.add')) {
				if (!status) {
					element.classList.add(expression);
				} else {
					if (!directive.includes('retain'))
						element.classList.remove(expression);
				}
			}

			if (directive.includes('.classList.remove')) {
				if (!status) {
					element.classList.remove(expression);
				} else {
					if (!directive.includes('.retain'))
						element.classList.add(expression);
				}
			}

			if (directive.includes('.style')) {
				if (!status) {
					expression.split(';').forEach(style => {
						const [property, value] = style.split(':');
						if (property && value) {
							element.style[property.trim()] = value.trim();
						}
					});
				} else {
					if (!directive.includes('.retain')) {
						expression.split(';').forEach(style => {
							const [property] = style.split(':');
							if (property) {
								element.style.removeProperty(property.trim());
							}
						});
					}
				}
			}

			if (directive.includes('.attr')) {
				if (!status) {
					element.setAttribute(expression, true)
				} else {
					if (!directive.includes('.retain')) {
						element.removeAttribute(expression);
					}
				}
			}
		});
	}, ['classList.add', 'classList.remove', 'attr', 'style', 'retain']);
}

