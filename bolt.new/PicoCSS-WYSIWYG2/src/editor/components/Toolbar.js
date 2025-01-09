import { editorColors } from '../config/colors.js';
import { createEmojiPicker } from './EmojiPicker.js';

export function createToolbar() {
  const toolbar = document.createElement('div');
  toolbar.className = 'editor-toolbar';
  toolbar.innerHTML = `
    <div class="toolbar-group">
      <details role="list" class="dropdown">
        <summary aria-haspopup="listbox">Paragraph</summary>
        <ul role="listbox">
          <li><button data-command="formatBlock" data-value="p">Paragraph</button></li>
          <li><button data-command="formatBlock" data-value="h1">Heading 1</button></li>
          <li><button data-command="formatBlock" data-value="h2">Heading 2</button></li>
          <li><button data-command="formatBlock" data-value="h3">Heading 3</button></li>
          <li><button data-command="formatBlock" data-value="h4">Heading 4</button></li>
          <li><button data-command="formatBlock" data-value="h5">Heading 5</button></li>
          <li><button data-command="formatBlock" data-value="h6">Heading 6</button></li>
          <li><button data-command="formatBlock" data-value="blockquote">Blockquote</button></li>
        </ul>
      </details>

      <details role="list" class="dropdown">
        <summary aria-haspopup="listbox">Font</summary>
        <ul role="listbox">
          <li><button data-command="fontName" data-value="Arial">Arial</button></li>
          <li><button data-command="fontName" data-value="Times New Roman">Times New Roman</button></li>
          <li><button data-command="fontName" data-value="Courier New">Courier New</button></li>
        </ul>
      </details>

      <details role="list" class="dropdown">
        <summary aria-haspopup="listbox">Size</summary>
        <ul role="listbox">
          <li><button data-command="fontSize" data-value="1">Small</button></li>
          <li><button data-command="fontSize" data-value="3">Normal</button></li>
          <li><button data-command="fontSize" data-value="5">Large</button></li>
          <li><button data-command="fontSize" data-value="7">Huge</button></li>
        </ul>
      </details>

      <details role="list" class="dropdown">
        <summary aria-haspopup="listbox">Color</summary>
        <ul role="listbox" class="color-menu">
          ${editorColors.map(color => `
            <li><button data-command="foreColor" data-value="${color.value}" style="color: ${color.value}">
              <i class="bi bi-circle-fill"></i> ${color.name}
            </button></li>
          `).join('')}
        </ul>
      </details>
    </div>

    <div class="toolbar-group">
      <button data-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
      <button data-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
      <button data-command="underline" title="Underline"><i class="bi bi-type-underline"></i></button>
    </div>

    <div class="toolbar-group">
      <button data-command="justifyLeft" title="Align Left"><i class="bi bi-text-left"></i></button>
      <button data-command="justifyCenter" title="Align Center"><i class="bi bi-text-center"></i></button>
      <button data-command="justifyRight" title="Align Right"><i class="bi bi-text-right"></i></button>
      <button data-command="justifyFull" title="Justify"><i class="bi bi-justify"></i></button>
    </div>

    <div class="toolbar-group">
      <button data-command="insertUnorderedList" title="Bullet List"><i class="bi bi-list-ul"></i></button>
      <button data-command="insertOrderedList" title="Numbered List"><i class="bi bi-list-ol"></i></button>
      <button data-command="insertHorizontalRule" title="Horizontal Line"><i class="bi bi-dash-lg"></i></button>
    </div>

    <div class="toolbar-group">
      <button id="insertCard" title="Insert Card"><i class="bi bi-card-text"></i></button>
      <button id="insertAccordion" title="Insert Accordion"><i class="bi bi-chevron-down"></i></button>
      <button id="insertImage" title="Insert Image"><i class="bi bi-image"></i></button>
      <button id="insertTable" title="Insert Table"><i class="bi bi-table"></i></button>
      <button id="insertLink" title="Insert Link"><i class="bi bi-link"></i></button>
      <details role="list" class="dropdown">
        <summary aria-haspopup="listbox" title="Insert Emoji"><i class="bi bi-emoji-smile"></i></summary>
        <ul role="listbox" class="emoji-menu">
          ${createEmojiPicker().innerHTML}
        </ul>
      </details>
    </div>

    <div class="toolbar-group">
      <button id="undo" title="Undo"><i class="bi bi-arrow-counterclockwise"></i></button>
      <button id="redo" title="Redo"><i class="bi bi-arrow-clockwise"></i></button>
    </div>

    <div class="toolbar-group">
      <button id="viewSource" title="View Source"><i class="bi bi-code-slash"></i></button>
    </div>
  `;
  return toolbar;
}