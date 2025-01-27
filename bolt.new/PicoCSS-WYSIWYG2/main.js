import '@yohns/picocss';
import './src/styles/editor.css';
import { WYSIWYGEditor } from './src/editor/editor.js';

// Initialize the editor
const editorContainer = document.querySelector('#app');
const editor = new WYSIWYGEditor(editorContainer);

// Example of getting content when form is submitted
document.querySelector('#app').insertAdjacentHTML('beforeend', `
  <form id="editorForm" class="container">
    <button type="submit">Submit</button>
  </form>
`);

document.querySelector('#editorForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const htmlContent = editor.getContent('html');
  const markdownContent = editor.getContent('markdown');
  
  console.log('HTML Content:', htmlContent);
  console.log('Markdown Content:', markdownContent);
});