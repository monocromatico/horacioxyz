class navMain extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
         <nav class="navbar nav-main navigation">
            <ul>
                <li><a href="/">/home</a></li>
                <li><a href="/resume">/resume</a></li>
                <!--<li><a href="/contact">/contact</a></li>-->
                <!--<li><a href="/sandbox">/sandbox</a></li>-->
                <li><a href="/etc">/etc</a></li>
            </ul>
         </nav>
        `;

        if (window.location.pathname === '/') {
            const firstLi = this.querySelector('ul li');
            if (firstLi) {
                firstLi.style.display = 'none';
            }
        }
    }
}

customElements.define('nav-main', navMain);