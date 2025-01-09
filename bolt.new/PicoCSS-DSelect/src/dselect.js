// Custom select component using PicoCSS
function createCustomSelect(el, options = {}) {
  // Hide original select
  el.style.display = 'none';
  
  const config = {
    search: el.dataset.search === 'true' || options.search || false,
    creatable: el.dataset.creatable === 'true' || options.creatable || false,
    clearable: el.dataset.clearable === 'true' || options.clearable || false,
    searchPlaceholder: el.dataset.searchPlaceholder || options.searchPlaceholder || 'Search...',
    noResultsText: el.dataset.noResultsText || options.noResultsText || 'No results found',
    createOptionText: el.dataset.createOptionText || options.createOptionText || 'Press Enter to add "[TERM]"'
  };

  // Create wrapper
  const wrapper = document.createElement('div');
  wrapper.className = 'custom-select';
  
  // Create toggle button
  const toggle = document.createElement('button');
  toggle.className = 'select-toggle';
  toggle.type = 'button';
  toggle.setAttribute('aria-haspopup', 'true');
  
  // Create dropdown
  const dropdown = document.createElement('div');
  dropdown.className = 'select-dropdown';
  
  // Add search if enabled
  if (config.search) {
    const searchContainer = document.createElement('div');
    searchContainer.className = 'search-container';
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = config.searchPlaceholder;
    searchContainer.appendChild(searchInput);
    dropdown.appendChild(searchContainer);
    
    searchInput.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase();
      filterOptions(term);
    });
    
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && config.creatable) {
        createNewOption(searchInput.value);
      }
    });
  }
  
  // Create options container
  const optionsContainer = document.createElement('div');
  optionsContainer.className = 'options-container';
  dropdown.appendChild(optionsContainer);
  
  // Create no results message
  const noResults = document.createElement('div');
  noResults.className = 'no-results hidden';
  noResults.textContent = config.noResultsText;
  dropdown.appendChild(noResults);
  
  // Add clear button if enabled
  if (config.clearable && !el.multiple) {
    const clearBtn = document.createElement('button');
    clearBtn.className = 'clear-btn';
    clearBtn.innerHTML = '×';
    clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      clearSelection();
    });
    wrapper.appendChild(clearBtn);
  }
  
  function updateToggleText() {
    const selected = Array.from(el.selectedOptions);
    if (selected.length === 0) {
      toggle.textContent = el.getAttribute('placeholder') || 'Select...';
      toggle.classList.add('placeholder');
    } else if (el.multiple) {
      toggle.innerHTML = selected.map(opt => 
        `<span class="tag">${opt.text}<button class="tag-remove" data-value="${opt.value}">×</button></span>`
      ).join('');
      toggle.classList.remove('placeholder');
    } else {
      toggle.textContent = selected[0].text;
      toggle.classList.remove('placeholder');
    }
  }
  
  function renderOptions() {
    optionsContainer.innerHTML = '';
    Array.from(el.children).forEach(child => {
      if (child.tagName === 'OPTGROUP') {
        const group = document.createElement('div');
        group.className = 'option-group';
        group.innerHTML = `<div class="group-label">${child.label}</div>`;
        Array.from(child.children).forEach(opt => group.appendChild(createOptionElement(opt)));
        optionsContainer.appendChild(group);
      } else {
        optionsContainer.appendChild(createOptionElement(child));
      }
    });
  }
  
  function createOptionElement(option) {
    if (option.hidden) return null;
    const opt = document.createElement('button');
    opt.className = 'option';
    opt.type = 'button';
    opt.dataset.value = option.value;
    opt.textContent = option.text;
    if (option.selected) opt.classList.add('selected');
    if (option.disabled) opt.disabled = true;
    
    opt.addEventListener('click', () => selectOption(option.value));
    return opt;
  }
  
  function selectOption(value) {
    if (el.multiple) {
      const option = Array.from(el.options).find(opt => opt.value === value);
      option.selected = !option.selected;
    } else {
      el.value = value;
      closeDropdown();
    }
    updateToggleText();
    el.dispatchEvent(new Event('change'));
  }
  
  function filterOptions(term) {
    let hasResults = false;
    Array.from(optionsContainer.querySelectorAll('.option')).forEach(opt => {
      const matches = opt.textContent.toLowerCase().includes(term);
      opt.classList.toggle('hidden', !matches);
      if (matches) hasResults = true;
    });
    
    noResults.classList.toggle('hidden', hasResults);
    if (config.creatable && !hasResults) {
      noResults.innerHTML = config.createOptionText.replace('[TERM]', term);
    }
  }
  
  function createNewOption(value) {
    const option = document.createElement('option');
    option.value = value;
    option.text = value;
    el.add(option);
    option.selected = true;
    updateToggleText();
    renderOptions();
    closeDropdown();
    el.dispatchEvent(new Event('change'));
  }
  
  function clearSelection() {
    Array.from(el.options).forEach(opt => opt.selected = false);
    updateToggleText();
    el.dispatchEvent(new Event('change'));
  }
  
  function closeDropdown() {
    wrapper.classList.remove('open');
    if (config.search) {
      dropdown.querySelector('input').value = '';
      filterOptions('');
    }
  }
  
  // Event listeners
  toggle.addEventListener('click', () => {
    wrapper.classList.toggle('open');
    if (wrapper.classList.contains('open') && config.search) {
      dropdown.querySelector('input').focus();
    }
  });
  
  document.addEventListener('click', (e) => {
    if (!wrapper.contains(e.target)) {
      closeDropdown();
    }
  });
  
  // Initialize
  wrapper.appendChild(toggle);
  wrapper.appendChild(dropdown);
  el.parentNode.insertBefore(wrapper, el);
  updateToggleText();
  renderOptions();
  
  // Handle tag removal in multiple select
  if (el.multiple) {
    toggle.addEventListener('click', (e) => {
      if (e.target.classList.contains('tag-remove')) {
        e.stopPropagation();
        selectOption(e.target.dataset.value);
      }
    });
  }
  
  return {
    update: () => {
      updateToggleText();
      renderOptions();
    }
  };
}

// Export for use in other files
export { createCustomSelect };