export default class DashboardNavMainContent extends HTMLElement {
  constructor() {
    super();
    this.innerHTML = `
      <ul>
        <li><a href="#overview" data-i18n="nav_overview">Visión General</a></li>
        <li><a href="#reports" data-i18n="nav_reports">Reportes</a></li>
        <li><a href="#settings" data-i18n="nav_settings">Configuración</a></li>
      </ul>
    `;
  }
}

customElements.define("dashboard-nav-main-content", DashboardNavMainContent);
