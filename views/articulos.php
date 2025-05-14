<?php
require_once '../models/MySQL.php';
session_start();

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

$resultado = $mysql->efectuarConsulta("SELECT * FROM articulos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Blog - Flor Reina</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
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

      <form class="d-flex me-3" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
          <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
          <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
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

<!-- Encabezado -->
<header class="bg-light py-5 text-center">
  <div class="container">
    <h1 class="display-5">Nuestro Blog</h1>
    <p class="lead">Artículos y noticias sobre nuestros productos artesanales.</p>
  </div>
</header>

<!-- Contenido de artículos -->
<div class="container py-5">
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
    <?php while ($articulo = mysqli_fetch_assoc($resultado)) : ?>
      <div class="col">
        <div class="card h-100 shadow-sm rounded-4 border-0">
          <?php if (!empty($articulo['imagen'])): ?>
            <img src="../<?php echo $articulo['imagen']; ?>" class="card-img-top rounded-top-4" alt="<?php echo htmlspecialchars($articulo['titulo']); ?>" style="height: 200px; object-fit: cover;">
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($articulo['titulo']); ?></h5>
            <p class="card-text text-muted"><?php echo substr(htmlspecialchars($articulo['contenido']), 0, 120); ?>...</p>
            <div class="mt-auto">
              <a href="#" class="btn btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalArticulo<?php echo $articulo['id']; ?>">Leer más</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Leer Más -->
      <div class="modal fade" id="modalArticulo<?php echo $articulo['id']; ?>" tabindex="-1" aria-labelledby="modalArticuloLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content rounded-4">
            <div class="modal-header bg-primary text-white border-0">
              <h5 class="modal-title" id="modalArticuloLabel<?php echo $articulo['id']; ?>">
                <?php echo htmlspecialchars($articulo['titulo']); ?>
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <?php if (!empty($articulo['imagen'])): ?>
                <img src="../<?php echo $articulo['imagen']; ?>" class="img-fluid mb-4 rounded-3" alt="Imagen del artículo">
              <?php endif; ?>
              <p><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
            </div>
          </div>
        </div>
      </div>

    <?php endwhile; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
