<!-- ===== MENU + UI AAA FINAL ===== -->

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

<style>

/* ================= MENU ================= */
.global-menu,
.global-menu * {
  box-sizing: border-box;
  font-family: 'Orbitron', sans-serif;
}

.global-menu {
  --primary: #2563eb;
  --bg: #020617;
  --bg2: #0f172a;
  --text: #e2e8f0;
}

/* BOTÓN */
.menu-toggle {
  position: fixed;
  top: 18px;
  left: 18px;
  z-index: 100000;

  width: 52px;
  height: 52px;

  background: #020617;
  border: 2px solid #2563eb;

  color: white;
  font-size: 22px;

  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;

  box-shadow: 0 0 15px rgba(37,99,235,0.6);
  transition: 0.25s;
}

/* 🔥 HOVER BOTÓN */
.menu-toggle:hover {
  transform: rotate(-8deg) scale(1.1);
  box-shadow:
    0 0 25px rgba(37,99,235,0.9),
    0 0 50px rgba(37,99,235,0.5);
}

.menu-toggle.shifted {
  left: 290px;
}

/* OVERLAY */
.menu-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);

  opacity: 0;
  pointer-events: none;
  transition: 0.3s;
  z-index: 9000;
}

.menu-overlay.show {
  opacity: 1;
  pointer-events: auto;
}

/* MENU */
.global-menu {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 270px;

  background: linear-gradient(180deg, #020617, #0f172a);

  padding: 18px 14px;

  transform: translateX(-100%);
  transition: 0.35s ease;

  box-shadow: 0 0 40px rgba(37,99,235,0.3);

  z-index: 9500;
}

/* 💥 ENTRADA PRO */
.global-menu.show {
  transform: translateX(0);
  animation: entradaPro 0.45s ease;
}

@keyframes entradaPro {
  0% {
    transform: translateX(-100%) skewX(-15deg);
    opacity: 0;
  }
  60% {
    transform: translateX(15px) skewX(5deg);
  }
  100% {
    transform: translateX(0) skewX(0);
    opacity: 1;
  }
}

/* HEADER */
.menu-header {
  padding: 14px;
  border-radius: 10px;
  background: linear-gradient(135deg, #2563eb, #020617);
}

.menu-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.menu-logo-wrap {
  height: 48px;
  background: white;
  border-radius: 8px;
  padding: 5px;
}

.menu-logo {
  height: 100%;
}

.menu-user-info strong {
  font-size: 13px;
  color: white;
}

.menu-user-info span {
  font-size: 11px;
  color: #93c5fd;
}

/* ================= LINKS ================= */
.global-menu a {
  position: relative;
  display: block;
  color: var(--text);
  text-decoration: none;
  padding: 12px 14px;
  margin-bottom: 6px;

  background: #020617;
  overflow: hidden;

  transition: 0.25s;
}

/* 🔥 EFECTO LUZ HOVER */
.global-menu a::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(
    120deg,
    transparent,
    rgba(37,99,235,0.6),
    transparent
  );
  transform: translateX(-100%);
  transition: 0.4s;
}

.global-menu a:hover::before {
  transform: translateX(100%);
}

/* 🔥 HOVER */
.global-menu a:hover {
  background: #0f172a;
  transform: translateX(8px);
  box-shadow: 0 0 12px rgba(37,99,235,0.5);
}

/* 🔥 ACTIVO (PANTALLA ACTUAL) */
.global-menu a.active {
  background: linear-gradient(90deg, #2563eb, #1e40af);
  color: white;

  transform: translateX(6px);

  box-shadow:
    0 0 20px rgba(37,99,235,0.8),
    inset 0 0 10px rgba(0,0,0,0.4);
}

/* ================= LOADER ================= */
#gameLoader {
  position: fixed;
  inset: 0;
  background: #020617;
  z-index: 99999;

  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;

  color: white;
}

.loader-bar {
  width: 200px;
  height: 4px;
  background: #0f172a;
  overflow: hidden;
  margin-top: 15px;
}

.loader-bar span {
  display: block;
  height: 100%;
  width: 40%;
  background: #2563eb;
  animation: loading 1s linear infinite;
}

@keyframes loading {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(300%); }
}

#gameLoader.hidden {
  opacity: 0;
  pointer-events: none;
}

