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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link rel="stylesheet" href="../assets/css/estilo_productos.css">
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
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Tienda</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
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
            <!-- Lista de ingredientes -->
            <div class="ingredientes-lista">
              <ul class="mb-2 ps-3">
                <?php foreach (explode(',', $producto['ingredientes']) as $ingrediente): ?>
                  <li class="text-muted small"><?php echo trim($ingrediente); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <!-- Fin lista de ingredientes -->
            <div class="mt-auto">
              <p class="mb-1 text-success"><strong>Precio:</strong> $<?php echo htmlspecialchars($producto['precio']); ?></p>
              <p class="mb-2 text-secondary"><strong>Stock:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
              <div class="d-flex gap-2">
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                  <a href="#" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $producto['id']; ?>">Ver Más</a>
                <?php } ?>
                <!-- Botón para abrir el modal -->
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?> 
                <button type="button" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id']; ?>">Editar</button>
                <!-- Botón que abre el modal -->
                <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $producto['id']; ?>">Eliminar</button>
                <!-- Modal de confirmación -->
                <div class="modal fade" id="confirmarEliminar<?php echo $producto['id']; ?>" tabindex="-1" aria-labelledby="confirmarEliminarLabel<?php echo $producto['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmarEliminarLabel<?php echo $producto['id']; ?>">Confirmar eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                      </div>
                      <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar el producto <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form action="../controllers/eliminar_producto.php" method="POST">
                          <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                          <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
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
      <div class="modal-header border-0 bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalVerMasLabel<?php echo $producto['id']; ?>">
          <?php echo htmlspecialchars($producto['nombre']); ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <!-- Imagen del producto -->
          <div class="col-md-5 text-center">
            <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="img-fluid rounded-3" style="max-height: 300px; object-fit: cover;">
          </div>

          <!-- Detalles del producto -->
          <div class="col-md-7">
            <p class="fw-bold">Descripción:</p>
            <p class="text-muted mb-3"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
            
            <p class="fw-bold">Ingredientes:</p>
            <div class="ingredientes-lista mb-3">
              <ul class="ps-3">
                <?php foreach (explode(',', $producto['ingredientes']) as $ingrediente): ?>
                  <li class="text-muted small"><?php echo trim($ingrediente); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <p class="fw-bold mb-0">Precio:</p>
                <p class="h4 text-success">$<?php echo htmlspecialchars($producto['precio']); ?></p>
              </div>
              <div>
                <p class="fw-bold mb-0">Stock disponible:</p>
                <p class="text-muted"><?php echo htmlspecialchars($producto['stock']); ?></p>
              </div>
            </div>

            <!-- Formulario para agregar al carrito -->
            <form class="agregar-carrito-form" data-producto-id="<?php echo $producto['id']; ?>">
              <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
              <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
              <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">
              <input type="hidden" name="imagen" value="<?php echo $producto['imagen']; ?>">
              <input type="hidden" name="stock" value="<?php echo $producto['stock']; ?>">
              
              <div class="input-group mb-3">
                <button class="btn btn-outline-secondary" type="button" id="decrementar">-</button>
                <input type="number" name="cantidad" class="form-control text-center" value="1" min="1" max="<?php echo htmlspecialchars($producto['stock']); ?>">
                <button class="btn btn-outline-secondary" type="button" id="incrementar">+</button>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-cart-plus"></i> Agregar al Carrito
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
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
              <!-- Ingredientes -->
              <div>
                <label class="form-label">Ingredientes</label>
                <div id="contenedor-ingredientes" class="row">
                  <div class="col-md-6" id="columna-ingredientes-1">
                  <?php 
                  $ingredientes = explode(',', $producto['ingredientes']);
                  foreach ($ingredientes as $index => $ingrediente): 
                    if ($index > 0 && $index % 5 === 0): ?>
                    </div><div class="col-md-6" id="columna-ingredientes-<?php echo ceil(($index + 1) / 5); ?>">
                    <?php endif; ?>
                    <div class="input-group mb-2">
                    <input type="text" name="ingredientes[]" class="form-control" value="<?php echo htmlspecialchars(trim($ingrediente)); ?>">
                    <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
                      <i class="bi bi-trash"></i>
                    </button>
                    </div>
                  <?php endforeach; ?>
                  </div>
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarCampo()">
                  <i class="bi bi-plus-circle"></i> Agregar otro ingrediente
                </button>
                </div>
                <!-- Fin ingredientes -->
                <div class="mb-3">
                <label class="form-label">Precio ($)</label>
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

        <!-- Modal del Carrito (se abre desde la derecha) -->
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-slideout">
    <div class="modal-content h-100 rounded-start-4">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold" id="modalCarritoLabel">
          <i class="bi bi-cart3"></i> Tu Carrito de Compras
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body overflow-auto">
        <div id="carrito-vacio" class="text-center py-5">
          <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">Tu carrito está vacío</p>
        </div>
        <div id="carrito-contenido" style="display: none;">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-center">Cantidad</th>
                  <th class="text-end">Precio</th>
                  <th class="text-end">Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="carrito-items">
                <!-- Los items del carrito se generan dinámicamente aquí -->
              </tbody>
              <tfoot>
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
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir comprando</button>
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

<!-- Toast de notificación -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="toast-agregado" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle-fill me-2"></i> Producto agregado al carrito
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
                <img src="../${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
                <span>${item.nombre}</span>
              </div>
            </td>
            <td class="text-center">
              <div class="input-group" style="max-width: 120px; margin: 0 auto;">
                <button class="btn btn-outline-secondary decrementar-cantidad" type="button" data-index="${index}">-</button>
                <input type="number" class="form-control text-center" value="${item.cantidad}" min="1" max="${item.stock}" data-index="${index}">
                <button class="btn btn-outline-secondary incrementar-cantidad" type="button" data-index="${index}">+</button>
              </div>
            </td>
            <td class="text-end">$${item.precio.toFixed(2)}</td>
            <td class="text-end">$${subtotal.toFixed(2)}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-danger eliminar-item" data-index="${index}">
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
