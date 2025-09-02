export default class DashboardHeaderMainContent extends HTMLElement {
  constructor() {
    super();
    this.innerHTML = `
      <div style="display: flex; flex-flow:row; align-items: end; gap: 10px;">
        <label for="lang-select" class="visually-hidden">Idioma</label>
        <select id="lang-select">
          <option value="" selected disabled hidden>Language</option>
          <option value="es">Español</option>
          <option value="en">English</option>
          <option value="fr">Français</option>
          <option value="hi">Hindi</option>
          <option value="de">Deutsch</option>

        
        </select>
        <button id="toggle-theme">🌗</button>
      </div>
    `;
  }
}

customElements.define("dashboard-header-main-content", DashboardHeaderMainContent);
