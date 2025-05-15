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
  <link rel="stylesheet" href="assets/css/estilo_nav.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/carrusel.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
  <style>
    h1 {
  font-family: 'Playfair Display', serif;
}
.lead {
  font-family: 'Montserrat', sans-serif;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.text-gradient {
  animation: fadeInUp 1s ease-out;
}
.hero-section {
  background: url('assets/imagenes/fondo-leche.jpg') center/cover no-repeat;
}
  </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="assets/imagenes/logo.png" alt="Flor Reina" height="60">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
        <li class="nav-item"><a class="nav-link active" href="views/creacion.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link active" href="views/registrar.php">REGISTRAR</a></li>
        <li class="nav-item"><a class="nav-link" href="views/repartidores.php">Repartidores</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="views/blog.php">Blog</a></li>
         <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
        <li class="nav-item"><a class="nav-link" href="views/contacto.php">Contacto</a></li>
         <?php } ?>
      </ul>

      <form class="d-flex me-3" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
          <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
          <a href="controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
        <?php else: ?>
          <a href="views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
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
    <h1 class="display-5">Productos Lácteos Artesanales</h1>
    <p class="lead">Disfruta del sabor auténtico de Asturias.</p>
  </div>
</header>

<!-- Carrusel Grande de Yogures -->
<div id="carouselYogures" class="carousel slide fullscreen-carousel" data-bs-ride="carousel">
  <div class="carousel-inner">
    
    
    <!-- Yogur 4 -->
    <div class="carousel-item active">
      <img src="assets/imagenes/yogur4.png" class="d-block w-100" alt="Yogur de Vainilla">
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

<!-- Quienes Somos? -->
<section class="quienes-somos-section py-5 bg-white">
  <div class="container">
    <div class="row align-items-center">
      <!-- Imagen -->
      <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
        <img src="assets/imagenes/quienessomos" alt="Equipo de Flor Reina" class="img-fluid rounded shadow">
      </div>
      <!-- Texto -->
      <div class="col-lg-6" data-aos="fade-left">
        <h2 class="fw-bold mb-3">¿Quiénes somos?</h2>
        <p class="fs-5">En <strong>Flor Reina</strong>, somos una familia apasionada por la tradición y el sabor auténtico. Desde el corazón de Asturias, elaboramos yogures artesanales con ingredientes naturales y procesos que respetan la herencia láctea de nuestra tierra.</p>
        <p class="fs-5">Cada cucharada de nuestros productos refleja el compromiso con la calidad, la sostenibilidad y el bienestar de nuestros clientes.</p>
        <a href="views/nuestra-historia.php" class="btn btn-outline-primary mt-3">Conoce nuestra historia</a>
      </div>
    </div>
  </div>
</section>
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


<!-- Sección de Ubicación -->
<section class="ubicacion-section py-5 bg-light">
  <div class="container">
    <h2 class="text-center fw-bold mb-5">¿Dónde estamos?</h2>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
      </div>
    </div>
  </div>
