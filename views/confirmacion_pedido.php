<?php
session_start();

$numero_pedido = $_GET['numero_pedido'] ?? 'N/A';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Limpiar el mensaje después de mostrarlo

// Redirigir si no hay mensaje de éxito o número de pedido
if (empty($success_message) && $numero_pedido === 'N/A') {
    header('Location: ../views/productos.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Flor Reina</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_blog.css">
    <link rel="stylesheet" href="../assets/css/estilo_productos.css">
    <link rel="stylesheet" href="../assets/css/new.css">
    <style>
        /* Estilos personalizados para el color "rosita" */
        body { background-color: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1 0 auto; }
        footer { flex-shrink: 0; }

        /* Navbar Custom (asumiendo que estil_nav.css o new.css lo define) */
        .navbar-custom {
            background-color: #f8c4d3; /* Rosita para el navbar */
            border-bottom: 1px solid #e0b0be; /* Borde inferior rosita */
        }
        .navbar-brand img { max-height: 60px; }
        .navbar-nav .nav-link { color: #5a5a5a; } /* Color de enlace por defecto */
        .navbar-nav .nav-link:hover { color: #d1567b; } /* Color de enlace al pasar el ratón */
        .navbar-nav .nav-link.active { color: #d1567b; font-weight: bold; } /* Color de enlace activo */
        .btn-outline-primary {
            color: #d1567b;
            border-color: #d1567b;
        }
        .btn-outline-primary:hover {
            background-color: #d1567b;
            color: white;
        }
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }
        .btn-outline-success { /* This button is no longer used in this file, but kept for consistency with global styles */
            color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-success:hover { /* This button is no longer used in this file, but kept for consistency with global styles */
            background-color: #28a745;
            color: white;
        }

        /* Estilos para el contenedor de confirmación */
        .confirmation-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 40px;
            background: #ffeef2; /* Rosita suave para el fondo */
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #ffc4d3; /* Borde rosita */
        }
        .confirmation-icon {
            font-size: 5rem;
            color: #d1567b; /* Rosita para el icono de éxito */
            margin-bottom: 20px;
        }
        h1 {
            color: #d1567b; /* Tono rosita oscuro para el título */
        }
        .btn-primary {
            background-color: #d1567b; /* Mismo rosita que el botón de confirmar en pagar.php */
            border-color: #d1567b;
            color: white;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #ac4563; /* Hover más oscuro */
            border-color: #ac4563;
        }
        .alert-success {
            background-color: #e6ffed; /* Un verde claro que combine con el rosita */
            border-color: #b3e6c6;
            color: #1a7e3d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                    <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
                    <li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                    <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
                    <?php } ?>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['correo'])): ?>
                        <div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                                <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php } ?>
        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
    </ul>
</div>
                    <?php else: ?>
                        <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
                    <?php endif; ?>
                    </div>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="confirmation-container">
            <i class="bi bi-check-circle-fill confirmation-icon"></i>
            <h1 class="mb-3">¡Pedido Confirmado!</h1>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <p class="lead">Tu pedido con número <strong><?php echo htmlspecialchars($numero_pedido); ?></strong> ha sido registrado exitosamente.</p>
            <p>Recibirás un correo electrónico con los detalles completos de tu compra en breve.</p>
            <a href="./productos_usuario.php" class="btn btn-primary mt-4">
                <i class="bi bi-shop"></i> Seguir Comprando
            </a>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
            <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Limpiar el carrito de localStorage al confirmar el pedido
            localStorage.removeItem('carrito');
            // Si también tenías un contador de carrito en el navbar, asegúrate de que se actualice a 0 o se oculte.
            const contador = document.getElementById('carrito-contador');
            if (contador) {
                contador.textContent = '0';
                contador.style.display = 'none';
            }
        });
    </script>
</body>
</html>