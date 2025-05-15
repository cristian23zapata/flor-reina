<?php
require_once '../models/MySQL.php';

session_start();

$mysql = new MySQL;
$mysql->conectar();
$resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios;");

$articulos = $mysql->efectuarConsulta("SELECT * FROM articulos;");

$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Blog | Tu Página</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link rel="stylesheet" href="../assets/css/estilo_blog.css">
  <link rel="stylesheet" href="../assets/css/estilo_productos.css">
</head>
<body>

<!-- Navbar -->
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
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
        <?php } ?>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
          <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
          <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
        <?php else: ?>
          <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

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
        <div class="d-flex gap-2 mt-auto">
          <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user'): ?>
            <a href="#" class="btn btn-outline-success w-100 rounded-pill"
               data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $articulo['id']; ?>">
              Ver Más
            </a>
          <?php endif; ?>
a
          <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
            <button type="button" class="btn btn-outline-success w-100 rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $articulo['id']; ?>">
              Editar
            </button>
            <button type="button" class="btn btn-outline-danger rounded-pill"
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
<?php endforeach; ?>

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
          <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Contenido</label>
          <textarea name="contenido" rows="6" class="form-control" required><?php echo htmlspecialchars($articulo['contenido']); ?></textarea>
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

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
    <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
