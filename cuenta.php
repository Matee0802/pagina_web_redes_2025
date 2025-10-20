<?php
// ========================================================
// Archivo: cuenta.php
// Objetivo: Panel de Usuario (Login, Registro, Logout y Historial de Pedidos)
// ========================================================
require_once 'conexion.php'; 

// 1. INICIAR SESIÓN
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicializar variables de error/éxito
$mensaje_registro = $_SESSION['registro_mensaje'] ?? '';
$mensaje_login = $_SESSION['login_error'] ?? '';

// Limpiar mensajes de sesión
unset($_SESSION['registro_mensaje']);
unset($_SESSION['login_error']);

// ----------------------------------------------------
// A. PROCESAR ACCIONES (Login, Registro, Logout)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'logout') {
        // Lógica de LOGOUT
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: cuenta.php");
        exit();

    } elseif ($action === 'login') {
        // Lógica de LOGIN
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verificar contraseña hasheada
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_name'] = $usuario['nombre'];
            header("Location: cuenta.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Email o contraseña incorrectos.";
            header("Location: cuenta.php");
            exit();
        }
    } elseif ($action === 'register') {
        // Lógica de REGISTRO
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($nombre) || empty($email) || empty($password) || $password !== $password_confirm) {
            $_SESSION['registro_mensaje'] = "Error: Faltan datos o las contraseñas no coinciden.";
        } else {
            $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt_check->execute([$email]);
            if ($stmt_check->fetch()) {
                $_SESSION['registro_mensaje'] = "Error: El email ya está registrado.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                
                if ($stmt_insert->execute([$nombre, $email, $password_hash])) {
                    $_SESSION['registro_mensaje'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
                } else {
                    $_SESSION['registro_mensaje'] = "Error al guardar el usuario en la base de datos.";
                }
            }
        }
        header("Location: cuenta.php?mode=register");
        exit();
    }
}
// ----------------------------------------------------

// 2. DEFINICIÓN DE VARIABLES PARA LA VISTA
$user_logged_in = isset($_SESSION['usuario_id']); 
$user_name = $_SESSION['usuario_name'] ?? 'Invitado';

// Variable que se muestra en el NAV (Resuelve la sesión permanente)
$user_name_display = $user_logged_in ? $user_name : 'Cuenta'; 

// Contador del carrito para el header (Necesario para que el carrito se vea en el NAV)
$total_items_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_items_carrito += $item['quantity'] ?? 0;
    }
}
$search_term = $_GET['search'] ?? ''; 
$mode = $_GET['mode'] ?? 'login'; 

