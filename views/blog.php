<?php
session_start();

require_once '../models/MySQL.php';

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
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
      
      /* Estilos para las tarjetas de blog */
      .blog-card {
          border: none;
          border-radius: 10px;
          overflow: hidden;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
          height: 100%;
      }
      
      .blog-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      }
      
      .blog-img {
          width: 100%;
          height: 200px;
          object-fit: cover;
      }
      
      .modal-dialog-slideout {
          min-height: 100%;
          margin: 0 0 0 auto;
          background: #fff;
      }
      
      .modal.fade .modal-dialog.modal-dialog-slideout {
          transform: translate(100%, 0);
      }
      
      .modal.fade.show .modal-dialog.modal-dialog-slideout {
          transform: translate(0, 0);
      }
      
      /* Estilo para evitar parpadeo en modales */
      .modal-backdrop {
          opacity: 0.5 !important;
      }
      
      /* Estilo para el botón de eliminar */
      .btn-eliminar {
          width: 40px;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0;
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
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> Estadísticas</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> Insumos</span></a></li>
            <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> Mis Entregas</span></a></li>
            <?php } ?>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos_usuario.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog_usuario.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/contacto.php"><i class="bi bi-envelope"></i><span> Contacto</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/user_pedidos.php"><i class="bi bi-list-check"></i><span> Mis Pedidos</span></a></li>
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

        <div class="row g-4">
            <?php foreach ($articulos as $articulo): ?>
            <div class="col-md-3">
                <div class="card blog-card h-100">
                    <img src="../<?php echo $articulo['imagen']; ?>" alt="Imagen del artículo" class="blog-img">
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
                    <div class="modal-content rounded-4">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title" id="modalVerMasLabel<?php echo $articulo['id']; ?>">
                                <?php echo htmlspecialchars($articulo['titulo']); ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($articulo['imagen'])): ?>
                                <img src="../<?php echo $articulo['imagen']; ?>" class="img-fluid mb-4 rounded-3"
                                     alt="Imagen del artículo">
                            <?php endif; ?>
                            <p><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de edición de artículo -->
            <div class="modal fade" id="modalEditar<?php echo $articulo['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content form-container" action="../controllers/actualizar_articulo.php" method="POST" enctype="multipart/form-data">
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