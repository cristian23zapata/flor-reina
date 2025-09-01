<?php
require_once '../models/MySQL.php';

session_start();

$mysql = new MySQL;
$mysql->conectar();
$resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios;");

$articulos = $mysql->efectuarConsulta("SELECT * FROM articulos;");

$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Blog | Tu Página</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_carrito.css">
  <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
  
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link rel="stylesheet" href="../assets/css/estilo_blog.css">
  <link rel="stylesheet" href="../assets/css/estilo_productos.css">
  <link rel="stylesheet" href="../assets/css/new.css">
  
   
</head>
<body>
<?php include '../views/partials/carrito_modal.php'; ?>
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
          <li class="nav-item"><a class="nav-link active" href="../views/admin_pedidos.php">PEDIDOS</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">REPARTIDORES</a></li>
        
                        <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
        <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                         <li class="nav-item"><a class="nav-link active" href="../views/repartidores.php">Mis Entregas</a></li> <?php } ?>
        
        <?php if (empty($_SESSION['tipo'])) { ?>
            <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
            <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
        <?php } ?>
        
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
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
                                <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php } ?>
        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
    </ul>
</div>
        <?php else: ?>
          <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
        <?php endif; ?>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
           
        <?php } ?>
      </div>
    </div>
  </div>
  
</nav>

<div class="container py-5">
  <h1 class="mb-4 fw-bold text-center">Nuestro Blog</h1>
  <div class="row g-4">

    <?php foreach ($articulos as $articulo): ?>
  <div class="col-md-3">
    <div class="card blog-card h-100">
      <img src="../<?php echo $articulo['imagen']; ?>" alt="Imagen del artículo" class="blog-img">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title fw-bold"><?php echo $articulo['titulo']; ?></h5>
        <p class="card-text text-muted"><?php echo mb_strimwidth($articulo['contenido'], 0, 100, '...'); ?></p>
        <div class="d-flex gap-2 mt-auto">
          
            <a href="#" class="btn btn-outline-success w-100 rounded-pill"
               data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $articulo['id']; ?>">
              Ver Más
            </a>


          <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
            <button type="button" class="btn btn-outline-success w-100 rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $articulo['id']; ?>">
              Editar
            </button>
            <button type="button" class="btn btn-outline-danger rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $articulo['id']; ?>">
              Eliminar
            </button>

            <!-- Modal Confirmar Eliminación -->
            <div class="modal fade" id="confirmarEliminar<?php echo $articulo['id']; ?>" tabindex="-1"
                 aria-labelledby="confirmarEliminarLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmarEliminarLabel<?php echo $articulo['id']; ?>">
                      Confirmar eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar el artículo <strong><?php echo htmlspecialchars($articulo['titulo']); ?></strong>?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="../controllers/eliminar_articulo.php" method="POST">
                      <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
                      <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Ver Más -->
  <div class="modal fade" id="modalVerMas<?php echo $articulo['id']; ?>" tabindex="-1"
       aria-labelledby="modalVerMasLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-rodado text-white border-0">
          <h5 class="modal-title" id="modalVerMasLabel<?php echo $articulo['id']; ?>">
            <?php echo htmlspecialchars($articulo['titulo']); ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <?php if (!empty($articulo['imagen'])): ?>
            <img src="../<?php echo $articulo['imagen']; ?>" class="img-fluid mb-4 rounded-3"
                 alt="Imagen del artículo">
          <?php endif; ?>
          <p><?php echo nl2br(htmlspecialchars($articulo['contenido'])); ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de edición de artículo -->
<div class="modal fade" id="modalEditar<?php echo $articulo['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $articulo['id']; ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content form-container" action="../controllers/actualizar_articulo.php" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarLabel<?php echo $articulo['id']; ?>">Editar Artículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" value="<?php echo $articulo['id']; ?>">
        <input type="hidden" name="imagenActual" value="<?php echo $articulo['imagen']; ?>">

        <div class="mb-3">
          <label class="form-label">Título del artículo</label>
          <input type="text" name="titulo" class="form-control" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Contenido</label>
          <textarea name="contenido" rows="6" class="form-control" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" required><?php echo htmlspecialchars($articulo['contenido']); ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Imagen de portada (opcional)</label>
          <input type="file" name="imagen" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<?php endforeach; ?>

  </div>
</div>

 <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Artículo actualizado correctamente.',
                confirmButtonText: 'Aceptar'
            });
        <?php elseif ($_GET['estado'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= htmlspecialchars($_GET["mensaje"] ?? "Hubo un error") ?>',
                confirmButtonText: 'Intentar de nuevo'
            });
        <?php endif; ?>

        // ✅ Eliminar los parámetros de la URL sin recargar
        if (window.history.replaceState) {
            const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: url }, "", url);
        }
    </script>
<?php endif; ?>

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

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
      <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
      <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>
