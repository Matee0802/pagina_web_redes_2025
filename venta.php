<?php
// ========================================================
// Archivo: venta.php
// Objetivo: Muestra los detalles completos de un solo producto
// ========================================================
require_once 'conexion.php'; 

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

// 1. Obtener el ID del producto de la URL
$producto_id = $_GET['id'] ?? 0;

// Validar ID
if ($producto_id == 0 || !is_numeric($producto_id)) {
    // Si no hay ID válido, redirige o muestra un error
    header("Location: segundamano.php"); 
    exit();
}

try {
    // 2. Consulta para obtener TODOS los detalles de UN solo producto
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        die("Producto no encontrado.");
    }
    
    // Extracción y limpieza de datos (el código que me enviaste)
    $nombre = htmlspecialchars($producto['nombre']);
    $precio = htmlspecialchars($producto['precio']);
    $talle = htmlspecialchars($producto['talle']);
    $medidas = htmlspecialchars($producto['medidas']);
    $estado = htmlspecialchars($producto['estado']);
    $imagen_principal = htmlspecialchars($producto['imagen_url']); 
    
    // *** STOCK: Extraer el stock del producto, asumiendo que la columna se llama 'stock' ***
    $stock = $producto['stock'] ?? 99; 
    
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Venta - Morbido Ropas</title>
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="styles2.css">
</head>

<body>

<header>
  <div class="menu-toggle"><i class="fa fa-bars"></i></div>
  <div class="logo">logo</div>
  <form action="segundamano.php" method="get" class="search-bar">
    <input type="text" name="search" 
           placeholder="¿Qué estás buscando?" 
           value="<?= htmlspecialchars($search_term ?? '') ?>">
    <button type="submit"><i class="fa fa-search"></i></button>
  </form>
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
        <a href="cuenta.php" style="color:<?= $user_logged_in ? '#e20000' : 'inherit' ?>; font-weight: bold;">
            <i class="fa fa-user"></i> 
            <?= htmlspecialchars($user_name_display) ?>
        </a>
    </li>
  </ul>
</nav>



<section class="product-view">
    <div class="main-image"><img src="<?= $imagen_principal ?>" alt="<?= $nombre ?>"></div> 

    <div class="gallery-column">
        <div class="gallery-thumbnail"><img class="thumbnail" src="<?= $imagen_principal ?>" alt=""></div>
        <div class="gallery-thumbnail"><img class="thumbnail" src="foto2.jpeg" alt=""></div>
        <div class="gallery-thumbnail"><img class="thumbnail" src="foto3.jpeg" alt=""></div>
    </div>
    
 <div class="info-column">
    
    <h1 class="product-name-title" 
        style="
            color: #ffffffff !important; 
            font-size: 2.2rem; 
            margin-bottom: 5px;
            barckground-color: #ffffffff !important;
        ">
        <?= htmlspecialchars($producto['nombre']) ?>
    </h1>
    
    <div class="price-box" 
        style="
            color: #ffffffff !important; 
            font-size: 1.8rem; 
            font-weight: bold; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #000; 
            margin-bottom: 20px;
            background-color: #000000ff !important;
        ">
        $<?= number_format($producto['precio'], 0, ',', '.') ?>
    </div>

    <div class="info-box" style="color: #fff; background-color: #000 !important; padding: 15px;">
        <strong style="border-bottom: 2px solid #fff; padding-bottom: 5px; display: block; margin-bottom: 10px;">
            Medidas Técnicas
        </strong>
        <strong style="color: #fff !important;">Talle:</strong> <?= htmlspecialchars($producto['talle'] ?? 'N/A') ?><br><br>
        
        <strong style="color: #fff !important;">Medidas:</strong>
        <p><?= nl2br(htmlspecialchars($producto['medidas'] ?? 'No especificadas')) ?></p><br>
        
        <strong style="color: #fff !important;">Estado:</strong> <?= htmlspecialchars($producto['estado'] ?? 'No especificado') ?>
    </div>
    
 <form action="carrito.php" method="post">

 <div class="cantidad-box" style="margin-bottom: 20px !important; display: flex; align-items: center; gap: 15px;">
 <label for="quantity" style="color: #000 !important; font-weight: bold;">Cantidad:</label>
 <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $stock ?>"
    style="
        color: #000 !important; 
        background-color: #fff !important; 
        border: 2px solid #000 !important; 
        text-align: center; 
        width: 80px; 
        padding: 0.7rem;
    ">
 </div>

 <input type="hidden" name="action" value="add">
 <input type="hidden" name="product_id" value="<?= $producto['id'] ?>">

 <button type="submit" class="add-to-cart-button"
    style="
        background-color: #e20000 !important; 
        color: #fff !important; 
        width: 100% !important; 
        padding: 1rem 0; /* Más alto */
        font-weight: bold;
        border: 2px solid #e20000 !important;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 5px; /* Esquinas redondeadas */
    ">
    <i class="fa fa-shopping-cart" style="color: #fff !important; margin-right: 10px; font-size: 1.1rem;"></i> Añadir al Carrito
 </button>
 </form>

</div>
</section>

<footer class="footer-seccion">
  <div class="footer-grid">
    <div class="footer-box pago">
      <img src="mercadopago.png" alt="Mercado Pago" class="mp-img">
    </div>
    <div class="footer-box middle">
      <div class="stack">
    <strong>Donaciones</strong>
        <div class="stack-item alias-text">
          Alias: <strong>el.morbido</strong>
        </div>
      </div>
    </div>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.querySelector(".menu-toggle");
  const menus = document.querySelectorAll("nav ul");
  toggle.addEventListener("click", () => {
    menus.forEach(menu => menu.classList.toggle("show"));
  });
});
</script>


<script>

  document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.querySelector(".menu-toggle");
  const menus = document.querySelectorAll("nav ul");

  toggle.addEventListener("click", () => {
    menus.forEach(menu => menu.classList.toggle("show"));
  });
});

document.addEventListener("DOMContentLoaded", function () {
  // Menú hamburguesa (ya existente)
  const toggle = document.querySelector(".menu-toggle");
  const menus = document.querySelectorAll("nav ul");
  toggle.addEventListener("click", () => {
    menus.forEach(menu => menu.classList.toggle("show"));
  });

  // --- CAMBIO DE IMÁGENES EN LA GALERÍA ---
  const mainImage = document.querySelector(".main-image img");
  const thumbnails = document.querySelectorAll(".gallery-thumbnail img");

  thumbnails.forEach(thumbnail => {
    thumbnail.addEventListener("click", () => {
      // Cambia la imagen principal al src de la miniatura
      mainImage.src = thumbnail.src;

      // (Opcional) marca la miniatura activa con un borde
      thumbnails.forEach(t => t.parentElement.style.border = "2px solid #000");
      thumbnail.parentElement.style.border = "2px solid red";
    });
  });
});
</script>


</body>
</html>