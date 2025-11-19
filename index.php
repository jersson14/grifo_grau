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

    /* Partículas brillantes mejoradas */
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
      width: 12px;
      height: 12px;
      background: radial-gradient(circle, #FFD700, #FFA500);
      animation-duration: 8s;
      animation-delay: 0s;
    }

    .particle:nth-child(2) {
      left: 25%;
      width: 15px;
      height: 15px;
      background: radial-gradient(circle, #FF6B35, #FF4500);
      animation-duration: 12s;
      animation-delay: 2s;
    }

    .particle:nth-child(3) {
      left: 40%;
      width: 10px;
      height: 10px;
      background: radial-gradient(circle, #00D4FF, #0080FF);
      animation-duration: 10s;
      animation-delay: 4s;
    }

    .particle:nth-child(4) {
      left: 55%;
      width: 14px;
      height: 14px;
      background: radial-gradient(circle, #FF1493, #FF69B4);
      animation-duration: 14s;
      animation-delay: 1s;
    }

    .particle:nth-child(5) {
      left: 70%;
      width: 11px;
      height: 11px;
      background: radial-gradient(circle, #9D4EDD, #7209B7);
      animation-duration: 11s;
      animation-delay: 3s;
    }

    .particle:nth-child(6) {
      left: 85%;
      width: 13px;
      height: 13px;
      background: radial-gradient(circle, #FFD700, #FFED4E);
      animation-duration: 9s;
      animation-delay: 5s;
    }

    .particle:nth-child(7) {
      left: 15%;
      width: 9px;
      height: 9px;
      background: radial-gradient(circle, #00FFF5, #00CED1);
      animation-duration: 13s;
      animation-delay: 6s;
    }

    .particle:nth-child(8) {
      left: 65%;
      width: 16px;
      height: 16px;
      background: radial-gradient(circle, #FF6B35, #FFB627);
      animation-duration: 15s;
      animation-delay: 7s;
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

    /* Ondas vibrantes mejoradas */
    .waves-container {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 250px;
      z-index: 1;
      pointer-events: none;
      overflow: hidden;
    }

    .wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 200%;
      height: 100%;
      opacity: 0.4;
    }

    .wave:nth-child(1) {
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120'%3E%3Cpath d='M0,50 Q300,0 600,50 T1200,50 L1200,120 L0,120 Z' fill='%23FF6B35'/%3E%3C/svg%3E");
      background-size: 50% 100%;
      animation: waveMove 15s linear infinite;
      opacity: 0.3;
    }

    .wave:nth-child(2) {
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120'%3E%3Cpath d='M0,70 Q300,20 600,70 T1200,70 L1200,120 L0,120 Z' fill='%2300D4FF'/%3E%3C/svg%3E");
      background-size: 50% 100%;
      animation: waveMove 20s linear infinite reverse;
      opacity: 0.25;
    }

    .wave:nth-child(3) {
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120'%3E%3Cpath d='M0,60 Q300,10 600,60 T1200,60 L1200,120 L0,120 Z' fill='%23FFD700'/%3E%3C/svg%3E");
      background-size: 50% 100%;
      animation: waveMove 18s linear infinite;
      opacity: 0.2;
    }

    @keyframes waveMove {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    /* Círculos flotantes decorativos */
    .circles-container {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      opacity: 0.15;
      animation: floatCircle linear infinite;
    }

    .circle:nth-child(1) {
      width: 80px;
      height: 80px;
      left: 10%;
      top: 20%;
      background: linear-gradient(135deg, #FF6B35, #FFB627);
      animation-duration: 20s;
      animation-delay: 0s;
    }

    .circle:nth-child(2) {
      width: 120px;
      height: 120px;
      right: 15%;
      top: 60%;
      background: linear-gradient(135deg, #00D4FF, #0080FF);
      animation-duration: 25s;
      animation-delay: 5s;
    }

    .circle:nth-child(3) {
      width: 60px;
      height: 60px;
      left: 50%;
      top: 80%;
      background: linear-gradient(135deg, #FFD700, #FFA500);
      animation-duration: 18s;
      animation-delay: 3s;
    }

    .circle:nth-child(4) {
      width: 100px;
      height: 100px;
      right: 40%;
      top: 10%;
      background: linear-gradient(135deg, #9D4EDD, #7209B7);
      animation-duration: 22s;
      animation-delay: 7s;
    }

    @keyframes floatCircle {
      0% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0.15;
      }
      50% {
        transform: translateY(-50px) translateX(50px) scale(1.2);
        opacity: 0.25;
      }
      100% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0.15;
      }
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
        radial-gradient(ellipse at 50% 50%, rgba(255, 215, 0, 0.2) 0%, transparent 50%),
        radial-gradient(ellipse at 30% 70%, rgba(138, 43, 226, 0.25) 0%, transparent 45%),
        radial-gradient(ellipse at 70% 30%, rgba(255, 20, 147, 0.2) 0%, transparent 40%);
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
      padding: 2rem 1rem;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(15px);
      border-radius: 25px;
      border: 2px solid rgba(255, 69, 0, 0.3);
      box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 0 20px rgba(255, 69, 0, 0.2),
        inset 0 0 20px rgba(255, 69, 0, 0.05);
      margin-bottom: 2rem;
      animation: fadeInDown 1s ease-out, neonGlow 3s ease-in-out infinite;
      position: relative;
      overflow: hidden;
    }
    
    .header-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 69, 0, 0.1),
        transparent
      );
      animation: shine 3s infinite;
    }
    
    @keyframes shine {
      0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
      100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    @keyframes neonGlow {
      0%, 100% {
        box-shadow: 
          0 8px 32px rgba(0, 0, 0, 0.3),
          0 0 20px rgba(255, 69, 0, 0.2),
          inset 0 0 20px rgba(255, 69, 0, 0.05);
      }
      50% {
        box-shadow: 
          0 8px 32px rgba(0, 0, 0, 0.3),
          0 0 40px rgba(255, 69, 0, 0.4),
          inset 0 0 30px rgba(255, 69, 0, 0.1);
      }
    }
    
    .title-container {
      position: relative;
      z-index: 1;
    }
    
    .title-container h1 {
      color: var(--light-color);
      font-family: 'Playfair Display', serif;
      font-size: clamp(32px, 5vw, 52px);
      font-weight: 700;
      letter-spacing: 0.08em;
      text-shadow: 
        0 0 10px rgba(255, 69, 0, 0.8),
        0 0 20px rgba(255, 69, 0, 0.6),
        0 0 30px rgba(255, 69, 0, 0.4),
        2px 2px 8px rgba(0, 0, 0, 0.8);
      margin-bottom: 15px;
      line-height: 1.3;
      animation: textGlow 2s ease-in-out infinite;
    }
    
    @keyframes textGlow {
      0%, 100% {
        text-shadow: 
          0 0 10px rgba(255, 69, 0, 0.8),
          0 0 20px rgba(255, 69, 0, 0.6),
          0 0 30px rgba(255, 69, 0, 0.4),
          2px 2px 8px rgba(0, 0, 0, 0.8);
      }
      50% {
        text-shadow: 
          0 0 20px rgba(255, 69, 0, 1),
          0 0 30px rgba(255, 69, 0, 0.8),
          0 0 40px rgba(255, 69, 0, 0.6),
          2px 2px 8px rgba(0, 0, 0, 0.8);
      }
    }
    
    .title-container h2 {
      color: var(--accent-color);
      font-family: 'Poppins', sans-serif;
      font-size: clamp(18px, 2.5vw, 28px);
      font-weight: 600;
      letter-spacing: 0.1em;
      text-shadow: 
        0 0 10px rgba(255, 215, 0, 0.8),
        0 0 20px rgba(255, 215, 0, 0.4),
        1px 1px 4px rgba(0, 0, 0, 0.8);
      opacity: 1;
      margin-top: 10px;
    }
    
    .subtitle-location {
      color: var(--secondary-color);
      font-size: clamp(14px, 1.8vw, 20px);
      font-weight: 400;
      letter-spacing: 0.05em;
      text-shadow: 
        0 0 10px rgba(0, 191, 255, 0.6),
        1px 1px 4px rgba(0, 0, 0, 0.6);
      margin-top: 8px;
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
      background: linear-gradient(45deg, var(--primary-color), var(--neon-orange));
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 
        0 5px 15px rgba(255, 69, 0, 0.4),
        0 0 20px rgba(255, 69, 0, 0.2);
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
      background: linear-gradient(45deg, var(--secondary-color), var(--neon-blue));
      transition: width 0.5s ease;
      z-index: -1;
    }
    
    .btn-login:hover::before {
      width: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 
        0 8px 25px rgba(255, 69, 0, 0.6),
        0 0 30px rgba(255, 69, 0, 0.4);
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
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(10px);
      color: white;
      text-align: center;
      padding: 15px 0;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      letter-spacing: 1px;
      border-top: 2px solid rgba(255, 69, 0, 0.3);
      box-shadow: 
        0 -5px 20px rgba(0, 0, 0, 0.3),
        0 0 15px rgba(255, 69, 0, 0.2);
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
  <!-- Círculos flotantes decorativos -->
  <div class="circles-container">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
  </div>

  <!-- Partículas brillantes -->
  <div class="particles-container">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  <!-- Ondas vibrantes -->
  <div class="waves-container">
    <div class="wave"></div>
    <div class="wave"></div>
    <div class="wave"></div>
  </div>

  <div class="glass-container">
    <!-- Títulos -->
    <div class="header-section">
      <div class="title-container">
        <h1><b>⛽ ESTACIÓN GOYO ⛽</b></h1>
        <h2><b>ULLPUTO - CHUQUI</b></h2>
        <p class="subtitle-location"><i class="fas fa-map-marker-alt"></i> Sistema de Gestión de Grifo</p>
      </div>
    </div>

    <!-- Login -->
    <div class="login-box">
      <div class="card">
        <div class="login-card-body">
          <div class="logo-container">
            <img src="img/grau.png" alt="INCOCAT Logo" class="img-fluid" style="width:100%">
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
      <i class="fas fa-gas-pump"></i> Estación Goyo - Ullputo, Chuqui &copy; 2025 | Sistema de Gestión de Grifo
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