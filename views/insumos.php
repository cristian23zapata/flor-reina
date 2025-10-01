<?php
// Insumos / Inventario – Panel de Administración con tarjetas y modales
session_start();
// Solo permitir el acceso a administradores
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../models/MySQL.php';
$mysql = new MySQL();
$mysql->conectar();

// Mostrar mensajes de operación
$alert = '';
if (isset($_GET['estado'])) {
    if ($_GET['estado'] === 'exito') {
        $alert = '<div class="alert alert-success">¡Operación realizada con éxito!</div>';
    } elseif ($_GET['estado'] === 'error') {
        $msg = htmlspecialchars($_GET['mensaje'] ?? 'Hubo un error');
        $alert = "<div class=\"alert alert-danger\">$msg</div>";
    }
}

// Obtener todos los productos (insumos) ordenados por nombre
$query = "SELECT id, nombre, descripcion, ingredientes, precio, stock, imagen, estado
            FROM productos
           ORDER BY nombre";
$res = $mysql->efectuarConsulta($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Inventario - Flor Reina</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* Estilos de la barra lateral */
    .sidebar {
        background-color: #ffe6f0;
        border-right: 1px solid #f8c8dc;
        min-width: 220px;
        transition: all 0.3s ease;
        padding: 1rem 0.5rem;
    }
    .sidebar .navbar-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-bottom: 1rem;
        font-weight: bold;
        color: #d63384;
    }
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
    .toggle-btn {
        border: none;
        background: none;
        font-size: 1.25rem;
        color: #d63384;
    }
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
    .sidebar.collapsed .bi {
        margin-right: 0;
        font-size: 1.25rem;
    }
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
    /* Color para el precio */
    .precio-color {
        color: #d63384;
    }
    /* Tarjeta deshabilitada */
    .disabled-card {
        opacity: 0.6;
        filter: grayscale(50%);
    }
    /* Tarjeta para agregar */
    .add-card {
        border: 2px dashed #d63384;
        background-color: #ffe6f0;
        color: #d63384;
        transition: background 0.2s ease;
    }
    .add-card:hover {
        background-color: #fddbe9;
        text-decoration: none;
    }
    /* Encabezados de las ventanas modales en tonos rosados */
    .modal-header {
        background-color: #ffe6f0;
        color: #d63384;
        border-top-left-radius: calc(0.3rem - 1px);
        border-top-right-radius: calc(0.3rem - 1px);
    }
    .modal-header .btn-close {
        filter: invert(0.6); /* Icono de cierre oscuro */
    }
    /* Botones primarios (Guardar/Agregar) con tono rosado */
    .btn-primary {
        background-color: #d63384;
        border-color: #d63384;
    }
    .btn-primary:hover {
        background-color: #c1206e;
        border-color: #c1206e;
    }
    /* Otros botones outline (Editar/Habilitar) en rosa */
    .btn-outline-success,
    .btn-outline-info {
       color: #d63384;
        border-color: #d63384;
    }
    .btn-outline-success:hover,
    .btn-outline-info:hover {
        background-color: #d63384;
        color: white;
        border-color: #d63384;
    }
    /* Botón Deshabilitar en color dorado coherente */
    .btn-outline-warning {
        color: #e69500;
        border-color: #e69500;
    }
    .btn-outline-warning:hover {
        background-color: #e69500;
        color: white;
        border-color: #e69500;
    }
    #userDropdown.btn-outline-primary {
        color: #ff6b9d;         
        border-color: #ff6b9d;
        border-radius: 25rem;
    }
    #userDropdown.btn-outline-primary:hover,
    #userDropdown.btn-outline-primary:focus {
        background-color: #ff6b9d;
        color: white;
        border-color: #ff6b9d;
    }
    

  </style>
</head>
<body class="bg-light">
<!-- Botón para abrir sidebar en móvil -->
<button class="btn btn-outline-secondary d-lg-none m-3" id="mobileSidebarToggle">
    <i class="bi bi-list"></i>
