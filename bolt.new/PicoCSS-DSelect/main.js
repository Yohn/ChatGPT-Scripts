import '@picocss/pico'
import './style.css'
import { createCustomSelect } from './src/dselect.js'

// Initialize all selects
document.addEventListener('DOMContentLoaded', () => {
  // Basic select
  createCustomSelect(document.getElementById('basic'), {
    clearable: true
  })

  // Multiple select with search
  createCustomSelect(document.getElementById('multiple'), {
    searchPlaceholder: 'Search options...'
  })

  // Creatable select
  createCustomSelect(document.getElementById('creatable'), {
    createOptionText: 'Add "[TERM]" to the list'
  })
})