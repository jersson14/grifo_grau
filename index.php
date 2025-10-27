<?php
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
  <title>Iniciar Sesión - INCOCAT</title>

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
  <link rel="icon" href="img/incoca.png" type="image/jpg">
  <style>
    :root {
      --primary-color: #0a2463;
      --secondary-color: #3e92cc;
      --accent-color: #d8315b;
      --light-color: #fffaff;
      --dark-color: #1e1b18;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background-image: url('img/img1.jpg');
      background-size: cover;
      background-position: center;
      position: relative;
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(10, 36, 99, 0.85) 0%, rgba(216, 49, 91, 0.75) 100%);
      z-index: 0;
    }
    
    .glass-container {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      width: 100%;
      align-items: center;
      justify-content: space-between;
      padding: 2rem 1rem;
    }
    
    .header-section {
      width: 100%;
      text-align: center;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
      animation: fadeInDown 1s ease-out;
    }
    
    .title-container h1 {
      color: var(--light-color);
      font-family: 'Playfair Display', serif;
      font-size: clamp(28px, 4vw, 44px);
      font-weight: 700;
      letter-spacing: 0.05em;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
      margin-bottom: 15px;
      line-height: 1.2;
    }
    
    .title-container h2 {
      color: var(--light-color);
      font-family: 'Poppins', sans-serif;
      font-size: clamp(16px, 2vw, 24px);
      font-weight: 400;
      letter-spacing: 0.05em;
      text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
      opacity: 0.9;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      margin: auto;
      animation: fadeInUp 1s ease-out;
    }
    
    .card {
      border: none;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .login-card-body {
      border-radius: 20px;
      padding: 2.5rem;
      position: relative;
    }

    .login-card-body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
    }
    
    .logo-container {
      text-align: center;
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .logo-container img {
      max-width: 250px;
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
      transition: transform 0.3s ease;
    }
    
    .logo-container img:hover {
      transform: scale(1.02);
    }
    
    .login-box-msg {
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-size: 18px;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 1.5rem;
      letter-spacing: 0.05em;
      position: relative;
      padding-bottom: 10px;
    }
    
    .login-box-msg::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      border-radius: 3px;
    }

    .input-group {
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.3s ease;
    }
    
    /* Corrección para los bordes redondeados - Borde menos marcado */
    .form-control {
      height: auto;
      width: 100%;
      border: 1px solid #e8e8e8;
      border-radius: 30px !important;
      padding: 0.8rem 1rem;
      padding-right: 40px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
      color: #333;
      box-shadow: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }
    
    .form-control:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 3px 15px rgba(62, 146, 204, 0.15);
      outline: none;
    }
    
    /* Aseguramos que los iconos no afecten el borde redondeado */
    .input-group-append {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 9;
      display: flex;
      background: transparent;
      border: none;
    }
    
    .input-group-text, .icon-container {
      background-color: transparent;
      border: none;
      color: #aaa;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      font-size: 16px;
      margin-left: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .input-group:focus-within .input-group-text,
    .input-group:focus-within .icon-container i {
      color: var(--secondary-color);
    }
    
    /* Marca de texto en rojo para el nombre de usuario - referencia de la imagen */
    #txt_usuario::placeholder {
      color: #999;
    }
    
    .form-control[type="text"] {
      color: #e74c3c;
      font-weight: 500;
    }
    


    .icheck-primary label {
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      color: var(--dark-color);
      font-weight: 500;
      cursor: pointer;
    }
    
    .btn-login {
      border: none;
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(10, 36, 99, 0.3);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0%;
      height: 100%;
      background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
      transition: width 0.5s ease;
      z-index: -1;
    }
    
    .btn-login:hover::before {
      width: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(10, 36, 99, 0.4);
    }
    
    .btn-login:active {
      transform: translateY(-1px);
    }
    
    .btn-login i {
      margin-right: 8px;
      font-size: 16px;
    }

    footer {
      width: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      color: white;
      text-align: center;
      padding: 15px 0;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      letter-spacing: 1px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
      animation: fadeInUp 1s ease-out;
    }
    
    input[type="checkbox"] {
      accent-color: var(--primary-color);
    }

    /* Estilos para el enfoque de campos como se muestra en la imagen */
    .form-control:focus ~ .input-group-append .input-group-text .fas,
    .form-control:focus ~ .input-group-append .icon-container .fas {
      color: var(--secondary-color);
    }
    
    /* Efecto de texto escrito en el campo de usuario */
    .input-text-highlight {
      color: #e74c3c !important;
      font-weight: 500;
    }
   
    /* Asegurarnos que los iconos de los inputs se ven bien */
    .fas {
      position: relative;
      z-index: 5;
    }
    
    /* Input de contraseña con aspecto de puntos como en la imagen */
    .password-dots {
      letter-spacing: 3px;
      font-weight: bold;
    }
    
    /* Eliminar posibles conflictos de estilos AdminLTE que afectan a los inputs */
    .input-group > .form-control:not(:last-child) {
      border-top-right-radius: 30px !important;
      border-bottom-right-radius: 30px !important;
    }
    
    /* Evitar que AdminLTE o Bootstrap sobreescriban nuestros estilos */
    .input-group .form-control {
      z-index: 1;
    }
    
    /* Animaciones */
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
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
    
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(62, 146, 204, 0.4);
      }
      70% {
        box-shadow: 0 0 0 15px rgba(62, 146, 204, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(62, 146, 204, 0);
      }
    }
    
    /* Responsive adjustments */
    @media (min-width: 992px) {
      .header-section {
        max-width: 80%;
      }
    }
    
    @media (max-width: 768px) {
      .header-section {
        padding: 1rem 0.5rem;
      }
      
      .login-card-body {
        padding: 2rem 1.5rem;
      }
    }
    
    @media (max-width: 480px) {
      .header-section {
        padding: 0.8rem 0.3rem;
      }
      
      .title-container h1 {
        font-size: 6.5vw;
      }
      
      .title-container h2 {
        font-size: 4.5vw;
      }
      
      .login-card-body {
        padding: 1.5rem 1rem;
      }
      
      .logo-container img {
        max-width: 200px;
      }
    }
  </style>
