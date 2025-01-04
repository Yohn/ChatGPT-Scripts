// Validators for HTML5 attributes
export const attributeValidators = {
  required: (value) => value !== null && value !== undefined && value.toString().trim() !== '',
  
  minlength: (value, minLength) => value.length >= parseInt(minLength),
  
  maxlength: (value, maxLength) => value.length <= parseInt(maxLength),
  
  pattern: (value, pattern) => new RegExp(pattern).test(value),
  
  min: (value, min) => {
    const numValue = parseFloat(value);
    return !isNaN(numValue) && numValue >= parseFloat(min);
  },
  
  max: (value, max) => {
    const numValue = parseFloat(value);
    return !isNaN(numValue) && numValue <= parseFloat(max);
  },
  
  step: (value, step) => {
    const numValue = parseFloat(value);
    const stepValue = parseFloat(step);
    if (isNaN(numValue) || isNaN(stepValue)) return true;
    const decimals = stepValue.toString().split('.')[1]?.length || 0;
    const multiplier = Math.pow(10, decimals);
    return (numValue * multiplier) % (stepValue * multiplier) === 0;
  },
  
  type: (value, type) => {
    switch (type) {
      case 'email':
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
      case 'url':
        try {
          new URL(value);
          return true;
        } catch {
          return false;
        }
      case 'number':
        return !isNaN(parseFloat(value)) && isFinite(value);
      case 'tel':
        return /^[+\d\s-()]*$/.test(value);
      default:
        return true;
    }
  }
};