<?php
// Configurar zona horaria de Lima, Perú (UTC-5)
date_default_timezone_set('America/Lima');

session_start();
if (isset($_SESSION['S_ID'])) {
    header('Location: view/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión - Estación Goyo</title>

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="plantilla/dist/css/adminlte.min.css">
  <link rel="icon" href="img/grau.png" type="image/jpg">
  
  <style>
    :root {
      --primary-color: #ff4500;
      --secondary-color: #00bfff;
      --accent-color: #ffd700;
      --light-color: #ffffff;
      --dark-color: #1a1a2e;
      --neon-orange: #ff6b35;
      --neon-blue: #00d4ff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, 
        #0a0e27 0%, 
        #1a1147 15%,
        #2d1b69 30%,
        #1e3a8a 50%,
        #134e4a 70%,
        #1e293b 85%,
        #0f172a 100%);
      background-size: 400% 400%;
      animation: gradientShift 20s ease infinite;
      position: relative;
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    
    /* Capa de estrellas brillantes */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        radial-gradient(2px 2px at 20% 30%, white, transparent),
        radial-gradient(2px 2px at 60% 70%, white, transparent),
        radial-gradient(1px 1px at 50% 50%, white, transparent),
        radial-gradient(1px 1px at 80% 10%, white, transparent),
        radial-gradient(2px 2px at 90% 60%, white, transparent),
        radial-gradient(1px 1px at 33% 80%, white, transparent),
        radial-gradient(2px 2px at 15% 90%, white, transparent);
      background-size: 200% 200%;
      background-position: 0% 0%;
      animation: starsMove 60s linear infinite, starsTwinkle 3s ease-in-out infinite;
      z-index: 0;
      opacity: 0.6;
    }
    
    /* Rayos de luz dinámicos */
    body::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(ellipse at 10% 20%, rgba(255, 69, 0, 0.3) 0%, transparent 40%),
        radial-gradient(ellipse at 90% 80%, rgba(0, 191, 255, 0.3) 0%, transparent 40%),
        radial-gradient(ellipse at 50% 50%, rgba(255, 215, 0, 0.2) 0%, transparent 50%);
      z-index: 0;
      animation: lightPulse 8s ease-in-out infinite;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      25% { background-position: 50% 100%; }
      50% { background-position: 100% 50%; }
      75% { background-position: 50% 0%; }
      100% { background-position: 0% 50%; }
    }
    
    @keyframes starsMove {
      0% { background-position: 0% 0%; }
      100% { background-position: 100% 100%; }
    }
    
    @keyframes starsTwinkle {
      0%, 100% { opacity: 0.6; }
      50% { opacity: 1; }
    }
    
    @keyframes lightPulse {
      0%, 100% { 
        opacity: 0.6;
        transform: scale(1);
      }
      50% { 
        opacity: 1;
        transform: scale(1.1);
      }
    }

    /* Partículas brillantes */
    .particles-container {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
      z-index: 1;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      animation: floatParticle linear infinite;
      opacity: 0;
      box-shadow: 0 0 10px currentColor;
    }

    .particle:nth-child(1) {
      left: 10%;
      width: 8px;
      height: 8px;
      background: radial-gradient(circle, #FFD700, #FFA500);
      animation-duration: 8s;
      animation-delay: 0s;
    }

    .particle:nth-child(2) {
      left: 25%;
      width: 10px;
      height: 10px;
      background: radial-gradient(circle, #FF6B35, #FF4500);
      animation-duration: 12s;
      animation-delay: 2s;
    }

    .particle:nth-child(3) {
      left: 40%;
      width: 7px;
      height: 7px;
      background: radial-gradient(circle, #00D4FF, #0080FF);
      animation-duration: 10s;
      animation-delay: 4s;
    }

    .particle:nth-child(4) {
      left: 55%;
      width: 9px;
      height: 9px;
      background: radial-gradient(circle, #FF1493, #FF69B4);
      animation-duration: 14s;
      animation-delay: 1s;
    }

    .particle:nth-child(5) {
      left: 70%;
      width: 8px;
      height: 8px;
      background: radial-gradient(circle, #9D4EDD, #7209B7);
      animation-duration: 11s;
      animation-delay: 3s;
    }

    .particle:nth-child(6) {
      left: 85%;
      width: 10px;
      height: 10px;
      background: radial-gradient(circle, #FFD700, #FFED4E);
      animation-duration: 9s;
      animation-delay: 5s;
    }

    @keyframes floatParticle {
      0% {
        bottom: -10%;
        opacity: 0;
        transform: translateX(0) rotate(0deg) scale(0.5);
      }
      10% {
        opacity: 1;
      }
      50% {
        transform: translateX(50px) rotate(180deg) scale(1.2);
      }
      90% {
        opacity: 1;
      }
      100% {
        bottom: 110%;
        opacity: 0;
        transform: translateX(-50px) rotate(360deg) scale(0.5);
      }
    }

    /* Contenedor principal */
    .login-container {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 480px;
      animation: fadeInUp 0.8s ease-out;
    }
    
    .card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(20px);
      border-radius: 30px;
      box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.3),
        0 0 40px rgba(255, 69, 0, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 
        0 25px 70px rgba(0, 0, 0, 0.4),
        0 0 50px rgba(255, 69, 0, 0.25);
    }

    /* Header del card con logo */
    .card-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--neon-orange) 100%);
      padding: 2.5rem 2rem 1.5rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .card-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
      );
      animation: shine 3s infinite;
    }

    @keyframes shine {
      0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
      100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    .logo-container {
      position: relative;
      z-index: 1;
      margin-bottom: 1rem;
    }

    .logo-container img {
      max-width: 180px;
      height: auto;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
      animation: logoFloat 3s ease-in-out infinite;
    }

    @keyframes logoFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .company-name {
      color: white;
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 2px;
      text-shadow: 
        0 2px 10px rgba(0, 0, 0, 0.3),
        0 0 20px rgba(255, 215, 0, 0.5);
      margin: 0.5rem 0;
      position: relative;
      z-index: 1;
    }

    .company-subtitle {
      color: rgba(255, 255, 255, 0.95);
      font-size: 14px;
      font-weight: 500;
      letter-spacing: 1px;
      text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
      position: relative;
      z-index: 1;
    }
    
    /* Body del card */
    .card-body {
      padding: 2.5rem 2rem;
    }
    
    .login-title {
      text-align: center;
      font-size: 20px;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 2rem;
      position: relative;
      padding-bottom: 12px;
    }
    
    .login-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      border-radius: 3px;
    }

    /* Inputs mejorados */
    .input-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .form-control {
      width: 100%;
      border: 2px solid #e8e8e8;
      border-radius: 12px;
      padding: 0.85rem 1rem;
      padding-right: 45px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
      color: #333;
      background: white;
    }
    
    .form-control:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 4px rgba(0, 191, 255, 0.1);
      outline: none;
    }
    
    .input-group-append {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      gap: 8px;
      z-index: 3;
    }
    
    .input-group-text, .icon-container {
      background: transparent;
      border: none;
      color: #aaa;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 16px;
    }

    .input-group:focus-within .input-group-text,
    .input-group:focus-within .icon-container i {
      color: var(--secondary-color);
    }

    .icon-container:hover {
      color: var(--primary-color);
    }

    /* Checkbox */
    .remember-section {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .remember-section input[type="checkbox"] {
      width: 18px;
      height: 18px;
      margin-right: 8px;
      cursor: pointer;
      accent-color: var(--primary-color);
    }

    .remember-section label {
      font-size: 14px;
      color: var(--dark-color);
      font-weight: 500;
      cursor: pointer;
      user-select: none;
    }

    /* Botón de login */
    .btn-login {
      width: 100%;
      border: none;
      background: linear-gradient(135deg, var(--primary-color), var(--neon-orange));
      color: white;
      padding: 0.9rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      font-size: 16px;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 
        0 6px 20px rgba(255, 69, 0, 0.4),
        0 0 20px rgba(255, 69, 0, 0.2);
      position: relative;
      overflow: hidden;
      z-index: 1;
      cursor: pointer;
    }
    
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0%;
      height: 100%;
      background: linear-gradient(135deg, var(--secondary-color), var(--neon-blue));
      transition: width 0.5s ease;
      z-index: -1;
    }
    
    .btn-login:hover::before {
      width: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 
        0 10px 30px rgba(255, 69, 0, 0.6),
        0 0 30px rgba(255, 69, 0, 0.4);
    }
    
    .btn-login:active {
      transform: translateY(-1px);
    }
    
    .btn-login i {
      margin-right: 8px;
    }

    /* Footer del card */
    .card-footer {
      background: linear-gradient(135deg, rgba(26, 26, 46, 0.95), rgba(42, 42, 68, 0.95));
      padding: 1.2rem;
      text-align: center;
      border-top: 1px solid rgba(255, 69, 0, 0.2);
    }

    .card-footer-text {
      color: rgba(255, 255, 255, 0.9);
      font-size: 13px;
      margin: 0;
      letter-spacing: 0.5px;
    }

    .card-footer-text i {
      color: var(--accent-color);
      margin-right: 5px;
    }

    /* Animaciones */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Responsive */
    @media (max-width: 576px) {
      .login-container {
        max-width: 95%;
      }

      .card-header {
        padding: 2rem 1.5rem 1rem;
      }

      .logo-container img {
        max-width: 140px;
      }

      .company-name {
        font-size: 24px;
      }

      .card-body {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Partículas brillantes -->
  <div class="particles-container">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  <!-- Contenedor de Login -->
  <div class="login-container">
    <div class="card">
      <!-- Header con Logo -->
      <div class="card-header">
        <div class="logo-container">
          <img src="img/grau.png" alt="Estación Goyo Logo">
        </div>
        <h1 class="company-name">⛽ ESTACIÓN GOYO</h1>
      </div>

      <!-- Body con Formulario -->
      <div class="card-body">
        <h2 class="login-title">Iniciar Sesión</h2>

        <div class="input-group">
          <input type="text" class="form-control" placeholder="Usuario" id="txt_usuario">
          <div class="input-group-append">
            <div class="input-group-text">
              <i class="fas fa-user"></i>
            </div>
          </div>
        </div>

        <div class="input-group">
          <input type="password" class="form-control" placeholder="Contraseña" id="txt_contra">
          <div class="input-group-append">
            <div class="input-group-text">
              <i class="fas fa-lock"></i>
            </div>
            <div class="icon-container" id="togglePassword">
              <i class="fas fa-eye"></i>
            </div>
          </div>
        </div>

        <div class="remember-section">
          <input type="checkbox" id="remember">
          <label for="remember">Recuérdame</label>
        </div>
        
        <button class="btn-login" id="entrar" onclick="Iniciar_Sesion()">
          <i class="fas fa-sign-in-alt"></i>Iniciar Sesión
        </button>
      </div>

      <!-- Footer -->
      <div class="card-footer">
        <p class="card-footer-text">
          <i class="fas fa-gas-pump"></i>
          Sistema de Gestión de Grifo © 2025
        </p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="plantilla/plugins/jquery/jquery.min.js"></script>
  <script src="plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="plantilla/dist/js/adminlte.min.js"></script>
  <script src="js/console_usuario.js?rev=<?php echo time(); ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const rmcheck = document.getElementById('remember'),
          usuarioInput = document.getElementById('txt_usuario'),
          passInput = document.getElementById('txt_contra'),
          togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
      const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passInput.setAttribute('type', type);
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    if (localStorage.checkbox && localStorage.checkbox !== "") {
      rmcheck.setAttribute("checked", "checked");
      usuarioInput.value = localStorage.usuario;
      passInput.value = localStorage.pass;
    } else {
      rmcheck.removeAttribute("checked");
      usuarioInput.value = "";
      passInput.value = "";
    }

    usuarioInput.focus();
    
    passInput.addEventListener("keyup", function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        document.getElementById("entrar").click();
      }
    });
    
    // Guardado de credenciales si está marcado "Recuérdame"
    document.getElementById('entrar').addEventListener('click', function() {
      if (rmcheck.checked && usuarioInput.value !== "" && passInput.value !== "") {
        localStorage.usuario = usuarioInput.value;
        localStorage.pass = passInput.value;
        localStorage.checkbox = rmcheck.value;
      } else {
        localStorage.usuario = "";
        localStorage.pass = "";
        localStorage.checkbox = "";
      }
    });
  </script>
</body>
</html>