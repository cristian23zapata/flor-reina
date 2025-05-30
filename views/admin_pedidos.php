<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no es admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../views/login.php'); // O a una página de error/acceso denegado
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Obtener el estado del filtro de la URL, si existe
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Consulta base para obtener pedidos
$consulta_pedidos = "SELECT p.*, u.nombre AS nombre_usuario, u.correo AS correo_usuario, r.nombre AS nombre_repartidor
                     FROM pedidos p
                     JOIN usuarios u ON p.id_usuario = u.id_Usuarios
                     LEFT JOIN repartidores r ON p.id_repartidor = r.id";

// Añadir condición de filtro por estado
if ($filtro_estado !== 'todos' && in_array($filtro_estado, ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'])) {
    $consulta_pedidos .= " WHERE p.estado = '" . $mysql->escape_string($filtro_estado) . "'";
}

$consulta_pedidos .= " ORDER BY p.fecha_pedido DESC";

$resultado_pedidos = $mysql->efectuarConsulta($consulta_pedidos);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina - Gestión de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
        .estado-pendiente { background-color: #fff3cd; color: #664d03; } /* light yellow */
        .estado-confirmado { background-color: #d1e7dd; color: #0f5132; } /* light green */
        .estado-enviado { background-color: #cff4fc; color: #055160; } /* light blue */
        .estado-entregado { background-color: #d4edda; color: #155724; } /* medium green */
        .estado-cancelado { background-color: #f8d7da; color: #842029; } /* light red */
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
                        <li class="nav-item"><a class="nav-link active" href="../views/admin_pedidos.php">PEDIDOS</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/creacion.php">CREAR</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/registrar.php">REGISTRAR</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">REPARTIDORES</a></li>
                        
                        <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
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
        <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
        <li><hr class="dropdown-divider"></li>
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
            <h1 class="display-6">Gestión de Pedidos</h1>
            <p class="lead">Administra y sigue el estado de todos los pedidos.</p>
        </div>
    </header>

    <main class="container py-5">
        <div class="mb-4">
            <h4>Filtrar por Estado:</h4>
            <div class="btn-group" role="group" aria-label="Filtro de estados">
                <a href="admin_pedidos.php?estado=todos" class="btn btn-outline-primary <?= ($filtro_estado === 'todos') ? 'active' : '' ?>">Todos</a>
                <a href="admin_pedidos.php?estado=pendiente" class="btn btn-outline-warning <?= ($filtro_estado === 'pendiente') ? 'active' : '' ?>">Pendientes</a>
                <a href="admin_pedidos.php?estado=confirmado" class="btn btn-outline-success <?= ($filtro_estado === 'confirmado') ? 'active' : '' ?>">Confirmados</a>
                <a href="admin_pedidos.php?estado=enviado" class="btn btn-outline-info <?= ($filtro_estado === 'enviado') ? 'active' : '' ?>">Enviados</a>
                <a href="admin_pedidos.php?estado=entregado" class="btn btn-outline-dark <?= ($filtro_estado === 'entregado') ? 'active' : '' ?>">Entregados</a>
                <a href="admin_pedidos.php?estado=cancelado" class="btn btn-outline-danger <?= ($filtro_estado === 'cancelado') ? 'active' : '' ?>">Cancelados</a>
            </div>
        </div>

        <div class="row">
            <?php if (mysqli_num_rows($resultado_pedidos) > 0): ?>
                <?php while ($pedido = mysqli_fetch_assoc($resultado_pedidos)): ?>
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm rounded-3 border-0 <?= 'estado-' . $pedido['estado'] ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #<?php echo htmlspecialchars($pedido['numero_pedido']); ?></h5>
                                <span class="badge bg-primary fs-6"><?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?> (<?php echo htmlspecialchars($pedido['correo_usuario']); ?>)</p>
                                        <p><strong>Total:</strong> $<?php echo number_format($pedido['total_pedido'], 2); ?></p>
                                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono_contacto']); ?></p>
                                        <?php if ($pedido['id_repartidor'] && $pedido['nombre_repartidor']): ?>
                                            <p><strong>Repartidor Asignado:</strong> <?php echo htmlspecialchars($pedido['nombre_repartidor']); ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">No hay repartidor asignado.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Productos:</h6>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            // Obtener detalles de los productos para este pedido
                                            $consulta_detalle = "SELECT * FROM detallepedidos WHERE id_pedido = " . $pedido['id'];
                                            $resultado_detalle = $mysql->efectuarConsulta($consulta_detalle);
                                            while ($detalle = mysqli_fetch_assoc($resultado_detalle)):
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0">
                                                    <?php echo htmlspecialchars($detalle['nombre_producto']); ?> (x<?php echo $detalle['cantidad']; ?>)
                                                    <span>$<?php echo number_format($detalle['subtotal_linea'], 2); ?></span>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <?php if ($pedido['estado'] === 'pendiente'): ?>
                                        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#confirmarPedidoModal<?php echo $pedido['id']; ?>">
                                            Confirmar Pedido
                                        </button>
                                    <?php elseif ($pedido['estado'] === 'confirmado' && !$pedido['id_repartidor']): ?>
                                        <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#asignarRepartidorModal<?php echo $pedido['id']; ?>">
                                            Asignar Repartidor
                                        </button>
                                    <?php elseif ($pedido['estado'] === 'enviado'): ?>
                                        <button type="button" class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#marcarEntregadoModal<?php echo $pedido['id']; ?>">
                                            Marcar como Entregado
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($pedido['estado'] !== 'cancelado' && $pedido['estado'] !== 'entregado'): ?>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarPedidoModal<?php echo $pedido['id']; ?>">
                                            Cancelar Pedido
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="confirmarPedidoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="confirmarPedidoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="confirmarPedidoModalLabel<?php echo $pedido['id']; ?>">Confirmar Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres **confirmar** este pedido? Esto lo hará disponible para los repartidores.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <form action="../controllers/actualizar_estado_pedido.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="confirmado">
                                        <button type="submit" class="btn btn-success">Confirmar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($pedido['estado'] === 'confirmado' && !$pedido['id_repartidor']): ?>
                    <div class="modal fade" id="asignarRepartidorModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="asignarRepartidorModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="asignarRepartidorModalLabel<?php echo $pedido['id']; ?>">Asignar Repartidor a Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="../controllers/asignar_repartidor_pedido.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                        <div class="mb-3">
                                            <label for="repartidor_id_<?php echo $pedido['id']; ?>" class="form-label">Seleccionar Repartidor:</label>
                                            <select class="form-select" id="repartidor_id_<?php echo $pedido['id']; ?>" name="id_repartidor" required>
                                                <option value="">-- Seleccione un repartidor --</option>
                                                <?php
                                                // Obtener lista de repartidores para el modal
                                                $resultado_repartidores = $mysql->efectuarConsulta("SELECT id, nombre FROM repartidores ORDER BY nombre ASC");
                                                while ($repartidor = mysqli_fetch_assoc($resultado_repartidores)):
                                                    ?>
                                                    <option value="<?php echo $repartidor['id']; ?>"><?php echo htmlspecialchars($repartidor['nombre']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="modal-footer px-0">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-info">Asignar y Enviar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($pedido['estado'] === 'enviado'): ?>
                    <div class="modal fade" id="marcarEntregadoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="marcarEntregadoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title" id="marcarEntregadoModalLabel<?php echo $pedido['id']; ?>">Marcar Pedido #<?php echo $pedido['numero_pedido']; ?> como Entregado</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres marcar este pedido como **entregado**?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <form action="../controllers/actualizar_estado_pedido.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="entregado">
                                        <button type="submit" class="btn btn-dark">Marcar Entregado</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="modal fade" id="cancelarPedidoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="cancelarPedidoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="cancelarPedidoModalLabel<?php echo $pedido['id']; ?>">Cancelar Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres **cancelar** este pedido? Esta acción es irreversible.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <form action="../controllers/actualizar_estado_pedido.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="cancelado">
                                        <button type="submit" class="btn btn-danger">Cancelar Pedido</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        No hay pedidos en el estado "<?php echo htmlspecialchars($filtro_estado); ?>" por el momento.
                    </div>
                </div>
            <?php endif; ?>
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
            // Obtener elementos del DOM
            const carritoContador = document.getElementById('carrito-contador');
            const carritoItems = document.getElementById('carrito-items');
            const carritoTotal = document.getElementById('carrito-total');
            const carritoVacio = document.getElementById('carrito-vacio');
            const carritoContenido = document.getElementById('carrito-contenido');

            // Cargar carrito desde localStorage
            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

            // Función para actualizar el contador del carrito
            function actualizarContador() {
                let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
                carritoContador.textContent = totalItems;
                carritoContador.style.display = totalItems > 0 ? 'inline-block' : 'none';
            }

            // Función para eliminar un item del carrito
            function eliminarItem(index) {
                carrito.splice(index, 1);
                localStorage.setItem('carrito', JSON.stringify(carrito));
                renderizarCarrito();
                actualizarContador();
            }

            // Función para vaciar el carrito
            function vaciarCarrito() {
                carrito = [];
                localStorage.removeItem('carrito');
                renderizarCarrito();
                actualizarContador();
            }

            // Asignar evento al botón de vaciar carrito
            const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
            if (vaciarCarritoBtn) {
                vaciarCarritoBtn.addEventListener('click', vaciarCarrito);
            }

            // Asignar eventos a los botones de eliminar item
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('eliminar-item')) {
                    const index = e.target.dataset.index;
                    eliminarItem(index);
                }
            });

            // Función para renderizar los items del carrito en el modal
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

            // Actualizar cantidad al cambiar el valor del input en el modal del carrito
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

            // Renderizar carrito cuando se abre el modal
            document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
                renderizarCarrito();
            });

            // Inicializar contador al cargar la página
            actualizarContador();

            // Manejar el botón "Pagar" en el modal del carrito (si se usa en admin_pedidos)
            document.getElementById('btn-pagar-modal').addEventListener('click', function() {
                const carritoData = localStorage.getItem('carrito');
                if (carritoData && JSON.parse(carritoData).length > 0) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../views/pagar.php'; // Asegúrate que esta ruta sea correcta
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