// Custom validators for special cases
export class CustomValidators {
  constructor() {
    this.matchValidations = new Map();
    this.serverValidations = new Map();
    this.debounceTimers = new Map();
  }

  // Add match validation between two elements
  addMatchValidation(sourceId, targetId, message) {
    this.matchValidations.set(sourceId, { targetId, message });
  }

  // Add server validation for an element
  addServerValidation(elementId, endpoint, options = {}) {
    this.serverValidations.set(elementId, {
      endpoint,
      method: options.method || 'POST',
      debounceMs: options.debounceMs || 300,
      extraData: options.extraData || {}
    });
  }

  // Validate matching elements
  async validateMatch(sourceElement) {
    const matchConfig = this.matchValidations.get(sourceElement.id);
    if (!matchConfig) return { isValid: true };

    const targetElement = document.getElementById(matchConfig.targetId);
    if (!targetElement) return { isValid: true };

    return {
      isValid: sourceElement.value === targetElement.value,
      message: matchConfig.message
    };
  }

  // Perform server validation
  async validateWithServer(element) {
    const config = this.serverValidations.get(element.id);
    if (!config) return { isValid: true };

    // Clear existing timer
    if (this.debounceTimers.get(element.id)) {
      clearTimeout(this.debounceTimers.get(element.id));
    }

    return new Promise((resolve) => {
      this.debounceTimers.set(
        element.id,
        setTimeout(async () => {
          try {
            const formData = {
              elementName: element.name,
              formId: element.form?.id,
              value: element.value,
              ...config.extraData
            };

            const response = await fetch(config.endpoint, {
              method: config.method,
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(formData)
            });

            const result = await response.json();
            resolve({
              isValid: result.isValid,
              message: result.message
            });
          } catch (error) {
            resolve({
              isValid: false,
              message: 'Server validation failed'
            });
          }
        }, config.debounceMs)
      );
    });
  }
}