// ----------------------------------------------------
// 3. OBTENER HISTORIAL DE PEDIDOS (SOLO SI LOGUEADO)
// ----------------------------------------------------
$pedidos = [];
$error_pedidos = null; // Variable para depuración
if ($user_logged_in) {
    try {
        // CORRECCIÓN CLAVE: Se usa 'total' en lugar de 'total_decimal'
        $stmt_pedidos = $pdo->prepare("
            SELECT id, total, estado, fecha_pedido, metodo_pago 
            FROM pedidos 
            WHERE usuario_id = ?
            ORDER BY fecha_pedido DESC
        ");
        $stmt_pedidos->execute([$_SESSION['usuario_id']]);
        $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Mejoramos el manejo de errores para mostrarlo en pantalla
        $error_pedidos = "ERROR DE BASE DE DATOS en Historial: Verifica los nombres de las columnas (total, estado, fecha_pedido, etc.). Mensaje: " . htmlspecialchars($e->getMessage());
        error_log("Error al cargar pedidos: " . $e->getMessage()); 
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Morbido Ropas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="carpeta.css">
    <style>
        /* Estilos base para la página (ESTÉTICA AJUSTADA) */
        .account-page {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            border: 2px solid #000;
            background: #fff;
            text-align: center;
            box-shadow: 5px 5px 0 #ccc; 
        }
        
        .account-page h2 {
            font-size: 2rem;
            margin-bottom: 25px;
            color: #000;
            text-transform: uppercase;
        }

        /* Contenedor del formulario (Login/Registro) */
        .auth-form {
            max-width: 400px;
            margin: 20px auto 0;
            padding: 25px;
            border: 1px solid #ccc; 
            background: #fcfcfc;
        }
        
        .auth-form h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #e20000;
        }

        /* Campos de entrada */
        .auth-form input[type="text"], 
        .auth-form input[type="password"],
        .auth-form input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #000;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .auth-form input:focus {
            border-color: #e20000;
            outline: none;
        }

        /* Botones de acción principal (Login/Registro) */
        .auth-form button {
            width: 100%;
            padding: 12px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            text-transform: uppercase;
            font-weight: bold;
            transition: background 0.3s;
        }
        .auth-form button:hover {
            background: #e20000;
        }

        /* Botón de Cerrar Sesión (rojo de advertencia) */
        .logout-form button {
            background: red;
            margin-top: 30px;
        }
        .logout-form button:hover {
            background: #000;
        }
        
        /* Enlaces de texto (Registro/Login y avisos) */
        .account-page p a {
            color: #e20000;
            font-weight: bold;
            text-decoration: none;
        }
        .account-page p a:hover {
            text-decoration: underline;
        }

        /* --- PESTAÑAS (Login/Registro) --- */
        .mode-links {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .mode-links a {
            padding: 8px 20px;
            text-decoration: none;
            color: #000;
            border: 2px solid #000;
            font-weight: bold;
            transition: all 0.2s;
        }
        /* Estilo de la pestaña ACTIVA */
        .mode-links a.active {
            background: #e20000;
            color: #fff;
            border-color: #e20000;
        }
        .mode-links a:not(.active):hover {
            background: #f0f0f0;
        }
        
        /* --- MENSAJES DE ERROR/ÉXITO --- */
        .message {
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            font-weight: bold;
            text-align: left;
        }
        .error {
            color: #a00000;
            border-color: #fdd;
            background-color: #ffeaea;
        }
        .message:not(.error) { 
            color: #006400;
            border-color: #ddf;
            background-color: #eaffea;
        }

        /* --- ESTILOS DE HISTORIAL DE PEDIDOS --- */
        .order-history {
            text-align: left;
            margin-top: 20px;
        }
        .order-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.05);
        }
        .order-card p {
            margin: 5px 0;
            font-size: 0.95rem;
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
           value="<?= htmlspecialchars($search_term) ?>">
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
            <i class="fa fa-user"></i> <?= htmlspecialchars($user_name_display) ?>
        </a>
    </li>
  </ul>
</nav>

<div class="account-page">
    <?php if ($user_logged_in): ?>
        <h2>¡Bienvenido, <?= htmlspecialchars($user_name) ?>!</h2>
        
        <div class="mode-links">
            <a href="cuenta.php" class="active">Pedidos</a>
            </div>
        
        <h3 style="margin-top: 40px; border-bottom: 2px solid #000; padding-bottom: 10px; text-transform: uppercase; font-size: 1.2rem;">Tu Historial de Compras</h3>

        <?php if ($error_pedidos): ?>
            <div class="message error" style="text-align: center; font-size: 1rem; padding: 15px; margin-bottom: 20px;">
                <strong>⚠️ Error:</strong> <?= $error_pedidos ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <p style="margin-top: 20px;">Aún no tienes pedidos realizados.</p>
        <?php else: ?>
            <div class="order-history">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="order-card">
                        <p><strong>Pedido N°:</strong> <?= htmlspecialchars($pedido['id']) ?></p>
                        <p><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($pedido['fecha_pedido'])) ?></p>
                        <p><strong>Total:</strong> $<?= number_format($pedido['total'], 0, ',', '.') ?></p>
                        <p><strong>Método:</strong> <?= htmlspecialchars($pedido['metodo_pago']) ?></p>
                        <p>
                            <strong>Estado:</strong> 
                            <span style="color: 
                                <?php 
                                    if ($pedido['estado'] == 'entregado') echo 'green';
                                    elseif ($pedido['estado'] == 'pendiente') echo 'orange';
                                    else echo 'red'; 
                                ?>; 
                                font-weight: bold;">
                                <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="logout-form">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="auth-form logout-button">Cerrar Sesión</button>
        </form>
        
    <?php else: ?>
        <h2>Tu Cuenta</h2>
        
        <div class="mode-links">
            <a href="cuenta.php?mode=login" class="<?= $mode == 'login' ? 'active' : '' ?>">Iniciar Sesión</a>
            <a href="cuenta.php?mode=register" class="<?= $mode == 'register' ? 'active' : '' ?>">Registrarse</a>
        </div>
        
        <?php if ($mode == 'login'): ?>
            <div class="auth-form">
                <h3>Iniciar Sesión</h3>
                <?php if ($mensaje_login): ?>
                    <div class="message error"><?= $mensaje_login ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Entrar</button>
                </form>
                <p style="margin-top: 15px;">¿No tienes cuenta? <a href="cuenta.php?mode=register">Regístrate aquí</a></p>
            </div>
        <?php elseif ($mode == 'register'): ?>
            <div class="auth-form">
                <h3>Crear Cuenta</h3>
                <?php if ($mensaje_registro): ?>
                    <div class="message <?= strpos($mensaje_registro, 'Error') !== false ? 'error' : '' ?>"><?= $mensaje_registro ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <input type="text" name="nombre" placeholder="Nombre completo" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <input type="password" name="password_confirm" placeholder="Confirmar Contraseña" required>
                    <button type="submit">Registrarme</button>
                </form>
                <p style="margin-top: 15px;">¿Ya tienes cuenta? <a href="cuenta.php?mode=login">Inicia Sesión</a></p>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
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