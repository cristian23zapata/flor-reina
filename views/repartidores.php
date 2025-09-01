<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o el usuario no es 'repartidor' ni 'admin'
if (!isset($_SESSION['tipo']) ){
    header("refresh:1;url=../views/login.php");
    exit();
}

if (isset($_SESSION['tipo']) ){
    if ($_SESSION['tipo'] === 'user') {
        header("Location: ../index.php");
        exit();
    } elseif ($_SESSION['tipo'] !== 'repartidor' && $_SESSION['tipo'] !== 'admin') {
        header("Location: ../views/login.php");
        exit();
    }
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
    if ($mensaje_error) {
        $mensaje_error .= "<br>";
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

$mysql->desconectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina - Panel de Repartidores</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
      /* SIDEBAR BASE */
      .sidebar {
          background-color: #ffe6f0;
          border-right: 1px solid #f8c8dc;
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
          background-color: #fddbe9;
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
          background-color: #ffe6f0;
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
              background-color: #ffe6f0;
              box-shadow: 0 0 10px rgba(0,0,0,0.1);
              transition: left 0.3s ease-in-out;
          }

          .sidebar.show {
              left: 0;
          }
      }
      
      /* Estilos para las tarjetas de pedidos */
      .card-header-custom {
          background-color: #f8f9fa;
          border-bottom: 1px solid #e9ecef;
      }
      .order-card {
          border-left: 5px solid #0d6efd;
          margin-bottom: 20px;
      }
      .order-card.pending {
          border-left: 5px solid #ffc107;
      }
      .order-card.delivered {
          border-left: 5px solid #198754;
      }
      .order-card.enviado {
          border-left: 5px solid #6c757d;
      }
      .order-card.cancelado {
          border-left: 5px solid #dc3545;
          opacity: 0.7;
      }
      
      /* Form container styles */
      .form-container {
          max-width: 100%;
          margin: auto;
          padding: 30px;
          background-color: white;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 220px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            <button class="toggle-btn d-none d-lg-inline" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/creacion.php"><i class="bi bi-plus-circle"></i><span> CREAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/registrar.php"><i class="bi bi-person-plus"></i><span> REGISTRAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark active" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> Estadísticas</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> Insumos</span></a></li>
            <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                <li class="nav-item"><a class="nav-link text-dark active" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> Mis Entregas</span></a></li>
            <?php } ?>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Panel de Repartidores</h1>
            <?php if (isset($_SESSION['correo'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <?php if ($_SESSION['tipo'] === 'user') { ?>
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

        <p class="lead">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>. Gestiona la asignación y el estado de los pedidos.</p>

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
                                        } elseif ($pedido['estado'] === 'cancelado') {
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
    </div>
</div>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= ($_GET['tipo'] === 'producto') ? 'Producto' : 'Artículo' ?> registrado con éxito',
                confirmButtonText: 'Aceptar'
            });
        <?php elseif ($_GET['estado'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= htmlspecialchars($_GET["mensaje"] ?? "Hubo un error") ?>',
                confirmButtonText: 'Intentar de nuevo'
            });
        <?php endif; ?>

        // Eliminar los parámetros de la URL sin recargar
        if (window.history.replaceState) {
            const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: url }, "", url);
        }
    </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar functionality
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