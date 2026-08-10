const footer = document.querySelector('footer');

if (footer) {
    const userLang = navigator.language || navigator.userLanguage;
    const phrases = {
        es: 'Hecho con <span class="heart" id="heart">❤️</span> para que el Sol resurja',
        en: 'Made with <span class="heart" id="heart">❤️</span> so the Sun may rise'
    };

    const text = userLang.startsWith('en') ? phrases.en : phrases.es;

    footer.classList.add('footer');
    footer.innerHTML = '<div>'+ text+ '</div>';

    const heart = footer.querySelector('#heart');

    if (heart) {
        let toggled = false;

        heart.addEventListener('click', () => {
            toggled = !toggled;
            heart.textContent = toggled ? '🫀' : '❤️';
        });

        footer.addEventListener('mouseover', () => {
            heart.textContent = '🫀';
        });

        footer.addEventListener('mouseout', () => {
            if (!toggled) heart.textContent = '❤️';
        });
    }
}