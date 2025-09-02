import "./components/dashboard-header-main-content.js";
import "./components/dashboard-nav-main-content.js";
//import { detectLanguage, loadTranslations } from "./language-detect.js";

// Language detection
 let lang = (navigator.language || navigator.userLanguage || "not-detected").substring(0,2);
  let t = {};

document.addEventListener('DOMContentLoaded', () => {
  localStorage.setItem('lang', lang);
});

// Manual language selection
const langSelect = document.getElementById('lang-select');
langSelect.addEventListener('change', (e) => {
  console.log("Language changed to:", e.target.value);
  localStorage.setItem('lang', e.target.value);
  lang = localStorage.getItem('lang');
  loadTranslations();
});

// Load translations
async function loadTranslations() {
    console.log("Detected language:", lang);
  try {
    const resp = await fetch("./translations.json");
    const translations = await resp.json();
    console.log("Loaded translations:", translations);
    // fallback a inglés si no existe el idioma
    t = translations[lang] || translations["en"];

    // aplicar a la interfaz
    document.getElementById("description").innerText = t.description;
    document.getElementById("campo_busqueda").placeholder = t.placeholder;
    document.getElementById("boton_buscar").innerText = t.boton;
  } catch (err) {
    console.error("Error cargando traducciones:", err);
  }
}
loadTranslations();

// Detects user theme preference
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
  document.documentElement.setAttribute('data-theme', savedTheme);
} else {
  document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
}
//console.log(`Theme set to: ${document.documentElement.getAttribute('data-theme')}`);

// Theme toggle
document.getElementById('toggle-theme').addEventListener('click', () => {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  
  console.log(`Theme set to: ${document.documentElement.getAttribute('data-theme')}`);
});

document.getElementById("search-form").addEventListener("submit", function(event) {
      event.preventDefault();
      buscarVideo();
    });

// al terminar de cargar el DOM
    // Función para buscar video en YouTube
let videoTitle;
async function buscarVideo() {
  const term = document.getElementById("campo_busqueda").value;
  const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=1&q=${encodeURIComponent(term)}&key=AIzaSyAEOnD5QCqHOghjuG59htpuHK7cR4cjXfE`;
  
  const resp = await fetch(url);
  const data = await resp.json();

  if (data.items && data.items.length > 0) {
    const videoId = data.items[0].id.videoId;
    videoTitle = data.items[0].snippet.title;
    console.log("Video ID:", data);
    
    mostrarVideo(videoId);
    obtenerInfoCancion(term);
  } else {
    document.getElementById("resultado").innerHTML = "No se encontró video.";
  }
}

    // Función para incrustar el video en un iframe
    function mostrarVideo(video_id) {
      document.getElementById("resultado").innerHTML = `
        <iframe width="720" height="480" 
          src="https://www.youtube.com/embed/${video_id}" 
          frameborder="0" allowfullscreen>
        </iframe>
      `;
    }

     // Llamada a OpenAI para obtener contexto de la canción
   async function obtenerInfoCancion(term) {
    console.log("Video encontrado:", videoTitle);
    console.log("Obteniendo info de la canción:", term);
    console.log("Idioma detectado:", localStorage.getItem('lang') || "no detectado");
      try{
        const response = await fetch("./openai.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            search_term: term,
            user_lang: localStorage.getItem('lang') || "en"
          })
        });
        //const textoPlano = await response.text(); // primero lo leemos como texto
        //console.log("Respuesta cruda del backend:", textoPlano);

            const data = await response.json();
            const payload = JSON.parse(data.choices[0].message.content);
            console.log("Datos recibidos de OpenAI:", payload);

    document.getElementById("datos_relevantes").innerHTML =
      `<p>${payload.datos_relevantes}</p>`;

  } catch (e) {
    console.error("Error", e);
    document.getElementById("datos_relevantes").innerHTML =
      "<p style='color:red'>Error al obtener información de la canción.</p>";
  }
    }