</section>

  <!-- Modal del Carrito (se abre desde la derecha) -->
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-slideout modal-lg">
    <div class="modal-content h-100 rounded-start-4">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold " id="modalCarritoLabel">
          <i class="bi bi-cart3"></i> Tu Carrito de Compras
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body overflow-auto p-3">
        <div id="carrito-vacio" class="text-center py-5">
          <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">Tu carrito está vacío</p>
        </div>
        <div id="carrito-contenido" style="display: none;">
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead>
                <tr class="border-bottom">
                  <th>Producto</th>
                  <th style="width: 140px;">Cantidad</th>
                  <th style="width: 100px;" class="text-end">Precio</th>
                  <th style="width: 100px;" class="text-end">Subtotal</th>
                  <th style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody id="carrito-items">
                <!-- Los items del carrito se generan dinámicamente aquí -->
              </tbody>
              <tfoot class="border-top">
                <tr>
                  <td colspan="3" class="text-end fw-bold">Total:</td>
                  <td class="text-end fw-bold" id="carrito-total">$0.00</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-arrow-left"></i> Seguir comprando
        </button>
        <div>
          <button type="button" class="btn btn-outline-danger me-2" id="vaciar-carrito">
            <i class="bi bi-trash"></i> Vaciar
          </button>
          <a href="../views/pagar.php" class="btn btn-success" id="btn-pagar">
            <i class="bi bi-credit-card"></i> Pagar
          </a>
        </div>
      </div>
    </div>
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
<script>
          function agregarCampo() {
          const contenedor = document.getElementById('contenedor-ingredientes');
          const columnas = contenedor.getElementsByClassName('col-md-6');
          let ultimaColumna = columnas[columnas.length - 1];
          const totalInputs = contenedor.querySelectorAll('input[name="ingredientes[]"]').length;

          if (totalInputs % 5 === 0) {
            const nuevaColumna = document.createElement('div');
            nuevaColumna.className = 'col-md-6';
            nuevaColumna.id = `columna-ingredientes-${columnas.length + 1}`;
            contenedor.appendChild(nuevaColumna);
            ultimaColumna = nuevaColumna;
          }

          const nuevoCampo = document.createElement('div');
          nuevoCampo.className = 'input-group mb-2';
          nuevoCampo.innerHTML = `
            <input type="text" name="ingredientes[]" class="form-control" placeholder="Escribe un ingrediente">
            <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
            <i class="bi bi-trash"></i>
            </button>
          `;
          ultimaColumna.appendChild(nuevoCampo);
          }

          function eliminarCampo(boton) {
          const campo = boton.parentElement;
          const columna = campo.parentElement;
          columna.removeChild(campo);

          // Reorganizar columnas si están vacías
          const contenedor = document.getElementById('contenedor-ingredientes');
          const columnas = Array.from(contenedor.getElementsByClassName('col-md-6'));
          columnas.forEach(col => {
            if (col.children.length === 0) {
            contenedor.removeChild(col);
            }
          });
          }
        </script>
        <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Actualizar contador del carrito
    function actualizarContador() {
      const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
      const contador = document.getElementById('carrito-contador');
      
      if (totalItems > 0) {
        contador.textContent = totalItems;
        contador.style.display = 'block';
      } else {
        contador.style.display = 'none';
      }
    }
    
    // Renderizar carrito en el modal
    function renderizarCarrito() {
      const carritoItems = document.getElementById('carrito-items');
      const carritoVacio = document.getElementById('carrito-vacio');
      const carritoContenido = document.getElementById('carrito-contenido');
      const carritoTotal = document.getElementById('carrito-total');
      
      if (carrito.length === 0) {
        carritoVacio.style.display = 'block';
        carritoContenido.style.display = 'none';
        document.getElementById('btn-pagar').style.display = 'none';
        document.getElementById('vaciar-carrito').style.display = 'none';
      } else {
        carritoVacio.style.display = 'none';
        carritoContenido.style.display = 'block';
        document.getElementById('btn-pagar').style.display = 'inline-block';
        document.getElementById('vaciar-carrito').style.display = 'inline-block';
        
        carritoItems.innerHTML = '';
        let total = 0;
        
        carrito.forEach((item, index) => {
          const subtotal = item.precio * item.cantidad;
          total += subtotal;
          
          const tr = document.createElement('tr');
tr.innerHTML = `
  <td>
    <div class="d-flex align-items-center">
      <img src="${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
      <span class="text-truncate" style="max-width: 150px;">${item.nombre}</span>
    </div>
  </td>
  <td>
    <div class="input-group" style="min-width: 140px;">
      <button class="btn btn-outline-secondary decrementar-cantidad py-1" type="button" data-index="${index}">-</button>
      <input type="number" class="form-control text-center py-1" value="${item.cantidad}" min="1" max="${item.stock}" data-index="${index}">
      <button class="btn btn-outline-secondary incrementar-cantidad py-1" type="button" data-index="${index}">+</button>
    </div>
  </td>
  <td class="text-end align-middle">$${item.precio.toFixed(2)}</td>
  <td class="text-end align-middle">$${subtotal.toFixed(2)}</td>
  <td class="text-center align-middle">
    <button class="btn btn-sm btn-outline-danger p-1 eliminar-item" data-index="${index}">
      <i class="bi bi-trash"></i>
    </button>
  </td>
`;
          carritoItems.appendChild(tr);
        });
        
        carritoTotal.textContent = `$${total.toFixed(2)}`;
      }
    }
    
    // Manejar el formulario de agregar al carrito
    document.addEventListener('submit', function(e) {
      if (e.target && e.target.classList.contains('agregar-carrito-form')) {
        e.preventDefault();
        
        const form = e.target;
        const id = form.querySelector('input[name="id"]').value;
        const nombre = form.querySelector('input[name="nombre"]').value;
        const precio = parseFloat(form.querySelector('input[name="precio"]').value);
        const imagen = form.querySelector('input[name="imagen"]').value;
        const stock = parseInt(form.querySelector('input[name="stock"]').value);
        const cantidad = parseInt(form.querySelector('input[name="cantidad"]').value);
        
        // Verificar si el producto ya está en el carrito
        const itemExistente = carrito.find(item => item.id === id);
        
        if (itemExistente) {
          // Actualizar cantidad si no supera el stock
          const nuevaCantidad = itemExistente.cantidad + cantidad;
          if (nuevaCantidad <= stock) {
            itemExistente.cantidad = nuevaCantidad;
          } else {
            alert('No hay suficiente stock disponible');
            return;
          }
        } else {
          // Agregar nuevo item al carrito
          carrito.push({
            id,
            nombre,
            precio,
            imagen,
            cantidad,
            stock
          });
        }
        
        // Guardar en localStorage y actualizar UI
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContador();
        renderizarCarrito();
        
        // Mostrar notificación
        const toast = new bootstrap.Toast(document.getElementById('toast-agregado'));
        toast.show();
      }
    });
    
    // Incrementar/decrementar cantidad en el modal de producto
    document.addEventListener('click', function(e) {
      // Botones + y - en el modal de producto
      if (e.target && (e.target.id === 'incrementar' || e.target.id === 'decrementar')) {
        const input = e.target.closest('.input-group').querySelector('input');
        let value = parseInt(input.value);
        
        if (e.target.id === 'incrementar' && value < parseInt(input.max)) {
          input.value = value + 1;
        } else if (e.target.id === 'decrementar' && value > parseInt(input.min)) {
          input.value = value - 1;
        }
      }
      
      // Eliminar item del carrito
      if (e.target && (e.target.classList.contains('eliminar-item') || e.target.closest('.eliminar-item'))) {
        const button = e.target.classList.contains('eliminar-item') ? e.target : e.target.closest('.eliminar-item');
        const index = button.dataset.index;
        carrito.splice(index, 1);
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContador();
        renderizarCarrito();
      }
      
      // Vaciar carrito
      if (e.target && e.target.id === 'vaciar-carrito') {
        if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
          carrito = [];
          localStorage.setItem('carrito', JSON.stringify(carrito));
          actualizarContador();
          renderizarCarrito();
        }
      }
      
      // Incrementar cantidad en el carrito
      if (e.target && (e.target.classList.contains('incrementar-cantidad') || e.target.closest('.incrementar-cantidad'))) {
        const button = e.target.classList.contains('incrementar-cantidad') ? e.target : e.target.closest('.incrementar-cantidad');
        const index = button.dataset.index;
        const input = button.closest('.input-group').querySelector('input');
        
        if (carrito[index].cantidad < carrito[index].stock) {
          carrito[index].cantidad++;
          input.value = carrito[index].cantidad;
          localStorage.setItem('carrito', JSON.stringify(carrito));
          renderizarCarrito();
          actualizarContador();
        }
      }
      
      // Decrementar cantidad en el carrito
      if (e.target && (e.target.classList.contains('decrementar-cantidad') || e.target.closest('.decrementar-cantidad'))) {
        const button = e.target.classList.contains('decrementar-cantidad') ? e.target : e.target.closest('.decrementar-cantidad');
        const index = button.dataset.index;
        const input = button.closest('.input-group').querySelector('input');
        
        if (carrito[index].cantidad > 1) {
          carrito[index].cantidad--;
          input.value = carrito[index].cantidad;
          localStorage.setItem('carrito', JSON.stringify(carrito));
          renderizarCarrito();
          actualizarContador();
        }
      }
    });
    
    // Actualizar cantidad desde el input en el carrito
    document.addEventListener('change', function(e) {
      if (e.target && e.target.matches('.input-group input[type="number"]')) {
        const input = e.target;
        const index = input.closest('.input-group').querySelector('button').dataset.index;
        const nuevaCantidad = parseInt(input.value);
        
        if (nuevaCantidad > 0 && nuevaCantidad <= carrito[index].stock) {
          carrito[index].cantidad = nuevaCantidad;
          localStorage.setItem('carrito', JSON.stringify(carrito));
          renderizarCarrito();
          actualizarContador();
        } else {
          alert('La cantidad no puede ser mayor al stock disponible');
          input.value = carrito[index].cantidad;
        }
      }
    });
    
    // Renderizar carrito cuando se abre el modal
    document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
      renderizarCarrito();
    });
    
    // Inicializar contador al cargar la página
    actualizarContador();
  });
</script>
</body>
</html>