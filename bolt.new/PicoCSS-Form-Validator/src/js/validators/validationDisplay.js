export class ValidationDisplay {
  updateValidationState(element, result) {
    // Remove any existing validation messages
    this.removeValidationMessage(element);
    
    if (result.isValid) {
      this.markValid(element);
    } else {
      this.markInvalid(element, result.message);
    }
  }

  markValid(element) {
    element.classList.remove('user-invalid');
    element.classList.add('user-valid');
    
    // Add success message if specified
    if (element.dataset.valid) {
      const small = document.createElement('small');
      small.setAttribute('data-valid', '');
      small.textContent = element.dataset.valid;
      element.parentNode.insertBefore(small, element.nextSibling);
    }
  }

  markInvalid(element, message) {
    element.classList.remove('user-valid');
    element.classList.add('user-invalid');

    // Add error message
    const small = document.createElement('small');
    small.setAttribute('data-invalid', '');
    small.textContent = message;
    element.parentNode.insertBefore(small, element.nextSibling);
  }

  removeValidationMessage(element) {
    // Remove existing validation messages
    const nextSibling = element.nextElementSibling;
    if (nextSibling && (nextSibling.hasAttribute('data-valid') || nextSibling.hasAttribute('data-invalid'))) {
      nextSibling.remove();
    }
  }
}