/* ================= TRANSICION ================= */
#pageTransition {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 9000;
}

.slice {
  position: absolute;
  width: 200%;
  height: 25%;
  left: -50%;
  background: #2563eb;
  transform: skewY(-10deg) translateX(-120%);
}

.slice:nth-child(2){top:25%;}
.slice:nth-child(3){top:50%;}
.slice:nth-child(4){top:75%;}

.slice.active {
  animation: sliceIn 0.5s forwards;
}

@keyframes sliceIn {
  to {
    transform: skewY(-10deg) translateX(0);
  }
}

/* TEXTO TRANSICION */
.transition-text {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) skewX(-10deg);

  font-size: 32px;
  font-weight: 700;
  color: white;

  z-index: 9500;
  opacity: 0;

  text-shadow: 0 0 20px rgba(37,99,235,0.9);
}

.transition-text.show {
  animation: textIn 0.4s forwards;
}

@keyframes textIn {
  from {
    opacity: 0;
    transform: translate(-50%, -60%) skewX(-20deg);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) skewX(-10deg);
  }
}

</style>

<!-- BOTÓN -->
<div class="menu-toggle" id="menuButton">☰</div>
<div class="menu-overlay" id="menuOverlay"></div>

<!-- MENU -->
<nav class="global-menu" id="drawerMenu">

  <div class="menu-header">
    <div class="menu-user">
      <div class="menu-logo-wrap">
        <img src="/shared/logo.png" class="menu-logo">
      </div>
      <div class="menu-user-info">
        <strong>Transporte Mimbral</strong>
        <span>Panel de control</span>
      </div>
    </div>
  </div>

  <a href="/flota/">Flota</a>
  <a href="/asignacion/">Asignación</a>
  <a href="/sistema_patio2/">Patio</a>
  <a href="/solicitud_epp/">EPP</a>
  <a href="/amonestacion/">Amonestaciones</a>
  <a href="/errores/">Faltas</a>
  <a href="/multas/">Multas</a>
  <a href="/Incidentes/">Informes de incidentes</a>
  <a href="/libretas/">Libretas</a>
  <a href="/aviso/">Avisos correctivos</a>
  <a href="/CMPC/">CMPC</a>
  <a href="/contactos/">Contactos</a>

</nav>

<!-- LOADER -->
<div id="gameLoader">
  <div>CARGANDO...</div>
  <div class="loader-bar"><span></span></div>
</div>

<!-- TRANSICION -->
<div id="pageTransition">
  <div class="slice"></div>
  <div class="slice"></div>
  <div class="slice"></div>
  <div class="slice"></div>
</div>

<!-- TEXTO -->
<div id="transitionText" class="transition-text"></div>

<script>

/* MENU */
menuButton.onclick = () => {
  drawerMenu.classList.toggle("show");
  menuOverlay.classList.toggle("show");
  menuButton.classList.toggle("shifted");
};

menuOverlay.onclick = () => {
  drawerMenu.classList.remove("show");
  menuOverlay.classList.remove("show");
  menuButton.classList.remove("shifted");
};

/* LOADER */
window.addEventListener("load", () => {
  const loader = document.getElementById("gameLoader");
  setTimeout(() => {
    loader.classList.add("hidden");
    setTimeout(() => loader.remove(), 500);
  }, 300);
});

/* TRANSICION */
document.querySelectorAll("a[href]").forEach(link => {
  link.addEventListener("click", function(e) {

    const href = link.getAttribute("href");

    if (!href || href.startsWith("#") || link.target === "_blank") return;

    e.preventDefault();

    const slices = document.querySelectorAll(".slice");
    const text = document.getElementById("transitionText");

    let label = link.textContent.trim().toUpperCase();
    text.textContent = "REDIRIGIENDO A " + label + "...";
    text.classList.add("show");

    slices.forEach((slice, i) => {
      setTimeout(() => {
        slice.classList.add("active");
      }, i * 80);
    });

    setTimeout(() => {
      window.location.href = href;
    }, 600);

  });
});

/* 🔥 LINK ACTIVO AUTOMÁTICO */
const links = document.querySelectorAll('.global-menu a');
const rutaActual = window.location.pathname;

links.forEach(link => {
  const href = link.getAttribute('href');

  if (rutaActual.startsWith(href)) {
    link.classList.add('active');
  }
});

</script>