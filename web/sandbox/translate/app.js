import "./components/dashboard-header-main-content.js";
import "./components/dashboard-nav-main-content.js";

// Detect browser language (normalize to 2 letters)
let lang = (navigator.language || navigator.userLanguage || "not-detected").substring(0, 2);
let translations = {};

// Store detected language on DOM load
document.addEventListener("DOMContentLoaded", () => {
	localStorage.setItem("lang", lang);
});

// Manual language selection
const langSelect = document.getElementById("lang-select");
langSelect.addEventListener("change", (e) => {
	console.log("Language changed to:", e.target.value);
	localStorage.setItem("lang", e.target.value);
	lang = localStorage.getItem("lang");
	loadTranslations();
});

// Load translations from external file
async function loadTranslations() {
	console.log("Detected language:", lang);
	try {
		const resp = await fetch("./translations.json");
		const data = await resp.json();
		console.log("Loaded translations:", data);

		// Fallback to English if language not available
		translations = data[lang] || data["en"];

		// Apply translations to UI
		document.getElementById("description").innerText = translations.description;
		document.getElementById("search").placeholder = translations.placeholder;
		//document.getElementById("submit").innerText = translations.boton;
	} catch (err) {
		console.error("Error loading translations:", err);
	}
}
loadTranslations();

// Detect and apply user theme preference
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
const savedTheme = localStorage.getItem("theme");
if (savedTheme) {
	document.documentElement.setAttribute("data-theme", savedTheme);
} else {
	document.documentElement.setAttribute("data-theme", prefersDark ? "dark" : "light");
}

// Theme toggle
document.getElementById("toggle-theme").addEventListener("click", () => {
	const current = document.documentElement.getAttribute("data-theme");
	const next = current === "dark" ? "light" : "dark";
	document.documentElement.setAttribute("data-theme", next);
	localStorage.setItem("theme", next);

	console.log(`Theme set to: ${document.documentElement.getAttribute("data-theme")}`);
});

// Handle form submit for video search
document.getElementById("search-form").addEventListener("submit", (event) => {
	event.preventDefault();
	searchVideo();
});

// Global variable to store video title
let videoTitle;

// Search YouTube video
async function searchVideo() {
	const term = document.getElementById("search").value;
  //api key constricted to domain
	const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=1&q=${encodeURIComponent(term)}&key=AIzaSyDrDPNaWbkUgyJU95aAA-wvNkHAbhy8P3k`;

	try {
		const resp = await fetch(url);
		const data = await resp.json();

		if (data.items && data.items.length > 0) {
			const videoId = data.items[0].id.videoId;
			videoTitle = data.items[0].snippet.title;

			console.log("YouTube response:", data);
			showVideo(videoId);
			getSongInfo(term);
		} else {
			document.getElementById("result").innerHTML = "<p>No video found.</p>";
		}
	} catch (err) {
		console.error("Error fetching YouTube data:", err);
		document.getElementById("result").innerHTML = "<p style='color:red'>Error fetching video.</p>";
	}
}

// Embed YouTube video in iframe
function showVideo(videoId) {
	document.getElementById("result").innerHTML = `
		<iframe width="720" height="480"
			src="https://www.youtube.com/embed/${videoId}"
			frameborder="0" allowfullscreen>
		</iframe>
	`;
}

async function getSongInfo(term) {
	console.log("Fetching song info for:", term);

	try {
		const response = await fetch("./openai.php", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({
				search_term: term,
				user_lang: localStorage.getItem("lang") || "en"
			})
		});

		const data = await response.json();
		const payload = JSON.parse(data.choices[0].message.content);
		console.log("Data received from OpenAI:", payload);

		// Update UI with each section
		document.getElementById("relevant_data").innerHTML = `<h3>Context</h3><p>${payload.relevant_data}</p>`;
    // h3 titles from translations.json based on detected language
    document.getElementById("relevant_data").innerHTML = `<h3>${translations.relevant_info}</h3><p>${payload.relevant_data}</p>`;

		document.getElementById("artist").innerHTML = `<h3>${translations.artist}</h3><p>${payload.artist}</p>`;
		document.getElementById("production").innerHTML = `<h3>${translations.production}</h3><p>${payload.production}</p>`;
		document.getElementById("popularity").innerHTML = `<h3>${translations.popularity}</h3><p>${payload.popularity}</p>`;
		document.getElementById("trivia").innerHTML = `<h3>${translations.trivia}</h3><p>${payload.trivia}</p>`;

		if (payload.recommendations && payload.recommendations.length > 0) {
			document.getElementById("recommendations").innerHTML =
				`<h3>${translations.recommendations}</h3><ul>${payload.recommendations.map(r => `<li>${r}</li>`).join("")}</ul>`;
		} else {
			document.getElementById("recommendations").innerHTML = "";
		}

	} catch (err) {
		console.error("Error fetching OpenAI data:", err);
		document.getElementById("relevant_data").innerHTML =
			"<p style='color:red'>Error fetching song info.</p>";
	}
}
