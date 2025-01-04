import { FormValidator } from './FormValidator.js';

// Initialize the form validator
const validator = new FormValidator();

// Register a form with options
validator.registerForm('registrationForm', {
  validateOnInput: true,
  showErrorsOnSubmit: true,
  onError: (results) => {
    console.log('Validation errors:', results);
  }
});

// Add password match validation
validator.addMatchValidation(
  'password',
  'confirmPassword',
  'Passwords must match'
);

// Add server validation for username availability
validator.addServerValidation(
  'username',
  '/api/check-username',
  {
    debounceMs: 500,
    extraData: { checkType: 'availability' }
  }
);