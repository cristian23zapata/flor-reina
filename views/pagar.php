<?php
session_start();
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

$carrito = [];
$mensaje_error = '';
$usuario_tiene_datos = false;
$usuario_data = null;
$nombre_usuario = $_SESSION['nombre'] ?? 'Invitado'; // Obtener el nombre del usuario para el nav

// 1. Verificar si el usuario está logueado y es de tipo 'user'
if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
    header('Location: ../views/login.php?redirect=pagar'); // Redirigir a login si no está logueado como user
    exit();
}

$correo_usuario = $_SESSION['correo'];

// 2. Obtener datos del usuario de la base de datos (id_Usuarios, dirección, teléfono)
// Se necesita id_Usuarios para guardar el pedido
$query_usuario_datos = "SELECT id_Usuarios, direccion, telefono FROM usuarios WHERE correo = ?";
$stmt_usuario_datos = $mysql->prepare($query_usuario_datos);
$stmt_usuario_datos->bind_param("s", $correo_usuario);
$stmt_usuario_datos->execute();
$resultado_usuario_datos = $stmt_usuario_datos->get_result();

if ($resultado_usuario_datos->num_rows > 0) {
    $usuario_data = $resultado_usuario_datos->fetch_assoc();
    // Verificar si la dirección y el teléfono no están vacíos o nulos
    if (!empty($usuario_data['direccion']) && !empty($usuario_data['telefono'])) {
        $usuario_tiene_datos = true;
    }
}
$stmt_usuario_datos->close();


// 3. Obtener datos del carrito
// Para asegurar que el carrito se mantenga si el usuario necesita actualizar sus datos,
// usaremos $_SESSION['carrito_para_pagar']. El "productos.php" debería enviar el carrito aquí por POST
// o guardar en sesión antes de redirigir.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carrito'])) {
    $carrito = json_decode($_POST['carrito'], true);
    // Guardar el carrito en sesión para persistencia en caso de que el usuario necesite actualizar datos
    $_SESSION['carrito_para_pagar'] = $carrito;
} elseif (isset($_SESSION['carrito_para_pagar'])) {
    $carrito = $_SESSION['carrito_para_pagar'];
}

