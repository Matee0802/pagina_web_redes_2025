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

   <div class="carousel">
  <div class="carousel-content">
    <div class="slide active">
      <img src="img1.jpg" alt="Imagen 1">
    </div>
    <div class="slide">
      <img src="img2.jpg" alt="Imagen 2">
    </div>
    <div class="slide">
      <img src="img3.jpg" alt="Imagen 3">
      
    </div>
    
    <!-- ...otras slides si existen... -->
  </div>
  <button class="arrow left">&#10094;</button>
  <button class="arrow right">&#10095;</button>
</div>

    <!-- Cuadros -->
<div class="cards-section">
  <button class="card" onclick="location.href='diseños.php'">
    <img src="diseño.jpg" alt="Diseños">
    <span class="card-text">Diseños</span>
    <span class="card-overlay"></span>
  </button>
  <button class="card" onclick="location.href='segundamano.php'">
    <img src="segunda mano.jpg" alt="Segunda mano" >
    <span class="card-text">Segunda mano</span>
    <span class="card-overlay"></span>
  </button>
  <button class="card" onclick="location.href='sobrenosotros.php'">
    <img src="sobrenosotros.jpg" alt="Sobre nosotros" >
    <span class="card-text">Sobre nosotros</span>
    <span class="card-overlay"></span>
  </button>
</div>
  <section class="text-box">
    <p><strong>
      hola, bienvenido a nuestro proyecto de indumentaria, incluimos diseños de ropa echos por nosotros y venta de prendas de segunda mano para financiarlos, tambien en uno de los apartados podran leer sobre nosotros y quienes emprezaron este proyecto.
      se agradece mucho que visitaran nuestra pagina, si gustan pueden apoyarnos con una donacion al alias de abajo o con la compra de una prenda ambas se agradecerian mucho y nos apoyaria para seguir con este pequeño proyecto.
      buscamos que la ropa sean recuerdos, personalidad y expresion.</strong>
    </p>
  </section>

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
</script>
</body>
</html>
