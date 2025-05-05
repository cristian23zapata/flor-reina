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
  <title>Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    /* Carrusel a pantalla completa */
    .fullscreen-carousel {
      width: 100%;
      height: 80vh;
      overflow: hidden;
      position: relative;
    }
    
    .fullscreen-carousel .carousel-inner,
    .fullscreen-carousel .carousel-item {
      height: 100%;
    }
    
    .fullscreen-carousel img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
    
    /* Capa de texto sobre imágenes */
    .carousel-caption {
      background: rgba(0, 0, 0, 0.6);
      padding: 20px;
      border-radius: 10px;
      bottom: 20%;
      left: 10%;
      right: 10%;
    }
    
    /* Sección Visión/Misión */
    .vision-mision-section {
      padding: 80px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .vision-card, .mision-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.5s ease;
      height: 100%;
    }
    
    .vision-card:hover, .mision-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .vision-card {
      background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);
      color: white;
    }
    
    .mision-card {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
    }
    
    .card-icon {
      font-size: 3rem;
      margin-bottom: 20px;
    }
    
    /* Animaciones */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .animate-fadeInUp {
      animation: fadeInUp 1s ease-out;
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
        <li class="nav-item"><a class="nav-link" href="#">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
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
<!-- commit -->

<div id="carruselFlorDeReina" class="carousel slide mt-5 shadow rounded overflow-hidden" data-bs-ride="carousel" style="max-width: 800px; margin: auto;">
  <!-- Indicadores -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carruselFlorDeReina" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carruselFlorDeReina" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carruselFlorDeReina" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>

  <!-- Slides -->
  <div class="carousel-inner" style="height: 400px;">
    <div class="carousel-item active">
      <img src="assets/imagenes/yogul1.jpg" class="d-block w-100 h-100 object-fit-cover" alt="Yogur 1">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
        <h5>Yogur Natural</h5>
        <p>Fresco, cremoso y perfecto para cualquier momento.</p>
      </div>
    </div>
    
    <!-- Yogur 2 -->
    <div class="carousel-item">
      <img src="assets/imagenes/yogul2.jpg" class="d-block w-100 h-100 object-fit-cover" alt="Yogur 2">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
        <h5>Yogur con Frutas</h5>
        <p>Endulzado naturalmente con trozos de fruta.</p>
      </div>
    </div>
    
    <!-- Yogur 3 -->
    <div class="carousel-item">
      <img src="assets/imagenes/yogul3.jpg" class="d-block w-100 h-100 object-fit-cover" alt="Yogur 3">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
        <h5>Flor de Reina</h5>
        <p>Nuestro yogur estrella con el sabor que conquista.</p>
      </div>
    </div>
  
  </div>

  <!-- Controles -->
  <button class="carousel-control-prev" type="button" data-bs-target="#carruselFlorDeReina" data-bs-slide="prev">
    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
    <span class="visually-hidden">Anterior</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carruselFlorDeReina" data-bs-slide="next">
    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
    <span class="visually-hidden">Siguiente</span>
  </button>
</div>

<!-- Sección de Visión y Misión -->
<section class="py-5" style="background-color: #ffe6f0;">
  <div class="container text-center">
    <h2 class="mb-4" style="color: #d63384;">Nuestra Esencia</h2>
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow" style="background-color: #fff0f5;">
          <div class="card-body">
            <h3 class="card-title" style="color: #d63384;">Visión</h3>
            <p class="card-text">Ser reconocidos como la marca líder en yogures artesanales, ofreciendo productos de alta calidad que deleiten a nuestros clientes y promuevan un estilo de vida saludable.</p>
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