<?php
require_once '../models/MySQL.php'; // Ajusta la ruta si es necesario

session_start();

$mysql = new MySQL();
$mysql->conectar();
// Puedes realizar consultas a la base de datos si necesitas datos dinámicos para esta página
// Por ejemplo, para mostrar información sobre la historia desde una tabla.
// $historia = $mysql->efectuarConsulta("SELECT * FROM nuestra_historia_tabla LIMIT 1;");
$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuestra Historia - Flor Reina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
        body {
            background-color: #ffe4e1; /* Rosa claro de fondo */
            font-family: 'Montserrat', sans-serif; /* Usar la fuente de Montserrat */
        }
        .section-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
            margin-bottom: 30px;
        }
        h1, h2 {
            font-family: 'Playfair Display', serif; /* Fuente elegante para títulos */
            color: #d11e5a; /* Un rosa más oscuro/fuerte para los títulos principales */
            text-align: center;
            margin-bottom: 30px;
        }
        h3 {
            color: #ff69b4; /* Rosa fuerte para subtítulos */
            font-family: 'Playfair Display', serif;
        }
        p {
            color: #4a4a4a;
            line-height: 1.7;
            font-size: 1.1rem;
        }
        .img-historia {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .bg-light-pink {
            background-color: #fce4ec; /* Un rosa aún más claro para secciones */
        }
        .text-pink-strong {
            color: #d11e5a;
        }
        .icon-large {
            font-size: 3rem;
            color: #ff69b4; /* Iconos en rosa fuerte */
            margin-bottom: 15px;
        }
        .card-custom {
            border: 1px solid #ffb6c1; /* Borde rosa suave */
            border-radius: 8px;
            padding: 25px;
            height: 100%;
            background-color: #fff;
            transition: transform 0.3s ease-in-out;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .nav-link.active {
            font-weight: bold;
            color: #d11e5a !important; /* Asegura que el enlace activo sea rosa fuerte */
        }
        .navbar-custom .navbar-brand img {
            filter: hue-rotate(330deg) saturate(1.5); /* Ajustar el logo si es necesario */
        }
    </style>
</head>

<body>

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
                    <li class="nav-item"><a class="nav-link" href="admin_pedidos.php">PEDIDOS</a></li>
                    <li class="nav-item"><a class="nav-link" href="creacion.php">CREAR</a></li>
                    <li class="nav-item"><a class="nav-link" href="repartidores.php">REPARTIDORES</a></li> 
                    <li class="nav-item"><a class="nav-link" href="gestionar_repartidores.php">Gestion Repartidores</a></li>
                <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                       <li class="nav-item"><a class="nav-link" href="repartidores.php">Mis Entregas</a></li> 
                <?php } ?>
                <li class="nav-item"><a class="nav-link" href="./productos_usuario.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="./blog_usuario.php">Blog</a></li>
                 <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                    <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_pedidos.php">Mis Pedidos</a></li>
                <?php } ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['correo'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                                <li><a class="dropdown-item" href="editar_perfil.php">Editar Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php } ?>
                            <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>    
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
                <?php endif; ?>
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                    
                <?php } ?>
            </div>
        </div>
    </div>
</nav>

<header class="bg-light py-5 text-center" style="background-color: #ffebf0 !important;">
    <div class="container">
        <h1 class="display-4 fw-bold text-pink-strong" data-aos="fade-down">Nuestra Historia en Flor Reina</h1>
        <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Un viaje de pasión, tradición y sabor auténtico.</p>
    </div>
</header>

<section class="quienes-somos-section py-5 bg-white">
    <div class="container section-container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="../assets/imagenes/yogur-vayas.avif" alt="Equipo de Flor Reina" class="img-fluid rounded shadow img-historia">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="fw-bold mb-3">Los Inicios de Nuestro Sueño</h2>
                <p>La historia de <strong>Flor Reina</strong> nace de un profundo amor por la naturaleza y la gastronomía auténtica. Todo comenzó en un pequeño pueblo, donde la idea de crear yogures que no solo fueran deliciosos, sino también nutritivos y elaborados con el mayor cuidado, empezó a florecer.</p>
                <p>Nuestra fundadora, con la herencia de recetas familiares y una visión clara, decidió transformar la leche fresca de granjas locales en yogures artesanales, libres de aditivos y llenos de sabor.</p>
                <p>Desde el primer lote, la respuesta fue abrumadora. La gente apreciaba la diferencia de un producto hecho con pasión y con ingredientes de verdad. Así, lo que empezó como una pequeña iniciativa, creció hasta convertirse en la marca que hoy conoces, manteniendo siempre la esencia de su origen.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light-pink">
    <div class="container section-container">
        <h2 class="fw-bold text-center mb-5">Nuestros Valores</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="card-custom">
                    <i class="bi bi-flower1 icon-large"></i>
                    <h3>Calidad y Pureza</h3>
                    <p>Seleccionamos cuidadosamente cada ingrediente, priorizando la frescura y la pureza para garantizar un producto excepcional.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="card-custom">
                    <i class="bi bi-hand-thumbs-up icon-large"></i>
                    <h3>Artesanía y Tradición</h3>
                    <p>Mantenemos los métodos de elaboración artesanal, respetando los tiempos y procesos que nos han enseñado nuestros ancestros.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="card-custom">
                    <i class="bi bi-leaf icon-large"></i>
                    <h3>Sostenibilidad y Respeto</h3>
                    <p>Estamos comprometidos con prácticas sostenibles que cuidan nuestro planeta y apoyan a las comunidades locales.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container section-container">
        <h2 class="fw-bold text-center mb-5">Momentos Clave</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline">
                    
                     <div class="timeline-item right" data-aos="fade-left">
                        <div class="timeline-content card-custom">
                            <h3>2025 - Nacimiento</h3>
                            <p>Creamos una empresa innovadora para el sector Colombiano, en la cual mostramos al mundo el verdadero sabor del yogur tradicional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1">© 2025 Flor Reina. Todos los derechos reservados.</p>
        <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
    </div>
</footer>

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
                    <a href="pagar.php" class="btn btn-success" id="btn-pagar">
                        <i class="bi bi-credit-card"></i> Pagar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast-agregado" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Éxito</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
        <div class="toast-body">
            Producto agregado al carrito!
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Inicializar animaciones
    AOS.init({
        duration: 1000,
        once: true
    });
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
                                <img src="../${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
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
        
        // Manejar el formulario de agregar al carrito (si hubiera, aunque en esta página no hay)
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
                
                const itemExistente = carrito.find(item => item.id === id);
                
                if (itemExistente) {
                    const nuevaCantidad = itemExistente.cantidad + cantidad;
                    if (nuevaCantidad <= stock) {
                        itemExistente.cantidad = nuevaCantidad;
                    } else {
                        alert('No hay suficiente stock disponible');
                        return;
                    }
                } else {
                    carrito.push({
                        id,
                        nombre,
                        precio,
                        imagen,
                        cantidad,
                        stock
                    });
                }
                
                localStorage.setItem('carrito', JSON.stringify(carrito));
                actualizarContador();
                renderizarCarrito();
                
                const toast = new bootstrap.Toast(document.getElementById('toast-agregado'));
                toast.show();
            }
        });
        
        // Incrementar/decrementar cantidad en el modal de producto (no aplica directamente aquí, pero se mantiene la lógica)
        document.addEventListener('click', function(e) {
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