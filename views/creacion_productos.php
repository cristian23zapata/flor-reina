<?php
require_once '../models/MySQL.php';

session_start();

    if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
    }

    $mysql = new MySQL;
    $mysql->conectar();

    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios;");

    $mysql->desconectar();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion_productos.css">
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
        <li class="nav-item"><a class="nav-link active" href="../views/creacion_productos.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Tienda</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
    </ul>

        <!-- Iconos -->
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['correo'])): ?>
            <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
            <?php else: ?>
            <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <button class="btn btn-outline-success"><i class="bi bi-bag"></i> Carrito</button>
            <?php } ?>
        </div>
    </div>
</div>
</nav>

<div class="container my-5">
  <h2 class="mb-4 text-center">Crear nuevo producto</h2>
  <form action="../controllers/crear_productos.php" method="POST" enctype="multipart/form-data" class="form-container">
    
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre del producto</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <div class="mb-3">
      <label for="descripcion" class="form-label">Descripción</label>
      <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
    </div>

    <div class="mb-3">
      <label for="precio" class="form-label">Precio (€)</label>
      <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock disponible</label>
      <input type="number" class="form-control" id="stock" name="stock" required>
    </div>
    <!--ingredientes-->
    <div class="mb-3">
      <label for="ingredientes" class="form-label">Ingredientes</label>
      <div id="nuevos-ingredientes">
        <div class="input-group mb-2 w-50">
        <input type="text" name="ingredientes[]" class="form-control" placeholder="Escribe un ingrediente">
    <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
      <i class="bi bi-trash"></i>
    </button>
        </div>
      </div>
      <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarCampo()">
        <i class="bi bi-plus-circle"></i> Agregar otro ingrediente
      </button>
    </div>
    <!--fin ingredientes-->
    <div class="mb-4">
      <label for="imagen" class="form-label">Imagen del producto</label>
      <input class="form-control" type="file" id="imagen" name="imagen" accept=".jpg, .jpeg, .png" required>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-outline-success px-5">Crear producto</button>
    </div>

  </form>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
        <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
    </div>

</footer>
<script>
    function agregarCampo() {
  const contenedor = document.getElementById('nuevos-ingredientes');
  const nuevoCampo = document.createElement('div');
  nuevoCampo.className = 'input-group mb-2 w-50';
  nuevoCampo.innerHTML = `
    <input type="text" name="ingredientes[]" class="form-control" placeholder="Escribe un ingrediente">
    <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
      <i class="bi bi-trash"></i>
    </button>
  `;
  contenedor.appendChild(nuevoCampo);
}

function eliminarCampo(boton) {
  const grupo = boton.parentNode;
  grupo.parentNode.removeChild(grupo);
}

  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
//a
</body>
</html>