// Si el carrito está vacío después de intentar recuperarlo
if (empty($carrito)) {
    $mensaje_error = "Tu carrito está vacío. Por favor, agrega algunos productos antes de proceder al pago.";
} else {
    // Calcular totales si el carrito no está vacío
    $subtotal = array_reduce($carrito, function($sum, $item) {
        return $sum + ($item['precio'] * $item['cantidad']);
    }, 0);

    $iva = 0.21; // 21% IVA
    $total = $subtotal + ($subtotal * $iva);

    // El número de pedido se generará solo cuando el pedido se confirme exitosamente en procesar_pago.php
    // Aquí no es necesario generarlo todavía.
}
$mysql->desconectar(); // Desconectar la base de datos después de usarla
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de Pago - Flor Reina</title>
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
        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-success:hover {
            background-color: #28a745;
            color: white;
        }

        /* Estilos para los contenedores principales (recibo-container y form-datos-envio) */
        .recibo-container, .form-datos-envio {
            max-width: 800px;
            margin: 30px auto;
            background: #ffeef2; /* Rosita suave para el fondo */
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #ffc4d3; /* Borde rosita */
        }
        .recibo-header {
            background: #ffc4d3; /* Rosita más fuerte para el encabezado del recibo */
            color: white;
            padding: 20px;
            text-align: center;
        }
        /* Para el logo en el header del recibo si ya está */
        .recibo-header .logo-recibo {
            filter: brightness(0) invert(1); /* Para hacer el logo blanco si es necesario */
        }

        .datos-cliente {
            background: #fdf5f7; /* Un rosita muy claro para la sección de datos del cliente */
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px dashed #ffc4d3; /* Borde punteado rosita */
        }
        .producto-imagen {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 5px;
        }
        /* Estilos para el botón de confirmar, similar al de contacto */
        .btn-confirmar-pedido {
            background-color: #d1567b; /* Un rosita oscuro similar a un botón de acción */
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            transition: background-color 0.3s;
            color: white; /* Texto blanco en el botón */
        }
        .btn-confirmar-pedido:hover {
            background-color: #ac4563; /* Hover más oscuro */
        }
        /* Estilo para el botón de "Guardar Datos y Continuar" */
        .form-datos-envio .btn-primary {
            background-color: #d1567b; /* Mismo rosita que el botón de confirmar */
            border-color: #d1567b;
        }
        .form-datos-envio .btn-primary:hover {
            background-color: #ac4563;
            border-color: #ac4563;
        }
        /* Para el h2 y h4 en la página */
        h2, h4 {
            color: #d1567b; /* Tono rosita oscuro para los títulos */
        }
        /* Para el SweetAlert2 */
        .swal2-popup .swal2-header {
            background-color: #f8c4d3; /* Fondo rosita para el encabezado de SweetAlert */
            color: white;
        }
        .swal2-popup .swal2-styled.swal2-confirm {
            background-color: #d1567b !important; /* Botón de confirmación rosita */
        }
        .swal2-icon.swal2-warning {
            border-color: #d1567b !important;
            color: #d1567b !important;
        }
        .swal2-icon.swal2-error {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
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
                    <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
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
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                        
                            <span id="carrito-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                0
                            </span>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-warning text-center py-4">
                <h3>¡Ups! <?php echo htmlspecialchars($mensaje_error); ?></h3>
                <a href="../views/productos.php" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left"></i> Volver a la tienda
                </a>
            </div>
        <?php elseif (!$usuario_tiene_datos): // Si el usuario no tiene dirección o teléfono ?>
            <div class="recibo-container p-4 form-datos-envio">
                <div class="text-center mb-4">
                    <i class="bi bi-person-exclamation" style="font-size: 3rem; color: #ffc107;"></i>
                    <h4 class="mt-3">Completa tus Datos de Envío</h4>
                    <p class="text-muted">Necesitamos tu dirección y teléfono para procesar tu pedido.</p>
                </div>
                <form action="../controllers/actualizar_datos_usuario.php" method="POST">
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección de Envío</label>
                        <input type="text" class="form-control" id="direccion" name="direccion"
                               value="<?php echo htmlspecialchars($usuario_data['direccion'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono"
                               value="<?php echo htmlspecialchars($usuario_data['telefono'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Guardar Datos y Continuar</button>
                </form>
            </div>
        <?php else: // Si el usuario tiene dirección y teléfono, mostrar resumen y botón de confirmar ?>
            <div class="recibo-container p-4">
                <div class="recibo-header">
                    <img src="../assets/imagenes/logo.png" alt="Flor Reina" class="logo-recibo mx-auto d-block mb-3" style="max-height: 80px;">
                    <h2 class="mb-0">Resumen de tu Pedido</h2>
                </div>

                <div class="recibo-body p-4">
                    <h5 class="mt-4"><i class="bi bi-person-check"></i> Datos de Envío</h5>
                    <div class="datos-cliente">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'N/A'); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['correo'] ?? 'N/A'); ?></p>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($usuario_data['direccion'] ?? 'No especificada'); ?></p>
                        <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario_data['telefono'] ?? 'No especificado'); ?></p>
                    </div>

                    <h5 class="mt-4"><i class="bi bi-cart-check"></i> Artículos del Pedido</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($carrito as $item): ?>
                                <tr class="producto-item">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($item['imagen']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['nombre']); ?>"
                                                 class="producto-imagen">
                                            <?php echo htmlspecialchars($item['nombre']); ?>
                                        </div>
                                    </td>
                                    <td class="text-end">$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td class="text-center"><?php echo $item['cantidad']; ?></td>
                                    <td class="text-end">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="totales bg-light rounded p-3 mt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>IVA (21%):</span>
                                    <span>$<?php echo number_format($subtotal * $iva, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Total a Pagar:</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recibo-footer bg-light p-3 text-center">
                    <p class="mb-2">¡Revisa los detalles de tu pedido!</p>
                    <p class="text-muted small mb-3">Una vez confirmes, tu pedido será procesado.</p>

                    <form action="../controllers/procesar_pago.php" method="POST">
                        <input type="hidden" name="total_a_pagar" value="<?php echo htmlspecialchars($total); ?>">
                        <input type="hidden" name="subtotal" value="<?php echo htmlspecialchars($subtotal); ?>">
                        <input type="hidden" name="iva_monto" value="<?php echo htmlspecialchars($subtotal * $iva); ?>">
                        <button type="submit" class="btn btn-confirmar-pedido w-75 py-2">
                            <i class="bi bi-check-circle"></i> Confirmar Pedido
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
            <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
        </div>
    </footer>

    <div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout modal-lg">
            <div class="modal-content h-100 rounded-start-4">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold " id="modalCarritoLabel">
                        <i class="bi bi-cart3"></i> Tu Carrito de Compras
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body overflow-auto p-3">
                    <div id="carrito-vacio" class="text-center py-5">
                        <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Tu carrito está vacío</p>
                    </div>
                    <div id="carrito-contenido" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr class="border-bottom">
                                        <th>Producto</th>
                                        <th style="width: 140px;">Cantidad</th>
                                        <th style="width: 100px;" class="text-end">Precio</th>
                                        <th style="width: 100px;" class="text-end">Subtotal</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="carrito-items">
                                    </tbody>
                                <tfoot class="border-top">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold" id="carrito-total">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left"></i> Seguir comprando
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-danger me-2" id="vaciar-carrito">
                            <i class="bi bi-trash"></i> Vaciar
                        </button>
                        <a href="../views/pagar.php" class="btn btn-success" id="btn-pagar">
                            <i class="bi bi-credit-card"></i> Pagar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="toast-agregado" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i> Producto agregado al carrito
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar carrito desde localStorage
            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

            // Actualizar contador del carrito
            function actualizarContador() {
                const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
                const contador = document.getElementById('carrito-contador');

                if (totalItems > 0) {
                    contador.textContent = totalItems;
                    contador.style.display = 'block';
                } else {
                    contador.style.display = 'none';
                }
            }

            // Renderizar carrito en el modal
            function renderizarCarrito() {
                const carritoItems = document.getElementById('carrito-items');
                const carritoVacio = document.getElementById('carrito-vacio');
                const carritoContenido = document.getElementById('carrito-contenido');
                const carritoTotal = document.getElementById('carrito-total');

                if (carrito.length === 0) {
                    carritoVacio.style.display = 'block';
                    carritoContenido.style.display = 'none';
                    document.getElementById('btn-pagar').style.display = 'none';
                    document.getElementById('vaciar-carrito').style.display = 'none';
                } else {
                    carritoVacio.style.display = 'none';
                    carritoContenido.style.display = 'block';
                    document.getElementById('btn-pagar').style.display = 'inline-block';
                    document.getElementById('vaciar-carrito').style.display = 'inline-block';

                    carritoItems.innerHTML = '';
                    let total = 0;

                    carrito.forEach((item, index) => {
                        const subtotal = item.precio * item.cantidad;
                        total += subtotal;

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
                                    <span class="text-truncate" style="max-width: 150px;">${item.nombre}</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group" style="min-width: 140px;">
                                    <button class="btn btn-outline-secondary decrementar-cantidad py-1" type="button" data-index="${index}">-</button>
                                    <input type="number" class="form-control text-center py-1" value="${item.cantidad}" min="1" max="${item.stock}" data-index="${index}">
                                    <button class="btn btn-outline-secondary incrementar-cantidad py-1" type="button" data-index="${index}">+</button>
                                </div>
                            </td>
                            <td class="text-end align-middle">$${item.precio.toFixed(2)}</td>
                            <td class="text-end align-middle">$${subtotal.toFixed(2)}</td>
                            <td class="text-center align-middle">
                                <button class="btn btn-sm btn-outline-danger p-1 eliminar-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        carritoItems.appendChild(tr);
                    });

                    carritoTotal.textContent = `$${total.toFixed(2)}`;
                }
            }

            // Manejar el formulario de agregar al carrito (no es directo en pagar.php, pero para el modal)
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('agregar-carrito-form')) {
                    e.preventDefault();

                    const form = e.target;
                    const id = form.querySelector('input[name="id"]').value;
                    const nombre = form.querySelector('input[name="nombre"]').value;
                    const precio = parseFloat(form.querySelector('input[name="precio"]').value);
                    const imagen = form.querySelector('input[name="imagen"]').value;
                    const stock = parseInt(form.querySelector('input[name="stock"]').value);
                    const cantidad = parseInt(form.querySelector('input[name="cantidad"]').value);

                    // Verificar si el producto ya está en el carrito
                    const itemExistente = carrito.find(item => item.id === id);

                    if (itemExistente) {
                        // Actualizar cantidad si no supera el stock
                        const nuevaCantidad = itemExistente.cantidad + cantidad;
                        if (nuevaCantidad <= stock) {
                            itemExistente.cantidad = nuevaCantidad;
                        } else {
                            alert('No hay suficiente stock disponible');
                            return;
                        }
                    } else {
                        // Agregar nuevo item al carrito
                        carrito.push({
                            id,
                            nombre,
                            precio,
                            imagen,
                            cantidad,
                            stock
                        });
                    }

                    // Guardar en localStorage y actualizar UI
                    localStorage.setItem('carrito', JSON.stringify(carrito));
                    actualizarContador();
                    renderizarCarrito();

                    // Mostrar notificación
                    const toast = new bootstrap.Toast(document.getElementById('toast-agregado'));
                    toast.show();
                }
            });

            // Incrementar/decrementar cantidad en el modal de producto (no directo en pagar.php, pero si en el modal)
            document.addEventListener('click', function(e) {
                // Eliminar item del carrito
                if (e.target && (e.target.classList.contains('eliminar-item') || e.target.closest('.eliminar-item'))) {
                    const button = e.target.classList.contains('eliminar-item') ? e.target : e.target.closest('.eliminar-item');
                    const index = button.dataset.index;
                    carrito.splice(index, 1);
                    localStorage.setItem('carrito', JSON.stringify(carrito));
                    actualizarContador();
                    renderizarCarrito();
                }

                // Vaciar carrito
                if (e.target && e.target.id === 'vaciar-carrito') {
                    if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
                        carrito = [];
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        actualizarContador();
                        renderizarCarrito();
                    }
                }

                // Incrementar cantidad en el carrito
                if (e.target && (e.target.classList.contains('incrementar-cantidad') || e.target.closest('.incrementar-cantidad'))) {
                    const button = e.target.classList.contains('incrementar-cantidad') ? e.target : e.target.closest('.incrementar-cantidad');
                    const index = button.dataset.index;
                    const input = button.closest('.input-group').querySelector('input');

                    if (carrito[index].cantidad < carrito[index].stock) {
                        carrito[index].cantidad++;
                        input.value = carrito[index].cantidad;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    }
                }

                // Decrementar cantidad en el carrito
                if (e.target && (e.target.classList.contains('decrementar-cantidad') || e.target.closest('.decrementar-cantidad'))) {
                    const button = e.target.classList.contains('decrementar-cantidad') ? e.target : e.target.closest('.decrementar-cantidad');
                    const index = button.dataset.index;
                    const input = button.closest('.input-group').querySelector('input');

                    if (carrito[index].cantidad > 1) {
                        carrito[index].cantidad--;
                        input.value = carrito[index].cantidad;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    }
                }
            });

            // Actualizar cantidad desde el input en el carrito
            document.addEventListener('change', function(e) {
                if (e.target && e.target.matches('.input-group input[type="number"]')) {
                    const input = e.target;
                    // Obtener el index correctamente buscando en el elemento padre el botón
                    const index = input.closest('.input-group').querySelector('button[data-index]').dataset.index;
                    const nuevaCantidad = parseInt(input.value);

                    if (!isNaN(nuevaCantidad) && nuevaCantidad > 0 && nuevaCantidad <= carrito[index].stock) {
                        carrito[index].cantidad = nuevaCantidad;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    } else {
                        alert('La cantidad no puede ser mayor al stock disponible o menor a 1');
                        input.value = carrito[index].cantidad; // Revertir al valor anterior
                    }
                }
            });

            // Renderizar carrito cuando se abre el modal
            document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
                renderizarCarrito();
            });

            // Inicializar contador al cargar la página
            actualizarContador();
        });
    </script>
</body>
</html>