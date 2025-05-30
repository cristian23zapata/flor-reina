<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o el usuario no es 'repartidor' ni 'admin'
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'repartidor' && $_SESSION['tipo'] !== 'admin')) {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Inicializar $mensaje_error para evitar el warning 'Undefined variable'
$mensaje_error = '';

// Obtener el ID del repartidor logueado si el tipo de sesión es 'repartidor'
$id_repartidor_logueado = null;
if ($_SESSION['tipo'] === 'repartidor') {
    if (!isset($_SESSION['id_repartidor'])) {
        // Error crítico: el ID de repartidor no está en la sesión
        session_destroy();
        header('Location: ../views/login.php?error=sesion_repartidor_invalida');
        exit();
    }
    $id_repartidor_logueado = $_SESSION['id_repartidor'];
}

// Consultar pedidos pendientes (sin repartidor asignado) - Admin only
$pedidos_pendientes = [];
if ($_SESSION['tipo'] === 'admin') {
    // La consulta de pedidos pendientes busca pedidos SIN repartidor asignado
    // y en estados que indican que están listos para ser asignados.
    $query_pendientes = "SELECT p.id, p.fecha_pedido, p.estado, p.total_pedido, u.nombre AS nombre_usuario, u.direccion, u.telefono
                         FROM pedidos p
                         JOIN usuarios u ON p.id_usuario = u.id_Usuarios
                         WHERE p.id_repartidor IS NULL AND p.estado IN ('pendiente', 'confirmado', 'preparacion')
                         ORDER BY p.fecha_pedido ASC";
    $result_pendientes = $mysql->efectuarConsulta($query_pendientes);
    if ($result_pendientes) {
        while ($row = mysqli_fetch_assoc($result_pendientes)) {
            $pedidos_pendientes[] = $row;
        }
    } else {
        error_log("Error fetching pending orders: " . $mysql->getConexion()->error);
        $mensaje_error = 'Error interno al cargar pedidos pendientes.';
    }
}


// Consultar pedidos asignados
$pedidos_asignados = [];
if ($_SESSION['tipo'] === 'repartidor') {
    // Si es repartidor, solo ve sus propios pedidos asignados
    $query_asignados = "SELECT p.id, p.fecha_pedido, p.estado, p.total_pedido, u.nombre AS nombre_usuario, u.direccion, u.telefono
                        FROM pedidos p
                        JOIN usuarios u ON p.id_usuario = u.id_Usuarios
                        WHERE p.id_repartidor = ? AND p.estado IN ('asignado', 'en_camino', 'entregado', 'enviado')
                        ORDER BY p.fecha_pedido ASC";
    
    $stmt_asignados = $mysql->getConexion()->prepare($query_asignados);

    if ($stmt_asignados === false) {
        $mensaje_error = 'Error al preparar la consulta de pedidos asignados: ' . $mysql->getConexion()->error;
        error_log("Error al preparar la consulta de pedidos asignados (repartidor): " . $mysql->getConexion()->error);
    } else {
        if (!$stmt_asignados->bind_param("i", $id_repartidor_logueado)) {
            $mensaje_error = 'Error al enlazar parámetros para pedidos asignados: ' . $stmt_asignados->error;
            error_log("Error al enlazar parámetros para pedidos asignados (repartidor): " . $stmt_asignados->error);
            $stmt_asignados->close();
        } else {
            if (!$stmt_asignados->execute()) {
                $mensaje_error = 'Error al ejecutar la consulta de pedidos asignados: ' . $stmt_asignados->error;
                error_log("Error al ejecutar la consulta de pedidos asignados (repartidor): " . $stmt_asignados->error);
                $stmt_asignados->close();
            } else {
                $result_asignados = $stmt_asignados->get_result();
                if ($result_asignados) {
                    while ($row = $result_asignados->fetch_assoc()) {
                        $pedidos_asignados[] = $row;
                    }
                } else {
                    $mensaje_error = 'Error al obtener resultados de pedidos asignados: ' . $stmt_asignados->error;
                    error_log("Error al obtener resultados de pedidos asignados (repartidor): " . $stmt_asignados->error);
                }
                $stmt_asignados->close();
            }
        }
    }
} elseif ($_SESSION['tipo'] === 'admin') {
    // Si es admin, ve todos los pedidos asignados a cualquier repartidor
    // La imagen de la DB muestra 'enviado' y 'cancelado' como estados.
    // Incluyo 'cancelado' para que el admin pueda ver todos los estados de los pedidos asignados,
    // o puedes ajustarlo si solo quieres ver los 'activos'
    $query_asignados_admin = "SELECT p.id, p.fecha_pedido, p.estado, p.total_pedido, u.nombre AS nombre_usuario, u.direccion, u.telefono, r.nombre AS nombre_repartidor_asignado
                              FROM pedidos p
                              JOIN usuarios u ON p.id_usuario = u.id_Usuarios
                              JOIN repartidores r ON p.id_repartidor = r.id
                              WHERE p.id_repartidor IS NOT NULL AND p.estado IN ('asignado', 'en_camino', 'entregado', 'enviado', 'cancelado')
                              ORDER BY p.fecha_pedido ASC";
    $result_asignados_admin = $mysql->efectuarConsulta($query_asignados_admin);
    if ($result_asignados_admin) {
        while ($row = mysqli_fetch_assoc($result_asignados_admin)) {
            $pedidos_asignados[] = $row;
        }
    } else {
        error_log("Error fetching assigned orders (admin): " . $mysql->getConexion()->error);
        $mensaje_error = 'Error interno al cargar pedidos asignados para admin.';
    }
}

// Consultar todos los repartidores disponibles (para el admin o para la asignación)
$repartidores_disponibles = [];
// Assuming 'estado' column exists in 'repartidores' table
$query_repartidores = "SELECT id, nombre FROM repartidores WHERE estado = 'activo' ORDER BY nombre ASC"; 
$result_repartidores = $mysql->efectuarConsulta($query_repartidores);
if ($result_repartidores) {
    while ($row = mysqli_fetch_assoc($result_repartidores)) {
        $repartidores_disponibles[] = $row;
    }
} else {
    error_log("Error fetching available deliverers: " . $mysql->getConexion()->error);
    $mensaje_error = 'Error interno al cargar la lista de repartidores.';
}

// Mensajes de éxito o error (combinados con posibles errores internos)
$mensaje_exito = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'pedido_asignado') {
        $mensaje_exito = 'Pedido asignado con éxito.';
    } elseif ($_GET['success'] === 'pedido_desasignado') {
        $mensaje_exito = 'Pedido desasignado con éxito.';
    } elseif ($_GET['success'] === 'estado_actualizado') {
        $mensaje_exito = 'Estado del pedido actualizado con éxito.';
    }
} elseif (isset($_GET['error'])) {
    // Si ya hay un mensaje de error por depuración, lo combinamos
    if ($mensaje_error) {
        $mensaje_error .= "<br>"; // Salto de línea para separar
    }
    if ($_GET['error'] === 'db_error') {
        $mensaje_error .= 'Ocurrió un error en la base de datos. Inténtalo de nuevo.';
    } elseif ($_GET['error'] === 'no_data') {
        $mensaje_error .= 'Datos incompletos para la operación.';
    } elseif ($_GET['error'] === 'no_autorizado') {
        $mensaje_error .= 'No estás autorizado para realizar esta acción.';
    } elseif ($_GET['error'] === 'pedido_no_encontrado') {
        $mensaje_error .= 'Pedido no encontrado o ya procesado.';
    } elseif ($_GET['error'] === 'repartidor_no_valido') {
        $mensaje_error .= 'El repartidor seleccionado no es válido.';
    } elseif ($_GET['error'] === 'pedido_ya_asignado') {
        $mensaje_error .= 'Este pedido ya ha sido asignado.';
    } elseif ($_GET['error'] === 'estado_no_valido') {
        $mensaje_error .= 'El estado de actualización no es válido.';
    } elseif ($_GET['error'] === 'no_autorizado_pedido') {
        $mensaje_error .= 'No estás autorizado para modificar este pedido.';
    } elseif ($_GET['error'] === 'sesion_repartidor_invalida' || $_GET['error'] === 'sesion_corrupta_gestion') {
        $mensaje_error .= 'Sesión inválida. Por favor, inicia sesión de nuevo.';
    }
}

