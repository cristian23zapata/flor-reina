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
    <link rel="stylesheet" href="../assets/css/estilo_registrar.css">
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
            <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
            <?php } ?>
            <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
            <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
        </ul>
        </div>

        <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
        <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
        <?php else: ?>
        <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
        <?php endif; ?>
        </div>
    </div>
</nav>

<br>

<div class="form-container" style="max-width: 75%; margin: auto;">
  <h2 class="text-center">Registrar nuevo repartidor</h2>

  <form action="../controllers/registrar_repartidor.php" method="POST" enctype="multipart/form-data">

    <div class="mb-3">
      <label class="form-label">Nombre completo</label>
      <input type="text" class="form-control" name="nombre" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Correo electrónico</label>
      <input type="email" class="form-control" name="correo" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Teléfono</label>
      <input type="tel" class="form-control" name="telefono" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="text" class="form-control border-pink" id="password" name="password" placeholder="********" required>
    </div>

    <div class="mb-3">
      <label for="confirmar_password" class="form-label">Confirmar contraseña</label>
      <input type="text" class="form-control border-pink" id="confirmar_password" name="confirmar_password" placeholder="********" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de transporte</label>
      <select class="form-control" name="transporte" required>
        <option value="">Selecciona una opción</option>
        <option value="bicicleta">Bicicleta</option>
        <option value="moto">Motocicleta</option>
        <option value="auto">Automóvil</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Foto de identificación</label>
      <input type="file" class="form-control" name="foto">
    </div>

    <button type="submit" class="btn btn-outline-primary">Registrar repartidor</button>
  </form>
</div>



</body>
</html>