<?php
// ========================================================
// 1. LÓGICA PHP: CONEXIÓN Y FILTRADO DE PRODUCTOS
// ========================================================
// Muestra errores para depuración (Quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php'; 

$search_term = $_GET['search'] ?? ''; 
$search_query = "%" . $search_term . "%"; 
$productos = [];

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

try {
    if (!empty($search_term)) {
        $sql = "SELECT id, nombre, precio, imagen_url FROM productos 
                WHERE nombre LIKE ? OR talle LIKE ? OR estado LIKE ?
                ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_query, $search_query, $search_query]);
    } else {
        $sql = "SELECT id, nombre, precio, imagen_url FROM productos ORDER BY id DESC";
        $stmt = $pdo->query($sql);
    }

    $productos = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al cargar los productos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Segunda mano - Morbido Ropas</title>
<link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="responsivecompu.css">
    <link rel="stylesheet" href="responsivetablet.css">
    <link rel="stylesheet" href="responsivecelular.css">
    <link rel="stylesheet" href="carpeta.css">
    
    <style>
        /* ----------------------------------- */
        /* 1. BARRA DE TÍTULO/FILTRO ESTÉTICO */
        /* ----------------------------------- */
        .section-title-bar {
            width: 85%;
            margin: 2% auto 0;
            text-align: center;
            padding: 2rem;
            border: 2px solid #000;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            background: #000;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 5px;
            box-sizing: border-box;
        }
        
        /* ----------------------------------- */
        /* 2. ESTILOS DE TARJETAS (Uniformidad y Hover) */
        /* ----------------------------------- */

        .product-card {
            display: flex; 
            flex-direction: column;
            min-height: 450px; 
            width: 100%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            border: 2px solid #000; 
            background: #000; /* CLAVE: Fondo negro para la tarjeta completa */
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-color: #e20000; 
        }

        .product-image {
            height: 300px; 
            width: 100%;
            overflow: hidden;
            background: #fff; /* CLAVE: Fondo blanco para el área de la imagen */
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            display: block;
            transition: transform 0.3s ease;
        }
        
        .product-name {
            height: 3rem; 
            line-height: 1.5rem; 
            overflow: hidden; 
            display: -webkit-box; 
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;

            padding: 10px 15px;
            text-align: center;
            font-weight: bold;
            background: #fff; /* CLAVE: Fondo blanco para el nombre */
            margin-bottom: 0; /* Quitar cualquier margen inferior */
        }
        
        .product-price {
            padding: 10px 15px;
            text-align: center;
            font-weight: bold;
            color: #e20000; 
            font-size: 1.2rem;
            background: #fff; /* CLAVE: Fondo blanco para el precio */
            margin-top: 0; /* Quitar cualquier margen superior */
            border-top: 2px solid #000; /* Línea de separación entre nombre y precio */
        }
    </style>

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
        <a href="cuenta.php" style="color: <?= $user_logged_in ? '#e20000' : 'inherit' ?>; font-weight: bold;">
            <i class="fa fa-user"></i> 
            <?= htmlspecialchars($user_name_display) ?>
        </a>
    </li>
  </ul>
</nav>


<div class="section-title-bar">
    <?php if (!empty($search_term)): ?>
        RESULTADOS PARA: "<?= htmlspecialchars($search_term) ?>"
    <?php else: ?>
        PRODUCTOS DE SEGUNDA MANO
    <?php endif; ?>
</div>

<section class="products-section">
    <div class="product-grid">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $p): ?>
                <a href="venta.php?id=<?= $p['id'] ?>">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        </div>
                        <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
                        <div class="product-price">$<?= number_format($p['precio'], 0, ',', '.') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 30px; font-size: 1.2rem;">
              No se encontraron productos disponibles.
              <?php if (!empty($search_term)): ?>
                  para la búsqueda: "<?= htmlspecialchars($search_term) ?>"
              <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <button class="view-more-button">Ver mas</button>
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

    <!-- boton de ver mas  -->

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Seleccionar los elementos
    const grid = document.querySelector('.product-grid');
    if (!grid) return; // Salir si no encuentra la grilla

    const items = grid.querySelectorAll('.product-card');
    const button = document.querySelector('.view-more-button');
    const itemsPerPage = 8; // Define cuántos productos se muestran inicialmente y cuántos se agregan en cada clic

    let visibleCount = itemsPerPage;

    // 2. Función para actualizar la visibilidad
    function updateVisibility() {
        // Recorrer todos los productos
        items.forEach((item, index) => {
            // Mostrar si el índice es menor que visibleCount
            if (index < visibleCount) {
                item.style.display = 'flex'; // Usamos 'flex' o 'block' según como esté definida tu .product-card
            } else {
                item.style.display = 'none'; // Ocultar
            }
        });

        // 3. Ocultar o mostrar el botón "Ver más"
        if (visibleCount >= items.length) {
            button.style.display = 'none'; // Ocultar si ya se mostraron todos
        } else {
            button.style.display = 'block'; // Mostrar si aún quedan productos
        }
    }

    // 4. Manejador de clic del botón
    if (button) {
        button.addEventListener('click', () => {
            // Aumentar el contador de productos visibles
            visibleCount += itemsPerPage;
            updateVisibility();
        });
    }

    // 5. Ejecutar al cargar la página para mostrar solo los primeros 'itemsPerPage'
    updateVisibility();
});
</script>



</body>
</html>