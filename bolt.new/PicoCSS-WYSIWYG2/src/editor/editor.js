import DOMPurify from 'dompurify';
import TurndownService from 'turndown';
import { createToolbar } from './components/Toolbar.js';
import { createImageModal, createTableModal, createLinkModal } from './components/Modals.js';
import { createCardModal } from './components/CardModal.js';
import { createAccordionModal } from './components/AccordionModal.js';
import { History } from './utils/history.js';

export class WYSIWYGEditor {
  constructor(container) {
    this.container = container;
    this.history = new History();
    this.turndownService = new TurndownService();
    this.init();
  }

  init() {
    // Create toolbar
    this.toolbar = createToolbar();
    this.container.appendChild(this.toolbar);

    // Create editable content area
    this.editor = document.createElement('div');
    this.editor.className = 'editor-content';
    this.editor.contentEditable = true;
    this.container.appendChild(this.editor);

    // Setup event listeners
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Toolbar command buttons
    this.toolbar.querySelectorAll('[data-command]').forEach(button => {
      button.addEventListener('click', (e) => {
        const command = e.target.closest('[data-command]').dataset.command;
        const value = e.target.closest('[data-command]').dataset.value || '';
        this.execCommand(command, value);
      });
    });

    // Special buttons
    document.getElementById('insertCard').addEventListener('click', () => this.handleCardInsert());
    document.getElementById('insertAccordion').addEventListener('click', () => this.handleAccordionInsert());
    document.getElementById('insertImage').addEventListener('click', () => this.handleImageInsert());
    document.getElementById('insertTable').addEventListener('click', () => this.handleTableInsert());
    document.getElementById('insertLink').addEventListener('click', () => this.handleLinkInsert());
    document.getElementById('undo').addEventListener('click', () => this.undo());
    document.getElementById('redo').addEventListener('click', () => this.redo());
    document.getElementById('viewSource').addEventListener('click', () => this.toggleSource());

    // Emoji picker
    this.toolbar.querySelectorAll('.emoji-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        this.execCommand('insertText', e.target.dataset.emoji);
      });
    });

    // Content change tracking
    this.editor.addEventListener('input', () => {
      this.history.push(this.editor.innerHTML);
    });
  }

  execCommand(command, value = '') {
    document.execCommand(command, false, value);
    this.editor.focus();
  }

  getContent(format = 'html') {
    const html = DOMPurify.sanitize(this.editor.innerHTML);
    return format === 'markdown' ? this.turndownService.turndown(html) : html;
  }

  setContent(content) {
    this.editor.innerHTML = DOMPurify.sanitize(content);
    this.history.push(this.editor.innerHTML);
  }

  undo() {
    const content = this.history.undo();
    if (content !== null) {
      this.editor.innerHTML = content;
    }
  }

  redo() {
    const content = this.history.redo();
    if (content !== null) {
      this.editor.innerHTML = content;
    }
  }

  toggleSource() {
    const isShowingSource = this.editor.classList.contains('showing-source');
    if (isShowingSource) {
      this.editor.innerHTML = this.editor.textContent;
      this.editor.classList.remove('showing-source');
    } else {
      this.editor.textContent = this.editor.innerHTML;
      this.editor.classList.add('showing-source');
    }
  }

  // ... (rest of the methods remain the same)
}