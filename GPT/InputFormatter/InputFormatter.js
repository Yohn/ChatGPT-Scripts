class InputFormatter {
	constructor(input) {
		this.input = input;

		// Validate the input element
		if (!this.input) {
			console.error('Input element not found.');
			return;
		}

		this.pattern = this.input.dataset.pattern || null;
		this.type = this.input.dataset.format || null;

		this.commonFormats = {
			phone: { pattern: '(999) 999-9999' },
			ssn: { pattern: '999-99-9999' },
			creditcard: { pattern: '9999-9999-9999-9999' },
			money: {
				custom: [
					{ test: /[^0-9]/g, replace: '' }, // Allow only numbers
				],
			},
		};

		this.init();
	}

	init() {
		if (this.type && this.commonFormats[this.type]) {
			const commonFormat = this.commonFormats[this.type];
			this.pattern = commonFormat.pattern || this.pattern;
			this.custom = commonFormat.custom || null;
		}

		this.input.addEventListener('input', this.handleInput.bind(this));
	}

	handleInput() {
		let value = this.input.value;

		if (this.pattern) {
			value = this.applyPattern(value);
		} else if (this.type === 'money') {
			value = this.formatMoney(value);
		} else if (this.custom) {
			value = this.applyCustomRules(value);
		}

		this.input.value = value;
	}

	applyPattern(value) {
		const cleanValue = value.replace(/[^a-zA-Z0-9]/g, ''); // Remove non-alphanumeric characters
		let formattedValue = '';
		let index = 0;

		for (const char of this.pattern) {
			if (index >= cleanValue.length) break;

			if (char === '9' && /\d/.test(cleanValue[index])) {
				formattedValue += cleanValue[index++];
			} else if (char === 'Z' && /[a-zA-Z]/.test(cleanValue[index])) {
				formattedValue += cleanValue[index++];
			} else if (char === 'X' && /[a-zA-Z0-9]/.test(cleanValue[index])) {
				formattedValue += cleanValue[index++];
			} else if (char === 'Y') {
				formattedValue += cleanValue[index++];
			} else if (char !== '9' && char !== 'Z' && char !== 'X' && char !== 'Y') {
				// Add literal characters from the pattern
				formattedValue += char;
			}
		}

		return formattedValue;
	}

	applyCustomRules(value) {
		for (const rule of this.custom) {
			if (rule.test && rule.replace) {
				const regex = typeof rule.test === 'string' ? new RegExp(rule.test, 'g') : rule.test;
				value = value.replace(regex, rule.replace);
			}
		}

		return value;
	}

	formatMoney(value) {
		// Remove non-numeric characters
		value = value.replace(/[^0-9]/g, '');

		// Convert to a numeric value
		const numericValue = parseFloat(value) / 100;

		// Format with two decimals and commas
		const formattedValue = numericValue.toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2,
		});

		return formattedValue;
	}

	static initAll() {
		// Automatically attach the formatter to all inputs with [data-format] or [data-pattern]
		document.querySelectorAll('input[data-format], input[data-pattern]').forEach((input) => {
			new InputFormatter(input);
		});
	}
}

// Ensure DOM is ready
document.addEventListener('DOMContentLoaded', () => {
	InputFormatter.initAll();

	// Handle dynamic inputs by listening for new elements being added
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === 1 && node.matches?.('input[data-format], input[data-pattern]')) {
					new InputFormatter(node);
				}
			});
		});
	});

	observer.observe(document.body, {
		childList: true,
		subtree: true,
	});
});


//// Ensure DOM is ready
//document.addEventListener('DOMContentLoaded', () => {
//	new InputFormatter(document.querySelector('#money'), { type: 'money' });
//	new InputFormatter(document.querySelector('#phone'), { type: 'phone' });
//	new InputFormatter(document.querySelector('#ssn'), { type: 'ssn' });
//
//	// Example with a custom pattern
//	new InputFormatter(document.querySelector('#custom'), {
//		pattern: 'Z9X-Y9Z',
//	});
//});
