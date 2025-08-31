class navMain extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
         <nav class="navbar nav-main navigation">
            <ul>
                <li><a href="#">/resume</a></li>
                <li><a href="#">/contact</a></li>
                <li><a href="/sandbox">/sandbox</a></li>
                <li><a href="#">/etc</a></li>
            </ul>
         </nav>
        `;
    }
}
customElements.define('nav-main', navMain);