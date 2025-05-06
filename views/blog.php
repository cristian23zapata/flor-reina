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
  <title>Blog | Tu Página</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <style>
    .blog-card {
      border-radius: 1.5rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
      transition: transform 0.2s ease-in-out;
    }
    .blog-card:hover {
      transform: scale(1.01);
    }
    .blog-img {
      border-top-left-radius: 1.5rem;
      border-top-right-radius: 1.5rem;
      height: 200px;
      object-fit: cover;
      width: 100%;
    }
  </style>
</head>
<body>

<!-- Navbar -->
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
        <li class="nav-item"><a class="nav-link active" href="../views/creacion_productos.php">CREAR</a></li>
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
      </div>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h1 class="mb-4 fw-bold text-center">Nuestro Blog</h1>
  <div class="row g-4">

    <?php
    // Ejemplo de artículos de blog (normalmente vienen de base de datos)
    $articulos = [
      [
        'titulo' => 'Cómo elegir el mejor producto',
        'resumen' => 'Descubre los factores más importantes a tener en cuenta antes de realizar una compra en línea.',
        'imagen' => 'ruta/a/imagen1.jpg',
        'link' => 'articulo1.php'
      ],
      [
        'titulo' => 'Tendencias tecnológicas 2025',
        'resumen' => 'Analizamos las tecnologías emergentes que marcarán el futuro del ecommerce y la vida diaria.',
        'imagen' => 'ruta/a/imagen2.jpg',
        'link' => 'articulo2.php'
      ],
      [
        'titulo' => 'Cómo optimizar tu carrito de compras',
        'resumen' => 'Consejos prácticos para mejorar tu experiencia de compra y ahorrar dinero.',
        'imagen' => 'ruta/a/imagen3.jpg',
        'link' => 'articulo3.php'
      ],
      [
        'titulo' => 'Cómo optimizar tu carrito de compras',
        'resumen' => 'Consejos prácticos para mejorar tu experiencia de compra y ahorrar dinero.',
        'imagen' => 'ruta/a/imagen3.jpg',
        'link' => 'articulo3.php'
      ],
      [
        'titulo' => 'Cómo elegir el mejor producto',
        'resumen' => 'Descubre los factores más importantes a tener en cuenta antes de realizar una compra en línea.',
        'imagen' => 'ruta/a/imagen1.jpg',
        'link' => 'articulo1.php'
      ],
      [
        'titulo' => 'Tendencias tecnológicas 2025',
        'resumen' => 'Analizamos las tecnologías emergentes que marcarán el futuro del ecommerce y la vida diaria.',
        'imagen' => 'ruta/a/imagen2.jpg',
        'link' => 'articulo2.php'
      ],
      [
        'titulo' => 'Cómo optimizar tu carrito de compras',
        'resumen' => 'Consejos prácticos para mejorar tu experiencia de compra y ahorrar dinero.',
        'imagen' => 'ruta/a/imagen3.jpg',
        'link' => 'articulo3.php'
      ],
      [
        'titulo' => 'Cómo optimizar tu carrito de compras',
        'resumen' => 'Consejos prácticos para mejorar tu experiencia de compra y ahorrar dinero.',
        'imagen' => 'ruta/a/imagen3.jpg',
        'link' => 'articulo3.php'
      ]
    ];

    foreach ($articulos as $articulo): ?>
      <div class="col-md-3">
        <div class="card blog-card h-100">
          <img src="<?php echo $articulo['imagen']; ?>" alt="Imagen del artículo" class="blog-img">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold"><?php echo $articulo['titulo']; ?></h5>
            <p class="card-text text-muted"><?php echo $articulo['resumen']; ?></p>
            <a href="<?php echo $articulo['link']; ?>" class="btn btn-outline-success mt-auto rounded-pill">Leer más</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

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