// Don't forget to close the connection when all database operations are done
$mysql->desconectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flor Reina - Panel de Repartidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
        .card-header-custom {
            background-color: #f8f9fa; /* Un color ligero para el header */
            border-bottom: 1px solid #e9ecef;
        }
        .order-card {
            border-left: 5px solid #0d6efd; /* Color azul para pedidos asignados */
            margin-bottom: 20px;
        }
        .order-card.pending {
            border-left: 5px solid #ffc107; /* Color amarillo para pendientes */
        }
        .order-card.delivered {
            border-left: 5px solid #198754; /* Color verde para entregados */
        }
        .order-card.enviado {
            border-left: 5px solid #6c757d; /* Gris para 'enviado' */
        }
        .order-card.cancelado { /* Nuevo estilo para 'cancelado' */
            border-left: 5px solid #dc3545; /* Rojo para 'cancelado' */
            opacity: 0.7; /* Ligeramente transparente para indicar que está inactivo */
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
                        <li class="nav-item"><a class="nav-link" href="../views/admin_pedidos.php">PEDIDOS</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/creacion.php">CREAR</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/registrar.php">REGISTRAR</a></li>
                        <li class="nav-item"><a class="nav-link active" href="../views/repartidores.php">REPARTIDORES</a></li> 
                        <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
                    <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                           <li class="nav-item"><a class="nav-link active" href="../views/repartidores.php">Mis Entregas</a></li> 
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
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
                        <button class="btn btn-outline-success position-relative" data-bs-toggle="modal" data-bs-target="#modalCarrito" id="btn-carrito">
                            <i class="bi bi-bag"></i> Carrito
                            <span id="carrito-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                0
                            </span>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="bg-light py-4 text-center">
        <div class="container">
            <h1 class="display-6">Panel de Repartidores</h1>
            <p class="lead">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>. Gestiona la asignación y el estado de los pedidos.</p>
        </div>
    </header>

    <main class="container py-5">
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if ($_SESSION['tipo'] === 'admin'): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Pedidos Pendientes de Asignación <span class="badge bg-warning text-dark"><?php echo count($pedidos_pendientes); ?></span></h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pedidos_pendientes)): ?>
                                <p class="text-muted text-center">No hay pedidos pendientes de asignación.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($pedidos_pendientes as $pedido): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center order-card pending p-3 rounded">
                                            <div>
                                                <p class="mb-1"><strong>Pedido #<?php echo htmlspecialchars($pedido['id']); ?></strong> (<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pedido['estado']))); ?>)</p>
                                                <p class="mb-1">Cliente: <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                                                <p class="mb-1">Dirección: <?php echo htmlspecialchars($pedido['direccion']); ?></p>
                                                <p class="mb-1">Teléfono: <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                                                <p class="fw-bold mb-0">Total: $<?php echo number_format($pedido['total_pedido'], 2); ?></p>
                                                <small class="text-muted">Fecha: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha_pedido']))); ?></small>
                                            </div>
                                            <form action="../controllers/gestionar_asignacion_repartidor.php" method="POST" class="d-flex align-items-center gap-2">
                                                <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                                                <select name="id_repartidor" class="form-select form-select-sm" required>
                                                    <option value="">Asignar a...</option>
                                                    <?php foreach ($repartidores_disponibles as $repartidor): ?>
                                                        <option value="<?php echo htmlspecialchars($repartidor['id']); ?>">
                                                            <?php echo htmlspecialchars($repartidor['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="asignar_pedido" class="btn btn-sm btn-primary">Asignar</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-md-<?php echo ($_SESSION['tipo'] === 'admin') ? '6' : '12'; ?>">
                <div class="card shadow-sm mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0">
                            <?php echo ($_SESSION['tipo'] === 'repartidor') ? 'Mis Pedidos Asignados' : 'Pedidos Asignados a Repartidores'; ?> 
                            <span class="badge bg-primary"><?php echo count($pedidos_asignados); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pedidos_asignados)): ?>
                            <p class="text-muted text-center">No hay pedidos asignados actualmente.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($pedidos_asignados as $pedido): ?>
                                    <?php
                                        $card_class = "order-card";
                                        if ($pedido['estado'] === 'entregado') {
                                            $card_class .= " delivered";
                                        } elseif ($pedido['estado'] === 'enviado') {
                                            $card_class .= " enviado";
                                        } elseif ($pedido['estado'] === 'cancelado') { // Estilo para 'cancelado'
                                            $card_class .= " cancelado";
                                        }
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center <?php echo $card_class; ?> p-3 rounded">
                                        <div>
                                            <p class="mb-1"><strong>Pedido #<?php echo htmlspecialchars($pedido['id']); ?></strong> (Estado: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pedido['estado']))); ?>)</p>
                                            <p class="mb-1">Cliente: <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                                            <p class="mb-1">Dirección: <?php echo htmlspecialchars($pedido['direccion']); ?></p>
                                            <p class="mb-1">Teléfono: <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                                            <?php if ($_SESSION['tipo'] === 'admin' && isset($pedido['nombre_repartidor_asignado'])): ?>
                                                <p class="mb-1">Asignado a: <strong><?php echo htmlspecialchars($pedido['nombre_repartidor_asignado']); ?></strong></p>
                                            <?php endif; ?>
                                            <p class="fw-bold mb-0">Total: $<?php echo number_format($pedido['total_pedido'], 2); ?></p>
                                            <small class="text-muted">Fecha: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha_pedido']))); ?></small>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <?php if ($_SESSION['tipo'] === 'repartidor' && $pedido['estado'] !== 'entregado' && $pedido['estado'] !== 'cancelado'): ?>
                                                <form action="../controllers/gestionar_asignacion_repartidor.php" method="POST" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                                                    <select name="nuevo_estado" class="form-select form-select-sm" required>
                                                        <option value="">Actualizar estado...</option>
                                                        <?php if ($pedido['estado'] === 'asignado' || $pedido['estado'] === 'enviado'): ?>
                                                            <option value="en_camino">En camino</option>
                                                        <?php endif; ?>
                                                        <?php if ($pedido['estado'] === 'en_camino' || $pedido['estado'] === 'asignado' || $pedido['estado'] === 'enviado'): ?>
                                                            <option value="entregado">Entregado</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <button type="submit" name="actualizar_estado" class="btn btn-sm btn-info">Actualizar</button>
                                                </form>
                                                <form action="../controllers/gestionar_asignacion_repartidor.php" method="POST">
                                                    <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['tipo'] === 'admin' && $pedido['estado'] !== 'entregado' && $pedido['estado'] !== 'cancelado'): ?>
                                                <form action="../controllers/gestionar_asignacion_repartidor.php" method="POST">
                                                    <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                                                    <button type="submit" name="desasignar_pedido_admin" class="btn btn-sm btn-danger mt-2">Desasignar</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalCarritoLabel">
                        <i class="bi bi-bag"></i> Mi Carrito
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="carrito-vacio" class="text-center">
                        <p>Tu carrito está vacío.</p>
                    </div>
                    <div id="carrito-contenido" style="display: none;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="carrito-items">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="carrito-total"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger" id="vaciar-carrito">Vaciar Carrito</button>
                    <button type="button" class="btn btn-success" id="btn-pagar-modal">Pagar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast-agregado" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Éxito</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body">
                Producto agregado al carrito!
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Este script es el mismo que en productos.php, admin_pedidos.php y user_pedidos.php
            // Idealmente, se movería a un archivo JS externo y se incluiría.
            const carritoContador = document.getElementById('carrito-contador');
            const carritoItems = document.getElementById('carrito-items');
            const carritoTotal = document.getElementById('carrito-total');
            const carritoVacio = document.getElementById('carrito-vacio');
            const carritoContenido = document.getElementById('carrito-contenido');

            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

            function actualizarContador() {
                let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
                carritoContador.textContent = totalItems;
                carritoContador.style.display = totalItems > 0 ? 'inline-block' : 'none';
            }

            function eliminarItem(index) {
                carrito.splice(index, 1);
                localStorage.setItem('carrito', JSON.stringify(carrito));
                renderizarCarrito();
                actualizarContador();
            }

            function vaciarCarrito() {
                carrito = [];
                localStorage.removeItem('carrito');
                renderizarCarrito();
                actualizarContador();
            }

            const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
            if (vaciarCarritoBtn) {
                vaciarCarritoBtn.addEventListener('click', vaciarCarrito);
            }

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('eliminar-item')) {
                    const index = e.target.dataset.index;
                    eliminarItem(index);
                }
            });

            function renderizarCarrito() {
                if (carrito.length === 0) {
                    carritoVacio.style.display = 'block';
                    carritoContenido.style.display = 'none';
                    document.getElementById('btn-pagar-modal').style.display = 'none';
                    document.getElementById('vaciar-carrito').style.display = 'none';
                } else {
                    carritoVacio.style.display = 'none';
                    carritoContenido.style.display = 'block';
                    document.getElementById('btn-pagar-modal').style.display = 'inline-block';
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
                                    <img src="../${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
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

            document.addEventListener('change', function(e) {
                if (e.target && e.target.matches('.input-group input[type="number"]')) {
                    const input = e.target;
                    const index = input.closest('.input-group').querySelector('button').dataset.index;
                    const nuevaCantidad = parseInt(input.value);
                    if (nuevaCantidad > 0 && nuevaCantidad <= carrito[index].stock) {
                        carrito[index].cantidad = nuevaCantidad;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    } else {
                        alert('La cantidad no puede ser mayor al stock disponible');
                        input.value = carrito[index].cantidad;
                    }
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('incrementar-cantidad')) {
                    const index = e.target.dataset.index;
                    const input = e.target.previousElementSibling;
                    const currentQuantity = parseInt(input.value);
                    if (currentQuantity < carrito[index].stock) {
                        input.value = currentQuantity + 1;
                        carrito[index].cantidad = currentQuantity + 1;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    }
                }
                if (e.target && e.target.classList.contains('decrementar-cantidad')) {
                    const index = e.target.dataset.index;
                    const input = e.target.nextElementSibling;
                    const currentQuantity = parseInt(input.value);
                    if (currentQuantity > 1) {
                        input.value = currentQuantity - 1;
                        carrito[index].cantidad = currentQuantity - 1;
                        localStorage.setItem('carrito', JSON.stringify(carrito));
                        renderizarCarrito();
                        actualizarContador();
                    }
                }
            });

            document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
                renderizarCarrito();
            });

            actualizarContador();

            document.getElementById('btn-pagar-modal').addEventListener('click', function() {
                const carritoData = localStorage.getItem('carrito');
                if (carritoData && JSON.parse(carritoData).length > 0) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../views/pagar.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'carrito';
                    input.value = carritoData;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('Tu carrito está vacío. Agrega productos antes de pagar.');
                }
            });
        });
    </script>
</body>
</html>