<?php
session_start();

require_once '../models/MySQL.php';

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
}

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { 
    header("Location: ../index.php");
    exit();
}

$mysql = new MySQL;
$mysql->conectar();
$resultado = $mysql->efectuarConsulta("SELECT * FROM usuarios;");
$articulos = $mysql->efectuarConsulta("SELECT * FROM articulos;");
$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog | Flor Reina</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_blog.css">
    <link rel="stylesheet" href="../assets/css/estilo_productos.css">
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
        
        /* Estilo del contenedor del filtro */
        .filter-sidebar {
            background-color: #ffffff; /* Fondo blanco para el filtro */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Sombra suave */
            padding: 20px;
            margin-bottom: 20px; /* Espacio debajo del filtro en pantallas pequeñas */
        }

        /* Ajustes para el acordeón del filtro */
        .filter-sidebar .accordion-button {
            font-size: 1.1em;
            color: #d63384; /* Color del texto del botón del acordeón */
            background-color: #f8f9fa; /* Fondo del botón del acordeón */
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        /* Estilo cuando el acordeón está expandido */
        .filter-sidebar .accordion-button:not(.collapsed) {
            background-color: #f4e6eb; /* Fondo más claro cuando está abierto */
            color: #ac4563; /* Color de texto más oscuro cuando está abierto */
        }
        .filter-sidebar .accordion-body {
            padding-top: 15px;
            padding-bottom: 0;
        }
        .filter-sidebar .form-check-label {
            font-size: 0.95em;
            color: #5a5a5a;
        }

        /* Estilo para los botones dentro del filtro */
        .filter-sidebar .btn-primary {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        .filter-sidebar .btn-primary:hover {
            background-color: #e55a8a; /* Rosa más oscuro */
            border-color: #e55a8a; /* Rosa más oscuro */
        }
        .filter-sidebar .btn-outline-secondary {
            border-color: #6c757d; /* Color gris de Bootstrap */
            color: #6c757d;
        }
        .filter-sidebar .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }

        /* Media queries para que el filtro sea sticky en pantallas grandes */
        @media (min-width: 768px) {
            .filter-sidebar {
                position: sticky; /* Hace que el filtro se quede fijo al hacer scroll */
                top: 20px; /* Distancia desde la parte superior de la ventana */
                align-self: flex-start; /* Ayuda al sticky en contenedores flex */
                max-height: calc(100vh - 40px); /* Para que no ocupe más de la altura de la ventana */
                overflow-y: auto; /* Permite scroll si el contenido del filtro es muy largo */
            }
        }
        
        /* ESTILOS ROSADOS PARA BOTONES */
        .btn-primary {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-primary:hover {
            background-color: #e55a8a; /* Rosa más oscuro */
            border-color: #e55a8a; /* Rosa más oscuro */
        }
        
        .btn-outline-primary {
            color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-outline-primary:hover {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .btn-outline-success {
            color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-outline-success:hover {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .btn-outline-danger {
            color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-outline-danger:hover {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .btn-outline-secondary {
            color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-outline-secondary:hover {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .btn-danger {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-danger:hover {
            background-color: #e55a8a; /* Rosa más oscuro */
            border-color: #e55a8a; /* Rosa más oscuro */
        }
        
        .btn-success {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
        }
        
        .btn-success:hover {
            background-color: #e55a8a; /* Rosa más oscuro */
            border-color: #e55a8a; /* Rosa más oscuro */
        }
        
        /* Estilos para encabezados de modales */
        .modal-header {
            background-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .modal-header .btn-close {
            filter: invert(1); /* Hace que la X sea blanca */
        }
        
        .bg-primary {
            background-color: #ff6b9d !important; /* Rosa */
        }
        
        .bg-danger {
            background-color: #e55a8a !important; /* Rosa más oscuro */
        }
        
        /* Estilo para el botón de crear artículo */
        .btn-crear-articulo {
            background-color: #ff6b9d; /* Rosa */
            border-color: #ff6b9d; /* Rosa */
            color: white;
        }
        
        .btn-crear-articulo:hover {
            background-color: #e55a8a; /* Rosa más oscuro */
            border-color: #e55a8a; /* Rosa más oscuro */
            color: white;
        }
        
        
        .add-card {
  border: 2px dashed #d63384;
  background-color: #ffe6f0;
  color: #d63384;
  transition: background .2s ease;
}
.add-card:hover { background-color: #fddbe9; text-decoration: none; }

/* Centrado perfecto del contenido de la card-botón */
.add-card .card-body {
  height: 200px;
  display: flex; align-items: center; justify-content: center;
  flex-direction: column;
}

/* Modal rosado coherente con la web */
.modal-header {
  background-color: #ffe6f0; color: #d63384;
  border-top-left-radius: calc(0.3rem - 1px);
  border-top-right-radius: calc(0.3rem - 1px);
}
.modal-header .btn-close { filter: invert(0.6); }
.btn-primary { background-color: #d63384; border-color: #d63384; }
.btn-primary:hover { background-color: #c1206e; border-color: #c1206e; }

/* Estilo unificado del botón de usuario (dropdown), igual que en Blog */
#userDropdown.btn-outline-primary { color:#ff6b9d; border-color:#ff6b9d; }
#userDropdown.btn-outline-primary:hover,
#userDropdown.btn-outline-primary:focus {
  background-color:#ff6b9d; color:#fff; border-color:#ff6b9d;
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
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> MIS ENTREGAS</span></a></li>
            <?php } ?>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos_usuario.php"><i class="bi bi-flower1"></i><span> PRODUCTOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog_usuario.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/contacto.php"><i class="bi bi-envelope"></i><span> CONTACTO</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/user_pedidos.php"><i class="bi bi-box-seam"></i><span> MIS PEDIDOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0 fw-bold text-center">Nuestro Blog</h1>
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

        <!-- Botón para abrir la modal de creación de artículos -->
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        
        <?php endif; ?>

        <div class="container py-5">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        
                        
                    <div class="col">
                        <div class="card add-card h-100 shadow-sm rounded-4 border-0"
                          data-bs-toggle="modal" data-bs-target="#modalCrearArticulo">
                            <div class="card-body">
                                <i class="bi bi-plus-circle" style="font-size:3rem;"></i>
                                <p class="mt-2 fw-bold mb-0">Agregar artículo</p>
                            </div>
                        </div>
                    </div>
                         
                        
                        <?php foreach ($articulos as $articulo): ?>
                        <div class="col">
                            <div class="card blog-card h-100 shadow-sm rounded-4 border-0">
                                <img src="../<?php echo $articulo['imagen']; ?>" alt="Imagen del artículo" class="blog-img card-img-top rounded-top-4" style="height: 200px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold"><?php echo $articulo['titulo']; ?></h5>
                                    <p class="card-text text-muted"><?php echo mb_strimwidth($articulo['contenido'], 0, 100, '...'); ?></p>
                                    <div class="d-flex gap-2 mt-auto">
                                        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user'): ?>
                                            <a href="#" class="btn btn-outline-success w-100 rounded-pill"
                                               data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $articulo['id']; ?>">
                                                Ver Más
                                            </a>
                                        <?php endif; ?>
        
                                        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                                            <button type="button" class="btn btn-outline-success w-100 rounded-pill"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $articulo['id']; ?>">
                                                Editar
                                            </button>
                                            <button type="button" class="btn btn-outline-danger rounded-pill btn-eliminar eliminar-btn"
                                                    data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $articulo['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        
                        <!-- Modal Confirmar Eliminación -->
                        <div class="modal fade" id="confirmarEliminar<?php echo $articulo['id']; ?>" tabindex="-1"
                             aria-labelledby="confirmarEliminarLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="confirmarEliminarLabel<?php echo $articulo['id']; ?>">
                                            Confirmar eliminación
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Estás seguro de que deseas eliminar el artículo <strong><?php echo htmlspecialchars($articulo['titulo']); ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="../controllers/eliminar_articulo.php" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Ver Más -->
                        <div class="modal fade" id="modalVerMas<?php echo $articulo['id']; ?>" tabindex="-1"
                             aria-labelledby="modalVerMasLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content rounded-4 shadow">
                                    <div class="modal-header bg-primary text-white border-0">
                                        <h5 class="modal-title fw-bold" id="modalVerMasLabel<?php echo $articulo['id']; ?>">
                                            <?php echo htmlspecialchars($articulo['titulo']); ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (!empty($articulo['imagen'])): ?>
                                            <img src="../<?php echo $articulo['imagen']; ?>" class="img-fluid mb-4 rounded-3"
                                                 alt="Imagen del artículo" style="height: auto; max-height: 400px; object-fit: contain;">
                                        <?php endif; ?>
                                        <p><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de edición de artículo -->
                        <div class="modal fade" id="modalEditar<?php echo $articulo['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <form class="modal-content" action="../controllers/actualizar_articulo.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditarLabel<?php echo $articulo['id']; ?>">Editar Artículo</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                                        <input type="hidden" name="imagenActual" value="<?php echo $articulo['imagen']; ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Título del artículo</label>
                                            <input type="text" name="titulo" class="form-control" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Contenido</label>
                                            <textarea name="contenido" rows="6" class="form-control" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" required><?php echo htmlspecialchars($articulo['contenido']); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Imagen de portada (opcional)</label>
                                            <input type="file" name="imagen" class="form-control">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear artículo -->
<div class="modal fade" id="modalCrearArticulo" tabindex="-1" aria-labelledby="modalCrearArticuloLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearArticuloLabel">Crear Nuevo Artículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="../controllers/crear_articulos.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título del artículo</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" required>
                    </div>

                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido</label>
                        <textarea class="form-control" id="contenido" name="contenido" 
                                  title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" 
                                  rows="6" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="imagenArticulo" class="form-label">Imagen de portada</label>
                        <input class="form-control" type="file" id="imagenArticulo" name="imagen" 
                               accept=".jpg, .jpeg, .png" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-outline-success">Crear artículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
        <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
    </div>
</footer>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Artículo actualizado correctamente.',
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

        // ✅ Eliminar los parámetros de la URL sin recargar
        if (window.history.replaceState) {
            const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: url }, "", url);
        }
    </script>
<?php endif; ?>

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
    
    // Solución para el problema del backdrop que no se elimina
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        // Cuando se oculta un modal, forzar la eliminación del backdrop
        modal.addEventListener('hidden.bs.modal', function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.parentNode.removeChild(backdrop);
            });
            
            // También eliminar la clase que bloquea el scroll del body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
    
    // Manejar botones de eliminar
    const eliminarBtns = document.querySelectorAll('.eliminar-btn');
    eliminarBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetModal = this.getAttribute('data-bs-target');
            const modal = document.querySelector(targetModal);
            if (modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        });
    });
});
</script>
</body>
</html>