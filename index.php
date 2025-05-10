<?php
require_once 'models/MySQL.php';

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
  <title>Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/carrusel.css">
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

<!-- Carrusel Grande de Yogures -->
<div id="carouselYogures" class="carousel slide fullscreen-carousel" data-bs-ride="carousel">
  <div class="carousel-inner">
    <!-- Yogur 1 -->
    <div class="carousel-item active">
      <img src="../assets/imagenes/yogur1.jpg" class="d-block w-100" alt="Yogur Natural">
      <div class="carousel-caption animate-fadeInUp">
        <h3>Yogur Natural Artesanal</h3>
        <p>Elaborado con leche fresca de nuestras granjas asturianas, sin conservantes ni aditivos.</p>
      </div>
    </div>
    
    <!-- Yogur 2 -->
    <div class="carousel-item">
      <img src="../assets/imagenes/yogur2.webp" class="d-block w-100" alt="Yogur de Fresa">
      <div class="carousel-caption animate-fadeInUp">
        <h3>Yogur de Fresa</h3>
        <p>Fresas naturales mezcladas con nuestro yogur cremoso. Dulce y refrescante.</p>
      </div>
    </div>
    
    <!-- Yogur 3 -->
    <div class="carousel-item">
      <img src="../assets/imagenes/yogur3.jpg" class="d-block w-100" alt="Yogur Griego">
      <div class="carousel-caption animate-fadeInUp">
        <h3>Yogur Griego</h3>
        <p>Textura cremosa y alto contenido en proteínas. Ideal para deportistas.</p>
      </div>
    </div>
    
    <!-- Yogur 4 -->
    <div class="carousel-item">
      <img src="../assets/imagenes/yogur4.png" class="d-block w-100" alt="Yogur de Vainilla">
      <div class="carousel-caption animate-fadeInUp">
        <h3>Yogur de Vainilla</h3>
        <p>Vainilla natural de Madagascar para un sabor suave y aromático.</p>
      </div>
    </div>
  
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselYogures" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Anterior</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselYogures" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Siguiente</span>
  </button>
</div>

<!-- Sección de Visión y Misión con Animaciones -->
<section class="vision-mision-section">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="display-4 fw-bold">Nuestra Esencia</h2>
      <p class="lead">Lo que nos define y nos impulsa cada día</p>
    </div>
    
    <div class="row g-4">
      <!-- Visión -->
      <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
        <div class="vision-card p-5 text-center h-100">
          <i class="bi bi-eye-fill card-icon"></i>
          <h3 class="fw-bold mb-3">Visión</h3>
          <p class="fs-5">Ser reconocidos como la marca líder en yogures artesanales, ofreciendo productos de alta calidad que deleiten a nuestros clientes y promuevan un estilo de vida saludable.</p>
          <div class="mt-4" data-aos="fade-up" data-aos-delay="300">
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Innovación constante</p>
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Calidad certificada</p>
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Sostenibilidad</p>
          </div>
        </div>
      </div>
      
      <!-- Misión -->
      <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
        <div class="mision-card p-5 text-center h-100">
          <i class="bi bi-heart-fill card-icon"></i>
          <h3 class="fw-bold mb-3">Misión</h3>
          <p class="fs-5">Elaborar yogures caseros con ingredientes naturales y frescos, brindando a nuestros clientes una experiencia única de sabor y bienestar en cada cucharada.</p>
          <div class="mt-4" data-aos="fade-up" data-aos-delay="300">
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Ingredientes locales</p>
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Procesos tradicionales</p>
            <p class="mb-1"><i class="bi bi-check-circle me-2"></i> Satisfacción garantizada</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
    <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  // Inicializar animaciones
  AOS.init({
    duration: 1000,
    once: true
  });
  
  // Pausar carrusel al pasar el mouse
  const carousel = document.getElementById('carouselYogures');
  carousel.addEventListener('mouseenter', () => {
    const carouselInstance = bootstrap.Carousel.getInstance(carousel);
    carouselInstance.pause();
  });
  
  carousel.addEventListener('mouseleave', () => {
    const carouselInstance = bootstrap.Carousel.getInstance(carousel);
    carouselInstance.cycle();
  });
</script>
</body>
</html>