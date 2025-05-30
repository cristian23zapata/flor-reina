<?php
require_once '../models/MySQL.php';

session_start();

    
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
    <title>Contacto | Tu Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_contacto.css">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
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
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Tienda</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
      </ul>

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

  <!-- Contenido principal -->
  <div class="contact-section">
  <div class="contact-info">
    <h3>Información de contacto</h3>
    <p>📞 +34 123 456 789</p>
    <p>✉️ contacto@tutienda.com</p>
    <p>📍 Calle Ficticia 123, Madrid, España</p>
    <p>🕒 Lunes a Viernes, 9:00 - 18:00</p>
  </div>
  <div class="contact-form">
    <h3>Contáctanos</h3>
    <form>
      <input type="text" placeholder="Nombre" required>
      <input type="email" placeholder="Correo electrónico" required>
      <textarea placeholder="Mensaje" rows="5" required></textarea>
      <button type="submit">Enviar mensaje</button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