</head>
<body>
  <div class="glass-container">
    <!-- Títulos -->
    <div class="header-section">
      <div class="title-container">
        <h1><b>Empresa de Saneamiento de Títulos de Propiedad INCOCAT S.R.L.</b></h1>
        <h2><b>"SISTEMA WEB DE SANEAMIENTO DE TÍTULOS DE PROPIEDAD"</b></h2>
      </div>
    </div>

    <!-- Login -->
    <div class="login-box">
      <div class="card">
        <div class="login-card-body">
          <div class="logo-container">
            <img src="img/incocat.jpeg" alt="INCOCAT Logo" class="img-fluid" style="width:100%">
          </div>
          
          <p class="login-box-msg">
            <b>DATOS DEL USUARIO</b>
          </p>

          <div class="input-group">
            <input type="text" class="form-control" placeholder="Ingrese su usuario" id="txt_usuario">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>

          <div class="input-group">
            <input type="password" class="form-control" placeholder="Ingrese su contraseña" id="txt_contra">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
              <div class="icon-container">
                <i class="fas fa-eye" id="togglePassword"></i>
              </div>
            </div>
          </div>

          <div class="row align-items-center mb-4">
            <div class="col-12 d-flex align-items-center">
              <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">Recuérdame</label>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12">
              <button class="btn btn-login btn-block" id="entrar" onclick="Iniciar_Sesion()">
                <i class='fas fa-sign-in-alt'></i><b>Iniciar Sesión</b>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer>
      Empresa de Saneamiento de Títulos de Propiedad INCOCAT S.R.L. &copy; 2025
    </footer>
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
      this.classList.toggle('fa-eye-slash');
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
    
    // Aplicar clase de resaltado al texto cuando se escribe
    usuarioInput.addEventListener("input", function() {
      if (this.value.length > 0) {
        this.classList.add('input-text-highlight');
      } else {
        this.classList.remove('input-text-highlight');
      }
    });
    
    // Aplicar clase de puntos a la contraseña
    passInput.addEventListener("input", function() {
      if (this.value.length > 0) {
        this.classList.add('password-dots');
      } else {
        this.classList.remove('password-dots');
      }
    });
    
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
    
    // Añadimos un efecto de animación al botón tras cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        document.querySelector('.btn-login').style.animation = 'pulse 2s infinite';
      }, 1500);
      
      // Si hay datos guardados en localStorage, aplicar las clases de estilo
      if (usuarioInput.value.length > 0) {
        usuarioInput.classList.add('input-text-highlight');
      }
      
      if (passInput.value.length > 0) {
        passInput.classList.add('password-dots');
      }
    });
  </script>
</body>
</html>