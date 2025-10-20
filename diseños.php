<?php
// ========================================================
// REQUERIDO EN CADA ARCHIVO (index.php, venta.php, etc.)
// ========================================================

// 1. Asegurar el inicio de sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php'; 

// 2. Definir las variables para el NAV
$user_logged_in = isset($_SESSION['usuario_id']); 
$user_name = $_SESSION['usuario_name'] ?? 'Invitado';

// Variable que usaremos en el HTML para mostrar el nombre o 'Cuenta'
$user_name_display = $user_logged_in ? $user_name : 'Cuenta'; 

// Contador del carrito para el header
$total_items_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_items_carrito += $item['quantity'] ?? 0;
    }
}
$search_term = $_GET['search'] ?? ''; 
// ... (El resto del código PHP específico de la página continúa aquí)
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="diseños.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/carrusel_secciones.css">
    <link rel="stylesheet" href="responsivecompu.css">
    <link rel="stylesheet" href="responsivetablet.css">
    <link rel="stylesheet" href="responsivecelular.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- imagenes -->
    <title>Morbido Ropas</title>

    </head>
<body>

    <header>
  <div class="menu-toggle"><i class="fa fa-bars"></i></div>
  <div class="logo">logo</div>
  <div class="search-bar">
    <input type="text" placeholder="¿Qué estás buscando?">
    <button><i class="fa fa-search"></i></button>
  </div>
</header>
    
<nav>
  <ul class="nav-left">
    <li><a href="index.php">Inicio</a></li>
    <li><a href="segundamano.php">Segunda mano</a></li>
    <li><a href="diseños.php">Diseños</a></li>
     <li><a href="https://api.whatsapp.com/send?phone=1164764255" target="_blank">Contacto</a></li>
    <li><a href="#">Sobre nosotros</a></li>
  </ul>
  <ul class="nav-right">
    <li><a href="carrito.php"><i class="fa fa-shopping-cart"></i> Carrito (<?= $total_items_carrito ?>)</a></li>
    
    <li>
        <a href="cuenta.php" style="color: <?= $user_logged_in ? '#e20000' : 'inherit' ?>; font-weight: bold;">
            <i class="fa fa-user"></i> 
            <?= htmlspecialchars($user_name_display) ?>
        </a>
    </li>
  </ul>
</nav>
<!------------------------------------------------------------------------------------------------->

<main class="content-diseños">

  <section class="seccion-intro">
    <div class="titulo-diseños">Diseños</div>
    <div class="intro-texto">
      bienvenido a diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo diseños texto largo
    </div>
  </section>

  <!-- SECCIÓN SUPERIOR -->
  <section class="bloque-diseño diseño-superior">

    <div class="botones-lateral botones-izq">
      <button class="boton-prenda" data-target="imagenPrincipalSuperior"><img src="sobrenosotros.jpg" alt="Prenda 2"></button>
      <button class="boton-prenda" data-target="imagenPrincipalSuperior"><img src="ethan.jpg" alt="Prenda 3"></button>
      <button class="boton-prenda" data-target="imagenPrincipalSuperior"><img src="segunda mano.jpg" alt="Prenda 4"></button>
    </div>

    <div class="contenedor-prenda prenda-grande" id="imagenPrincipalSuperior">
      <img src="ethan.jpg" alt="Prenda Principal" class="img-prenda-principal">
    </div>

    <div class="info-diseño info-derecha">
      <div class="info-item nombre-prenda">nombre de la prenda</div>
      <div class="info-item inspiracion">inspiracion</div>
      <div class="info-item diseñador">diseñador</div>
    </div>

  </section>

  <!-- SECCIÓN INFERIOR -->
  <section class="bloque-diseño diseño-inferior">

    <div class="info-diseño info-izquierda">
      <div class="info-item nombre-prenda">nombre de la prenda</div>
      <div class="info-item inspiracion">inspiracion</div>
      <div class="info-item diseñador">diseñador</div>
    </div>

    <div class="contenedor-prenda prenda-grande" id="imagenPrincipalInferior">
      <img src="ethan.jpg" alt="Prenda Principal" class="img-prenda-principal">
    </div>

    <div class="botones-lateral botones-der">
      <button class="boton-prenda" data-target="imagenPrincipalInferior"><img src="ethan.jpg" alt="Prenda 2"></button>
      <button class="boton-prenda" data-target="imagenPrincipalInferior"><img src="sobrenosotros.jpg" alt="Prenda 3"></button>
      <button class="boton-prenda" data-target="imagenPrincipalInferior"><img src="segunda mano.jpg" alt="Prenda 4"></button>
    </div>
  </section>

</main> 

<footer class="footer-seccion">
  <div class="footer-grid">

    <!-- Mercado Pago (solo logo en su caja) -->
    <div class="footer-box pago">
      <img src="mercadopago.png" alt="Mercado Pago" class="mp-img">
    </div>

    <!-- Columna central: Donaciones (link) arriba y Alias (texto) abajo -->
    <div class="footer-box middle">
      <div class="stack">
    <strong>Donaciones</strong>

        <div class="stack-item alias-text">
          Alias: <strong>el.morbido</strong>
        </div>
      </div>
    </div>

    <!-- Redes Sociales -->
    <div class="footer-box redes">
      <h4>Nuestras Redes</h4>
      <a href="https://instagram.com/ropas.elmorbido" target="_blank">
        <i class="fab fa-instagram"></i> ropas.elmorbido
      </a>
      <a href="https://tiktok.com" target="_blank">
        <i class="fab fa-tiktok"></i> ropas.elmorbido
      </a>
    </div>

  </div>

  <div class="footer-copy">
    <p>&copy; 2025 Morbido Ropas. Todos los derechos reservados.</p>
  </div>
</footer>
<!------------------------------------------------------------------------------------------------->
<script src="scripts.js">

document.addEventListener('DOMContentLoaded', () => {
  // Seleccionamos todos los botones laterales
  const botones = document.querySelectorAll('.boton-prenda');

  botones.forEach(boton => {
    boton.addEventListener('click', () => {
      // Obtener la imagen de la miniatura
      const miniatura = boton.querySelector('img');
      const nuevaSrc = miniatura.getAttribute('src');

      // Ver a qué imagen principal pertenece
      const targetId = boton.getAttribute('data-target');
      const contenedorPrincipal = document.getElementById(targetId);
      const imgPrincipal = contenedorPrincipal.querySelector('.img-prenda-principal');

      // Cambiar la imagen principal
      imgPrincipal.setAttribute('src', nuevaSrc);

      // (Opcional) marcar la miniatura activa
      const grupo = boton.closest('.botones-lateral');
      grupo.querySelectorAll('.boton-prenda').forEach(b => b.classList.remove('activo'));
      boton.classList.add('activo');
    });
  });
});

</script>
</body>
</html>