</button>
<div class="d-flex">
    <!-- Barra lateral -->
    <nav id="sidebar" class="border-end sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="#">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            <button class="toggle-btn d-none d-lg-inline" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        <ul class="nav flex-column">
            <!-- Menú solo para administrador -->
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
           <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>
    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="bi bi-box-seam"></i> Gestión de Inventario</h1>
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
                        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
        </div>
        <!-- Mensajes de alerta -->
        <?php echo $alert; ?>
        <div class="container py-4">
            <div class="row">
                <!-- Zona de tarjetas -->
                <div class="col-lg-12">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <!-- Tarjeta para agregar nuevo elemento -->
                        <div class="col">
                            <div class="card add-card h-100 shadow-sm rounded-4 border-0" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center" style="height: 200px;">
                                    <i class="bi bi-plus-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-2 fw-bold mb-0">Agregar producto</p>
                                </div>
                            </div>
                        </div>
                        <?php while ($producto = mysqli_fetch_assoc($res)) : ?>
                            <?php $disabled = ($producto['estado'] !== 'activo'); ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm rounded-4 border-0 <?php echo $disabled ? 'disabled-card' : ''; ?>">
                                    <?php if (!empty($producto['imagen'])): ?>
                                        <img src="../<?php echo $producto['imagen']; ?>" class="card-img-top rounded-top-4" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="height:200px; object-fit:cover;">
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                        <p class="card-text text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                        <h6><strong>Ingredientes:</strong></h6>
                                        <div class="mb-2">
                                            <ul class="ps-3">
                                                <?php foreach (explode(',', $producto['ingredientes']) as $ing): ?>
                                                    <li class="text-muted small"><?php echo trim($ing); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="mt-auto">
                                            <p class="mb-1 precio-color"><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                                            <p class="mb-1 text-secondary"><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?></p>
                                            <p class="mb-2">
                                                <?php if ($producto['estado'] === 'activo'): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="d-flex gap-2">
                                                <!-- Botón editar -->
                                                <button type="button" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id']; ?>">Editar</button>
                                                <!-- Formulario para habilitar/deshabilitar -->
                                                <form action="../controllers/toggle_insumo.php" method="post" class="w-100">
                                                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                                    <?php if ($producto['estado'] === 'activo'): ?>
                                                        <button type="submit" name="accion" value="deshabilitar" class="btn btn-outline-warning w-100 rounded-pill" onclick="return confirm('¿Deshabilitar este producto?');">Deshabilitar</button>
                                                    <?php else: ?>
                                                        <button type="submit" name="accion" value="habilitar" class="btn btn-outline-info w-100 rounded-pill" onclick="return confirm('¿Habilitar este producto?');">Habilitar</button>
                                                    <?php endif; ?>
                                                </form>
                                                <!-- Botón eliminar -->
                                                <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $producto['id']; ?>">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal de confirmación para eliminar -->
                            <div class="modal fade" id="confirmarEliminar<?php echo $producto['id']; ?>" tabindex="-1" aria-labelledby="confirmarEliminarLabel<?php echo $producto['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="confirmarEliminarLabel<?php echo $producto['id']; ?>">Confirmar eliminación</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que deseas eliminar el producto <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="../controllers/eliminar_producto.php" method="POST">
                                                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal de edición -->
                            <div class="modal fade" id="modalEditar<?php echo $producto['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $producto['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form class="modal-content" action="../controllers/actualizar_producto.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalEditarLabel<?php echo $producto['id']; ?>">Editar Producto</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <input type="hidden" name="imagenActual" value="<?php echo $producto['imagen']; ?>">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Descripción</label>
                                                <textarea name="descripcion" class="form-control" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Ingredientes</label>
                                                <div id="contenedor-ingredientes<?php echo $producto['id']; ?>" class="row">
                                                    <?php 
                                                    $ings = explode(',', $producto['ingredientes']);
                                                    $colIndex = 1;
                                                    echo '<div class="col-md-6" id="columna-ingredientes-' . $producto['id'] . '-' . $colIndex . '">';
                                                    foreach ($ings as $index => $ing) {
                                                        if ($index > 0 && $index % 5 === 0) {
                                                            echo '</div><div class="col-md-6" id="columna-ingredientes-' . $producto['id'] . '-' . (++$colIndex) . '">';
                                                        }
                                                        ?>
                                                        <div class="input-group mb-2">
                                                            <input type="text" name="ingredientes[]" class="form-control" value="<?php echo htmlspecialchars(trim($ing)); ?>">
                                                            <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                        <?php
                                                    }
                                                    echo '</div>';
                                                    ?>
                                                </div>
                                                <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarCampo('<?php echo $producto['id']; ?>')">
                                                    <i class="bi bi-plus-circle"></i> Agregar otro ingrediente
                                                </button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Precio ($)</label>
                                                <input type="number" step="0.01" name="precio" class="form-control" min="1" value="<?php echo $producto['precio']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Stock</label>
                                                <input type="number" name="stock" class="form-control" min="1" value="<?php echo $producto['stock']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Imagen (opcional)</label>
                                                <input type="file" name="imagen" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para agregar nuevo producto -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="../controllers/crear_productos.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearLabel">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ingredientes</label>
                    <div id="contenedor-ingredientes-nuevo" class="row">
                        <div class="col-md-6" id="columna-ingredientes-nuevo-1">
                            <div class="input-group mb-2">
                                <input type="text" name="ingredientes[]" class="form-control">
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarCampo('nuevo')">
                        <i class="bi bi-plus-circle"></i> Agregar otro ingrediente
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para agregar campos de ingredientes dinámicamente
function agregarCampo(id) {
    let contenedor;
    if (id === 'nuevo') {
        contenedor = document.getElementById('contenedor-ingredientes-nuevo');
    } else {
        contenedor = document.getElementById('contenedor-ingredientes' + id);
    }
    const columnas = contenedor.querySelectorAll('[id^="columna-ingredientes-' + id + '"]');
    let ultimaColumna = columnas[columnas.length - 1];
    // Si la última columna tiene 5 elementos, crear una nueva columna
    if (ultimaColumna.querySelectorAll('.input-group').length >= 5) {
        const nuevaColumna = document.createElement('div');
        nuevaColumna.className = 'col-md-6';
        const nuevoId = 'columna-ingredientes-' + id + '-' + (columnas.length + 1);
        nuevaColumna.id = nuevoId;
        contenedor.appendChild(nuevaColumna);
        ultimaColumna = nuevaColumna;
    }
    const nuevoCampo = document.createElement('div');
    nuevoCampo.className = 'input-group mb-2';
    nuevoCampo.innerHTML = `
        <input type="text" name="ingredientes[]" class="form-control">
        <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    ultimaColumna.appendChild(nuevoCampo);
}

// Función para eliminar campos de ingredientes
function eliminarCampo(boton) {
    const grupo = boton.closest('.input-group');
    const columna = boton.closest('.col-md-6');
    grupo.remove();
    // Si la columna queda vacía y no es la única, eliminarla
    if (columna && columna.parentNode.querySelectorAll('.col-md-6').length > 1 && columna.querySelectorAll('.input-group').length === 0) {
        columna.remove();
    }
}

// Control del sidebar
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
