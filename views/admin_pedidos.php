<?php
session_start();

require_once '../models/MySQL.php';


// Redirigir si no es admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../views/login.php');
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
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
        /* SIDEBAR BASE */
            .sidebar {
                background-color: #ffe6f0; /* Fondo rosa claro */
                border-right: 1px solid #f8c8dc; /* Borde más suave rosado */
                min-width: 220px;
                transition: all 0.3s ease;
                padding: 1rem 0.5rem;
            }

            /* LOGO */
            .sidebar .navbar-brand {
                display: flex;
                align-items: center;
                justify-content: center;
                padding-bottom: 1rem;
                font-weight: bold;
                color: #d63384;
            }

            /* NAV LINKS */
            .sidebar .nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: #444;
                font-weight: 500;
                border-radius: 0.375rem;
                transition: background 0.2s ease;
            }

            .sidebar .nav-link i {
                margin-right: 0.75rem;
                color: #d63384;
            }

            .sidebar .nav-link span {
                white-space: nowrap;
            }

            .sidebar .nav-link:hover,
            .sidebar .nav-link:focus {
                background-color: #fddbe9; /* Hover rosado suave */
                color: #d63384;
            }

            /* TOGGLE BUTTON */
            .toggle-btn {
                border: none;
                background: none;
                font-size: 1.25rem;
                color: #d63384;
            }

            /* COLLAPSED SIDEBAR */
            .sidebar.collapsed {
                min-width: 60px !important;
                overflow: hidden;
                background-color: #ffe6f0; /* Mantener fondo cuando colapsa */
            }

            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .navbar-brand span {
                display: none;
            }

            .sidebar.collapsed .nav-link {
                text-align: center;
            }

            .sidebar.collapsed .navbar-brand {
                padding: 0.5rem 0;
            }

            .sidebar.collapsed .bi {
                margin-right: 0;
                font-size: 1.25rem;
            }

            /* MOBILE SIDEBAR */
            @media (max-width: 991.98px) {
                .sidebar {
                    position: fixed;
                    top: 0;
                    left: -250px;
                    height: 100vh;
                    width: 220px;
                    z-index: 1050;
                    background-color: #ffe6f0; /* Fondo rosa también en móvil */
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    transition: left 0.3s ease-in-out;
                }

                .sidebar.show {
                    left: 0;
                }
            }

            /* ESTILOS PARA PEDIDOS */
            .estado-pendiente { background-color: #fff3cd; color: #664d03; } /* light yellow */
            .estado-confirmado { background-color: #d1e7dd; color: #0f5132; } /* light green */
            .estado-enviado { background-color: #cff4fc; color: #055160; } /* light blue */
            .estado-entregado { background-color: #d4edda; color: #155724; } /* medium green */
            .estado-cancelado { background-color: #f8d7da; color: #842029; } /* light red */
            
            .main-content {
                padding: 20px;
                width: 100%;
            }
            
            .filter-buttons .btn {
                margin-right: 5px;
                margin-bottom: 5px;
            }
            
            .card-pedido {
                margin-bottom: 20px;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .card-header-pedido {
                font-weight: bold;
                padding: 15px;
            }
            
            .producto-item {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                /*
                 * Los ítems dentro de la lista de productos se heredan del mismo
                 * color de fondo que la tarjeta del pedido. Bootstrap aplica un
                 * fondo blanco a las clases `list-group-item` por defecto,
                 * generando que cada producto aparezca con un recuadro blanco
                 * sobre la tarjeta coloreada según su estado (pendiente,
                 * confirmado, etc.). Para evitar ese contraste se fuerza la
                 * herencia del color y se elimina el fondo.
                 */
                background-color: inherit;
                color: inherit;
                border-bottom: 1px solid #eee;
            }

            /*
             * También aplicamos la misma herencia a todos los elementos
             * `.list-group-item` dentro de una tarjeta de pedido por si en el
             * futuro se añaden otros elementos de lista. Esto asegura que
             * cualquier ítem dentro de `.card-pedido` adopte el color de
             * fondo y texto de la tarjeta principal.
             */
            .card-pedido .list-group-item {
                background-color: inherit;
                color: inherit;
            }

            /*
             * Al navegar por los enlaces de la barra lateral (sidebar) se
             * aprecia un contorno/pseudoborde negro que sobresale del
             * recuadro rosa cuando el enlace obtiene el foco por teclado o
             * mouse. Bootstrap añade este resalte mediante la propiedad
             * `outline` y la sombra de foco (`box-shadow`). A continuación se
             * eliminan estos estilos para que el estado activo mantenga el
             * aspecto uniforme dentro del contenedor rosa.
             */
            .sidebar .nav-link:focus,
            .sidebar .nav-link:active {
                outline: none;
                box-shadow: none;
            }

            /* Algunos navegadores aplican el estilo `:focus-visible` cuando se
             * navega con el teclado. Se deshabilita aquí para mantener la
             * consistencia visual en los enlaces de la sidebar.
             */
            .sidebar .nav-link:focus-visible {
                outline: none;
                box-shadow: none;
            }
    </style>
</head>
<body>

<!-- Botón para abrir sidebar en móvil -->
<button class="btn btn-outline-secondary d-lg-none m-3" id="mobileSidebarToggle">
    <i class="bi bi-list"></i>
</button>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 300px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/creacion.php"><i class="bi bi-plus-circle"></i><span> CREAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> PRODUCTOS</span></a></li>
                
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Gestión de Pedidos</h1>
            <?php if (isset($_SESSION['correo'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <h4>Filtrar por Estado:</h4>
            <div class="btn-group filter-buttons" role="group" aria-label="Filtro de estados">
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
                        <div class="card shadow-sm card-pedido <?= 'estado-' . $pedido['estado'] ?>">
                            <div class="card-header d-flex justify-content-between align-items-center card-header-pedido">
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
                                                <li class="list-group-item producto-item">
                                                    <span><?php echo htmlspecialchars($detalle['nombre_producto']); ?> (x<?php echo $detalle['cantidad']; ?>)</span>
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

                    <!-- Modal Confirmar Pedido -->
                    <div class="modal fade" id="confirmarPedidoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="confirmarPedidoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="confirmarPedidoModalLabel<?php echo $pedido['id']; ?>">Confirmar Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres <strong>confirmar</strong> este pedido? Esto lo hará disponible para los repartidores.
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
                    <!-- Modal Asignar Repartidor -->
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
                    <!-- Modal Marcar como Entregado -->
                    <div class="modal fade" id="marcarEntregadoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="marcarEntregadoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title" id="marcarEntregadoModalLabel<?php echo $pedido['id']; ?>">Marcar Pedido #<?php echo $pedido['numero_pedido']; ?> como Entregado</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres marcar este pedido como <strong>entregado</strong>?
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

                    <!-- Modal Cancelar Pedido -->
                    <div class="modal fade" id="cancelarPedidoModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="cancelarPedidoModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="cancelarPedidoModalLabel<?php echo $pedido['id']; ?>">Cancelar Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que quieres <strong>cancelar</strong> este pedido? Esta acción es irreversible.
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');

    // Desktop toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('bi-chevron-left');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-left');
            }
        });
    }

    // Mobile toggle
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Close on outside click
        document.addEventListener('click', function (event) {
            const isClickInside = sidebar.contains(event.target) || mobileSidebarToggle.contains(event.target);
            if (!isClickInside && window.innerWidth < 992) {
                sidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
<?php
$mysql->desconectar();
?>