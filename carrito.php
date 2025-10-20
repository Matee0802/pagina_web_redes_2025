<?php
// ========================================================
// Archivo: carrito.php
// OBJETIVO: Carrito de Compras con LÓGICA DE SIMULACIÓN DE CHECKOUT.
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

// Iniciar sesión para usar el carrito
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Variables de sesión para el usuario
$usuario_id = $_SESSION['usuario_id'] ?? NULL; 
$usuario_name = $_SESSION['usuario_name'] ?? 'Cliente';

// ========================================================
// B. CALCULAR TOTALES
// ========================================================

$subtotal = 0;
$total_items = 0;

foreach ($_SESSION['carrito'] as $id => $item) {
    // Usamos el operador de coalescencia nula (??) para evitar Undefined Index si faltan claves
    $cantidad = intval($item['quantity'] ?? 1); 
    $precio = floatval($item['precio'] ?? 0); 
    
    $subtotal += $precio * $cantidad;
    $total_items += $cantidad;
}

// Lógica de Envío
$gastos_envio = ($subtotal > 0 && $subtotal < 5000) ? 500 : 0; 
$total_a_pagar = $subtotal + $gastos_envio;


// ========================================================
// A. PROCESAR ACCIONES (Añadir/Actualizar/Eliminar/CHECKOUT)
// ========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);

    // ----------------------------------------------------------------
    // LÓGICA AGREGAR PRODUCTO (ACTION: add)
    // ----------------------------------------------------------------
    if ($action === 'add' && $product_id > 0) {
        
        // 1. Obtener detalles del producto de la BD (asumiendo tabla 'productos')
        $stmt_product = $pdo->prepare("SELECT id, nombre, precio, imagen_url FROM productos WHERE id = ?");
        $stmt_product->execute([$product_id]);
        $product_data = $stmt_product->fetch();

        if ($product_data) {
            $quantity_to_add = intval($_POST['quantity'] ?? 1);
            if ($quantity_to_add <= 0) $quantity_to_add = 1;

            // 2. Si el producto ya existe en el carrito, solo incrementa la cantidad
            if (isset($_SESSION['carrito'][$product_id])) {
                $_SESSION['carrito'][$product_id]['quantity'] += $quantity_to_add;
                $_SESSION['mensaje'] = "Cantidad de '" . $product_data['nombre'] . "' actualizada en el carrito.";
            } else {
                // 3. Si es un producto nuevo, añádelo con todos sus detalles
                $_SESSION['carrito'][$product_id] = [
                    'id'           => $product_data['id'],
                    'nombre'       => $product_data['nombre'],
                    'precio'       => floatval($product_data['precio']),
                    'imagen_url'   => $product_data['imagen_url'],
                    'quantity'     => $quantity_to_add
                ];
                $_SESSION['mensaje'] = "Producto '" . $product_data['nombre'] . "' agregado al carrito.";
            }
        } else {
            $_SESSION['mensaje'] = "Error: El producto no pudo ser agregado o no existe.";
        }
    }
    // ----------------------------------------------------------------

    // --- Lógica de Actualizar (Update) ---
    elseif ($action === 'update' && $product_id > 0) {
        $new_quantity = intval($_POST['quantity'] ?? 0);
        
        if ($new_quantity > 0) {
             $_SESSION['carrito'][$product_id]['quantity'] = $new_quantity;
             $_SESSION['mensaje'] = "Carrito actualizado.";
        } else {
            unset($_SESSION['carrito'][$product_id]);
            $_SESSION['mensaje'] = "Producto eliminado del carrito.";
        }
    }
    
    // *** 3. PROCESAR CHECKOUT (Guardar en Base de Datos - SIMULACIÓN) ***
    elseif ($action === 'checkout' && $total_items > 0) {
        
        if (!$usuario_id) {
            $_SESSION['mensaje'] = "Debes iniciar sesión para finalizar la compra.";
            header("Location: cuenta.php?mode=login");
            exit();
        }

        // CAMBIO CLAVE para la simulación: Estado y Método de Pago
        $estado_simulado = "procesado"; 
        $metodo_pago_simulado = "Simulación Exitosa"; 

        try {
            $pdo->beginTransaction(); 

            // 1. Insertar el Pedido principal
            $stmt_pedido = $pdo->prepare("
                INSERT INTO pedidos (usuario_id, total, estado, metodo_pago) 
                VALUES (?, ?, ?, ?)
            ");
            // Se usa el estado de simulación ('procesado')
            $stmt_pedido->execute([$usuario_id, $total_a_pagar, $estado_simulado, $metodo_pago_simulado]);
            $pedido_id = $pdo->lastInsertId(); 

            // 2. Insertar los detalles del pedido y actualizar el stock (SE DESCUENTA EL STOCK)
            $stmt_detalle = $pdo->prepare("
                INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt_stock = $pdo->prepare("
                UPDATE productos SET stock = stock - ? WHERE id = ?
            ");

            foreach ($_SESSION['carrito'] as $item) {
                // a) Insertar detalle
                $stmt_detalle->execute([
                    $pedido_id, 
                    $item['id'], 
                    $item['quantity'], 
                    $item['precio']
                ]);

                // b) Actualizar stock
                $stmt_stock->execute([$item['quantity'], $item['id']]);
            }

            // 3. Confirmar la transacción
            $pdo->commit();

            // 4. Limpiar el carrito
            unset($_SESSION['carrito']);

            // CAMBIO: Mensaje de confirmación
            $_SESSION['mensaje'] = "¡Compra SIMULADA N°{$pedido_id} realizada con éxito! Revisa tu Historial de Pedidos.";

        } catch (Exception $e) {
            $pdo->rollBack(); 
            $_SESSION['mensaje'] = "Error al procesar la simulación del pedido. Intente nuevamente.";
        }
    }
    
    header("Location: carrito.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="carpeta.css">
    <style>
        /* Estilos específicos del carrito */
        .cart-container {
            width: 90%;
            max-width: 1200px;
            margin: 50px auto;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .cart-items {
            flex: 2;
            min-width: 300px;
        }
        .cart-summary {
            flex: 1;
            min-width: 250px;
            max-width: 350px;
            background: #f4f4f4;
            padding: 20px;
            border: 2px solid #000;
            height: fit-content;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
        }
        .item-details {
            flex: 1;
        }
        .item-details h4 {
            margin: 0;
            color: #000;
        }
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .item-quantity input {
            width: 50px;
            text-align: center;
            border: 1px solid #000;
            padding: 5px;
        }
        .item-remove button {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
            font-size: 1.1rem;
            margin-left: 10px;
        }
        .checkout-button {
            width: 100%;
            padding: 15px;
            background: #e20000; 
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            margin-top: 20px;
            text-transform: uppercase;
            font-weight: bold;
            border-radius: 5px;
        }
        .message {
            width: 100%;
            padding: 15px;
            background: #e6ffe6;
            color: #008000;
            border: 1px solid #008000;
            margin-bottom: 20px;
            text-align: center;
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
           value="<?php echo htmlspecialchars($search_term ?? ''); ?>">
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
    <li><a href="carrito.php"><i class="fa fa-shopping-cart"></i> Carrito (<?php echo $total_items_carrito; ?>)</a></li>
    
    <li>
        <a href="cuenta.php" style="color:  <?php echo $user_logged_in ? '#e20000' : 'inherit'; ?>; font-weight: bold;">
            <i class="fa fa-user"></i> 
            <?php echo htmlspecialchars($user_name_display); ?>
        </a>
    </li>
  </ul>
</nav>

<div class="cart-container">
    
    <?php 
    if (isset($_SESSION['mensaje'])): 
    ?>
        <div class="message"><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="cart-items">
        <h2>Tu Carrito de Compras (<?php echo $total_items; ?> ítems)</h2>

        <?php if (empty($_SESSION['carrito'])): ?>
            <p>Tu carrito está vacío. <a href="segundamano.php">¡Empieza a comprar!</a></p>
        <?php else: ?>
            <?php foreach ($_SESSION['carrito'] as $item): ?>
                <div class="cart-item">
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                        <p>Precio Unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                        <p style="font-weight: bold;">Subtotal: $<?php echo number_format($item['precio'] * $item['quantity'], 0, ',', '.'); ?></p>
                    </div>
                    
                    <form method="POST" style="display: flex; align-items: center;" onchange="this.submit()">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <div class="item-quantity">
                            <label for="qty-<?php echo $item['id']; ?>">Cant:</label>
                            <input type="number" 
                                   id="qty-<?php echo $item['id']; ?>" 
                                   name="quantity" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="0" 
                                   max="99" 
                                   style="width: 50px;">
                        </div>
                    </form>

                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="quantity" value="0">
                        <div class="item-remove">
                            <button type="submit" title="Eliminar"><i class="fa fa-trash"></i></button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <div class="cart-summary">
        <h3>Resumen del Pedido</h3>
        <hr style="border-top: 2px solid #000;">
        <p style="margin-top: 15px;">Subtotal (<?php echo $total_items; ?> productos): 
            <span style="float: right;">$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
        </p>
        <p>Envío: 
            <span style="float: right; font-weight: bold; color: <?php echo $gastos_envio > 0 ? '#000' : 'green'; ?>;">
                <?php if ($gastos_envio > 0): ?>
                    $<?php echo number_format($gastos_envio, 0, ',', '.'); ?>
                <?php else: ?>
                    ¡GRATIS!
                <?php endif; ?>
            </span>
        </p>
        <hr style="border-top: 2px solid #000;">
        <h4 style="font-size: 1.5rem; margin-top: 15px;">Total: 
            <span style="float: right;">$<?php echo number_format($total_a_pagar, 0, ',', '.'); ?></span>
        </h4>
        
        <?php if ($total_items > 0): ?>
            <form method="POST">
                <input type="hidden" name="action" value="checkout">
                <button type="submit" class="checkout-button">
                    <i class="fa fa-check-circle"></i> FINALIZAR COMPRA
                </button>
            </form>
        <?php endif; ?>
    </div>

</div>

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

</body>
</html>