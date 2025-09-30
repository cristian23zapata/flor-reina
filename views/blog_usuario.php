<?php
session_start();
require_once '../models/MySQL.php';

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
  <title>Blog | Tu Página</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_carrito.css">
  <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link rel="stylesheet" href="../assets/css/estilo_blog.css">
  <link rel="stylesheet" href="../assets/css/estilo_productos.css">
  <link rel="stylesheet" href="../assets/css/new.css">
  <style>
    /* Estilos responsivos para el blog */
    .blog-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
    }
    
    .blog-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .blog-img {
      height: 200px;
      object-fit: cover;
      width: 100%;
    }
    
    /* Ajustes para móviles */
    @media (max-width: 768px) {
      .blog-img {
        height: 180px;
      }
      
      .container.py-5 {
        padding: 1rem !important;
      }
      
      h1.mb-4 {
        font-size: 1.8rem;
        margin-bottom: 2rem !important;
      }
    }
    
    @media (max-width: 576px) {
      .blog-img {
        height: 160px;
      }
      
      .card-body {
        padding: 1rem;
      }
      
      .btn-group-mobile {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .btn-group-mobile .btn {
        width: 100%;
        margin: 0;
      }
    }
    
    /* Grid responsivo */
    @media (min-width: 1200px) {
      .col-md-3 {
        flex: 0 0 auto;
        width: 25%;
      }
    }
    
    @media (max-width: 1199px) and (min-width: 768px) {
      .col-md-3 {
        flex: 0 0 auto;
        width: 50%;
      }
    }
    
    @media (max-width: 767px) {
      .col-md-3 {
        flex: 0 0 auto;
        width: 100%;
      }
    }
    
    /* Modales responsivos */
    .modal-content {
      border: none;
      border-radius: 12px;
    }
    
    @media (max-width: 576px) {
      .modal-dialog {
        margin: 0.5rem;
      }
      
      .modal-body img {
        height: 200px;
        object-fit: cover;
      }
    }
    
    /* Botones responsivos */
    .btn-responsive {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }
    
    @media (max-width: 576px) {
      .btn-responsive {
        padding: 0.75rem 1rem;
        font-size: 1rem;
      }
    }
    
    /* Texto responsivo */
    .card-title {
      font-size: 1.1rem;
      line-height: 1.4;
      min-height: 3em;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .card-text {
      font-size: 0.9rem;
      line-height: 1.5;
      min-height: 4.5em;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    @media (max-width: 576px) {
      .card-title {
        font-size: 1.2rem;
        min-height: auto;
      }
      
      .card-text {
        font-size: 1rem;
        min-height: auto;
      }
    }
    
    /* Espaciado responsivo */
    .row.g-4 {
      --bs-gutter-y: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .row.g-4 {
        --bs-gutter-y: 1rem;
      }
    }
    
    /* Footer siempre abajo */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }
    
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    main {
      flex: 1 0 auto;
    }
    
    footer {
      flex-shrink: 0;
      margin-top: auto;
    }
  </style>
</head>
<body>
<?php include '../views/partials/carrito_modal.php'; ?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="../index.php">
      <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
    </a>

    <!-- Contenedor para botón de usuario y hamburguesa en móvil -->
    <div class="d-flex d-lg-none align-items-center gap-2">
      <!-- Botón de usuario en móvil -->
      <?php if (isset($_SESSION['correo'])): ?>
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuMobile">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
              <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
              <li><hr class="dropdown-divider"></li>
            <?php } ?>
            <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="../views/login.php" class="btn btn-outline-primary">
          <i class="bi bi-person-circle"></i>
        </a>
      <?php endif; ?>

      <!-- Botón hamburguesa -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
          <li class="nav-item"><a class="nav-link active" href="../views/admin_pedidos.php">PEDIDOS</a></li>
          <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">REPARTIDORES</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
        <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
          <li class="nav-item"><a class="nav-link active" href="../views/repartidores.php">Mis Entregas</a></li> 
        <?php } ?>
        
        <?php if (empty($_SESSION['tipo'])) { ?>
          <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
        <?php } ?>
        
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
          <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
          <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
        <?php } ?>
      </ul>

      <!-- Botón de usuario visible en desktop -->
      <div class="d-none d-lg-flex align-items-center gap-2 ms-auto">
        <?php if (isset($_SESSION['correo'])): ?>
          <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuDesktop" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i>
              <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuDesktop">
              <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php } ?>
              <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="../views/login.php" class="btn btn-outline-primary d-flex align-items-center">
            <i class="bi bi-person-circle me-1"></i>
            <span>Login</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<main>
  <div class="container py-5">
    <h1 class="mb-4 fw-bold text-center">Nuestro Blog</h1>
    <div class="row g-4">
      <?php foreach ($articulos as $articulo): ?>
        <div class="col-md-3">
          <div class="card blog-card h-100">
            <img src="../<?php echo $articulo['imagen']; ?>" alt="Imagen del artículo" class="blog-img">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title fw-bold"><?php echo $articulo['titulo']; ?></h5>
              <p class="card-text text-muted"><?php echo mb_strimwidth($articulo['contenido'], 0, 100, '...'); ?></p>
              <div class="d-flex gap-2 mt-auto btn-group-mobile">
                <a href="#" class="btn btn-outline-success w-100 rounded-pill btn-responsive"
                   data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $articulo['id']; ?>">
                  Ver Más
                </a>

                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                  <button type="button" class="btn btn-outline-success w-100 rounded-pill btn-responsive"
                          data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $articulo['id']; ?>">
                    Editar
                  </button>
                  <button type="button" class="btn btn-outline-danger rounded-pill btn-responsive"
                          data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $articulo['id']; ?>">
                    Eliminar
                  </button>

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
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Ver Más -->
        <div class="modal fade" id="modalVerMas<?php echo $articulo['id']; ?>" tabindex="-1"
             aria-labelledby="modalVerMasLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
              <div class="modal-header bg-rodado text-white border-0">
                <h5 class="modal-title" id="modalVerMasLabel<?php echo $articulo['id']; ?>">
                  <?php echo htmlspecialchars($articulo['titulo']); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <?php if (!empty($articulo['imagen'])): ?>
                  <img src="../<?php echo $articulo['imagen']; ?>" class="img-fluid mb-4 rounded-3 w-100"
                       alt="Imagen del artículo" style="max-height: 400px; object-fit: cover;">
                <?php endif; ?>
                <p class="text-justify"><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
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

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
</main>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-auto">
  <div class="container text-center">
    <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
    <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Script para mejorar la responsividad
  document.addEventListener('DOMContentLoaded', function() {
    // Ajustar altura de imágenes en tarjetas
    function ajustarAlturaImagenes() {
      const blogImgs = document.querySelectorAll('.blog-img');
      let maxHeight = 0;
      
      // Reset heights
      blogImgs.forEach(img => {
        img.style.height = 'auto';
      });
      
      // Encontrar la altura máxima
      blogImgs.forEach(img => {
        if (img.naturalHeight > 0) {
          maxHeight = Math.max(maxHeight, img.clientHeight);
        }
      });
      
      // Aplicar altura máxima solo en desktop
      if (window.innerWidth >= 768) {
        blogImgs.forEach(img => {
          img.style.height = maxHeight + 'px';
        });
      }
    }
    
    // Ejecutar al cargar y al redimensionar
    ajustarAlturaImagenes();
    window.addEventListener('resize', ajustarAlturaImagenes);
    
    // Mejorar experiencia táctil en móviles
    if ('ontouchstart' in window) {
      document.querySelectorAll('.blog-card').forEach(card => {
        card.style.cursor = 'pointer';
      });
    }
  });
</script>
</body>
</html>