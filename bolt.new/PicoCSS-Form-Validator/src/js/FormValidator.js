import { attributeValidators } from './validators/attributeValidators.js';
import { CustomValidators } from './validators/customValidators.js';
import { ValidationDisplay } from './validators/validationDisplay.js';

export class FormValidator {
  constructor() {
    this.customValidators = new CustomValidators();
    this.display = new ValidationDisplay();
    this.forms = new Map();
    this.init();
  }

  init() {
    document.addEventListener('input', this.handleInput.bind(this));
    document.addEventListener('submit', this.handleSubmit.bind(this), true);
  }

  registerForm(formId, options = {}) {
    const form = document.getElementById(formId);
    if (form) {
      // Remove novalidate if it exists to enable PicoCSS styles
      form.removeAttribute('novalidate');
    }
    
    this.forms.set(formId, {
      validateOnInput: options.validateOnInput ?? true,
      showErrorsOnSubmit: options.showErrorsOnSubmit ?? true,
      ...options
    });
  }

  addMatchValidation(sourceId, targetId, message) {
    this.customValidators.addMatchValidation(sourceId, targetId, message);
  }

  addServerValidation(elementId, endpoint, options) {
    this.customValidators.addServerValidation(elementId, endpoint, options);
  }

  async validateElement(element) {
    if (element.disabled || element.readonly) return { isValid: true };

    const validations = [];

    // HTML5 attribute validations
    for (const [attr, validator] of Object.entries(attributeValidators)) {
      if (element.hasAttribute(attr)) {
        const isValid = validator(element.value, element.getAttribute(attr));
        if (!isValid) {
          validations.push({
            isValid: false,
            message: element.dataset.invalid || `Invalid ${attr}`
          });
        }
      }
    }

    // Match validation
    const matchResult = await this.customValidators.validateMatch(element);
    if (!matchResult.isValid) {
      validations.push(matchResult);
    }

    // Server validation
    const serverResult = await this.customValidators.validateWithServer(element);
    if (!serverResult.isValid) {
      validations.push(serverResult);
    }

    const failedValidation = validations.find(v => !v.isValid);
    return failedValidation || { isValid: true };
  }

  async validateForm(form) {
    const elements = form.querySelectorAll('input:not([type="submit"]), select, textarea');
    const results = await Promise.all(
      Array.from(elements).map(async element => ({
        element,
        result: await this.validateElement(element)
      }))
    );

    return results;
  }

  async handleInput(event) {
    const element = event.target;
    const form = element.form;
    
    if (!form || !this.forms.has(form.id)) return;
    const formConfig = this.forms.get(form.id);
    
    if (!formConfig.validateOnInput) return;

    const result = await this.validateElement(element);
    this.display.updateValidationState(element, result);
  }

  async handleSubmit(event) {
    const form = event.target;
    if (!this.forms.has(form.id)) return;
    
    const formConfig = this.forms.get(form.id);
    const results = await this.validateForm(form);
    const hasErrors = results.some(({ result }) => !result.isValid);

    if (hasErrors) {
      event.preventDefault();
      
      if (formConfig.showErrorsOnSubmit) {
        results.forEach(({ element, result }) => {
          this.display.updateValidationState(element, result);
        });
      }

      if (formConfig.onError) {
        formConfig.onError(results);
      }
    }
  }
}