<?php
require_once '../models/MySQL.php';

session_start();

    if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");

    exit();
} 

$mysql = new MySQL();
$mysql->conectar(); 

$resultado = $mysql->efectuarConsulta("SELECT * FROM productos");

?>
//aaa
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="../views/index.php">
      <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
        <li class="nav-item"><a class="nav-link active" href="../views/creacion_productos.php">CREAR</a></li>
      <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Tienda</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
      </ul>

      <!-- Buscador -->
      <form class="d-flex me-3" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </form>

              <!-- Iconos -->
              <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['correo'])): ?>
            <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
            <?php else: ?>
            <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
              <a href="../views/carrito.php"><button class="btn btn-outline-success"><i class="bi bi-bag"></i> Carrito</button></a>
            <?php } ?>
            
        </div>
    </div>
  </div>
</nav>

<!-- Encabezado -->
<header class="bg-light py-5 text-center">
  <div class="container">
    <h1 class="display-5">Productos Lácteos Artesanales</h1>
    <p class="lead">Disfruta del sabor auténtico de Asturias.</p>
  </div>
</header>

<div class="container py-5">
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
    <?php while ($producto = mysqli_fetch_assoc($resultado)) : ?>
      <div class="col">
        <div class="card h-100 shadow-sm rounded-4 border-0">
          <img src="../<?php echo $producto['imagen']; ?>"
                class="card-img-top rounded-top-4"
                alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
            <p class="card-text text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
            <div class="mt-auto">
              <p class="mb-1 text-success"><strong>Precio:</strong> €<?php echo htmlspecialchars($producto['precio']); ?></p>
              <p class="mb-2 text-secondary"><strong>Stock:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
              <div class="d-flex gap-2">
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                  <a href="#" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $producto['id']; ?>">Ver Más</a>
                <?php } ?>
                <!-- Botón para abrir el modal -->
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                  <button type="button" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id']; ?>"> Editar</button>
                  <form action="../controllers/eliminar_producto.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                    <button type="submit" class="btn btn-outline-danger rounded-pill">Eliminar</button>
                  </form>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Ver Más -->
      <div class="modal fade" id="modalVerMas<?php echo $producto['id']; ?>" tabindex="-1" aria-labelledby="modalVerMasLabel<?php echo $producto['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content rounded-4 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold" id="modalVerMasLabel<?php echo $producto['id']; ?>">
                <?php echo htmlspecialchars($producto['nombre']); ?>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="row g-4">
                <!-- Imagen del producto -->
                <div class="col-md-5 text-center">
                  <img src="../<?php echo $producto['imagen']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                </div>
                
                <!-- Detalles del producto -->
                <div class="col-md-7">
                  <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['descripcion']); ?></p>
                  <p class="text-success h5"><strong>Precio:</strong> €<?php echo htmlspecialchars($producto['precio']); ?></p>
                  <p><strong>Stock disponible:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>

                  <!-- Formulario para agregar al carrito -->
                  <form action="ruta/a/agregar_carrito.php" method="POST" class="mt-4">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                    
                    <div class="mb-3">
                      <label for="cantidad<?php echo $producto['id']; ?>" class="form-label">Cantidad:</label>
                      <input type="number" name="cantidad" id="cantidad<?php echo $producto['id']; ?>" class="form-control" min="1" max="<?php echo htmlspecialchars($producto['stock']); ?>" value="1" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 rounded-pill">
                      Agregar al Carrito
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <div class="modal-footer border-0">
              <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
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
                <label class="form-label">Precio (€)</label>
                <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $producto['precio']; ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" value="<?php echo $producto['stock']; ?>" required>
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

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
    <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
