<?php
require_once '../models/MySQL.php';
session_start();
$mysql = new MySQL();
$mysql->conectar();
// Construcción de consulta dinámica con filtros
$consulta = "SELECT * FROM productos WHERE 1";
// Filtrar por ingredientes seleccionados
if (isset($_GET['ingredientes']) && is_array($_GET['ingredientes'])) {
foreach ($_GET['ingredientes'] as $ing) {
$ing = addslashes($ing);
// Escapar si no tienes un método como real_escape_string
$consulta .= " AND ingredientes LIKE '%$ing%'";
}
}
// Ejecutar consulta con filtros (o sin ellos si no hay GET)
$resultado = $mysql->efectuarConsulta($consulta);

// Obtener ingredientes únicos para el filtro (tu lógica original)
$query = "SELECT ingredientes FROM productos";
$result = $mysql->efectuarConsulta("SELECT ingredientes FROM productos");
$ingredientes_unicos = [];
while ($row = mysqli_fetch_assoc($result)) {
$ingredientes = explode(',', $row['ingredientes']);
foreach ($ingredientes as $ing) {
$ing = trim($ing);
if (!in_array($ing, $ingredientes_unicos)) {
$ingredientes_unicos[] = $ing;
}
}
}
sort($ingredientes_unicos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Flor Reina</title>
<link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/estilo_nav.css">
<link rel="stylesheet" href="../assets/css/estilo_productos.css">
<link rel="stylesheet" href="../assets/css/estilo_creacion.css">
<style>
    /* Colores base como en tu CSS personalizado anterior, para que los nuevos elementos se integren */
    :root {
        --primary-pink: #d1567b;
        --darker-pink: #ac4563;
        --light-pink: #f8c4d3;
        --lighter-pink: #f4e6eb;
        --text-color: #5a5a5a;
        --light-bg: #f8f9fa; /* Color de fondo general */
    }

    /* Estilo del contenedor del filtro */
    .filter-sidebar {
        background-color: #ffffff; /* Fondo blanco para el filtro */
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Sombra suave */
        padding: 20px;
        margin-bottom: 20px; /* Espacio debajo del filtro en pantallas pequeñas */
    }

    /* Ajustes para el acordeón del filtro */
    .filter-sidebar .accordion-button {
        font-size: 1.1em;
        color: var(--primary-pink); /* Color del texto del botón del acordeón */
        background-color: var(--light-bg); /* Fondo del botón del acordeón */
        border-radius: 5px;
        padding: 10px 15px;
        margin-bottom: 10px;
    }
    /* Estilo cuando el acordeón está expandido */
    .filter-sidebar .accordion-button:not(.collapsed) {
        background-color: var(--lighter-pink); /* Fondo más claro cuando está abierto */
        color: var(--darker-pink); /* Color de texto más oscuro cuando está abierto */
    }
    .filter-sidebar .accordion-body {
        padding-top: 15px;
        padding-bottom: 0;
    }
    .filter-sidebar .form-check-label {
        font-size: 0.95em;
        color: var(--text-color);
    }

    /* Estilo para los botones dentro del filtro */
    .filter-sidebar .btn-primary {
        background-color: var(--primary-pink);
        border-color: var(--primary-pink);
    }
    .filter-sidebar .btn-primary:hover {
        background-color: var(--darker-pink);
        border-color: var(--darker-pink);
    }
    .filter-sidebar .btn-outline-secondary {
        border-color: #6c757d; /* Color gris de Bootstrap */
        color: #6c757d;
    }
    .filter-sidebar .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }

    /* Media queries para que el filtro sea sticky en pantallas grandes */
    @media (min-width: 768px) {
        .filter-sidebar {
            position: sticky; /* Hace que el filtro se quede fijo al hacer scroll */
            top: 20px; /* Distancia desde la parte superior de la ventana */
            align-self: flex-start; /* Ayuda al sticky en contenedores flex */
            max-height: calc(100vh - 40px); /* Para que no ocupe más de la altura de la ventana */
            overflow-y: auto; /* Permite scroll si el contenido del filtro es muy largo */
        }
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
    <li class="nav-item"><a class="nav-link active" href="../views/admin_pedidos.php">PEDIDOS</a></li>
<li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
<li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
<li class="nav-item"><a class="nav-link" href="../views/repartidores.php">REPARTIDORES</a></li>

                        <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
<?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                         <li class="nav-item"><a class="nav-link active" href="../views/repartidores.php">Mis Entregas</a></li> <?php } ?>



<li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
<li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
<?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
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
<?php endif;
?>
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
<header class="bg-light py-5 text-center">
<div class="container">
<h1 class="display-5">Productos Lácteos Artesanales</h1>
<p class="lead">Disfruta del sabor auténtico de Asturias.</p>
</div>
</header>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <div class="filter-sidebar"> <div class="accordion" id="accordionFiltros">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingFiltros">
                            <button class="accordion-button fw-bold text-uppercase"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseFiltros"
                                aria-expanded="true" aria-controls="collapseFiltros">
                                Filtrar por ingredientes
                            </button>
                        </h2>
                        <div id="collapseFiltros" class="accordion-collapse collapse show" aria-labelledby="headingFiltros"
                            data-bs-parent="#accordionFiltros">
                            <div class="accordion-body p-0 pt-3">
                                <form method="GET" action="">
                                    <div class="d-flex flex-column gap-2 mb-4"> <?php
                                        // La lógica para obtener ingredientes únicos ya está al inicio del archivo
                                        foreach ($ingredientes_unicos as $ing): ?>
                                            <div class="form-check m-0"> <input class="form-check-input" type="checkbox" name="ingredientes[]"
                                                    id="ing-<?= htmlspecialchars($ing) ?>"
                                                    value="<?= htmlspecialchars($ing) ?>"
                                                    <?= (isset($_GET['ingredientes']) && in_array($ing, $_GET['ingredientes'])) ?
                                                        'checked' : '' ?>>
                                                <label class="form-check-label small" for="ing-<?= htmlspecialchars($ing) ?>">
                                                    <?= htmlspecialchars($ing) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 px-4 py-2 fw-bold">Aplicar Filtros</button>
                                    <?php if (!empty($_GET['ingredientes'])): ?>
                                        <a href="productos.php" class="btn btn-outline-secondary w-100 mt-2 py-2">Limpiar Filtros</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> <?php while ($producto = mysqli_fetch_assoc($resultado)) : ?>
                <div class="col">
                    <div class="card h-100 shadow-sm rounded-4 border-0">
                        <img src="../<?php echo $producto['imagen']; ?>"
                            class="card-img-top rounded-top-4"
                            alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                            <h10> <strong> Ingredientes: </strong></h10>
                            <div class="ingredientes-lista">
                                <ul class="mb-2 ps-3">
                                    <?php foreach (explode(',', $producto['ingredientes']) as $ingrediente): ?>
                                    <li class="text-muted small"><?php echo trim($ingrediente); ?></li>
                                    <?php endforeach;
                                    ?>
                                </ul>
                            </div>
                            <div class="mt-auto">
                                <p class="mb-1 text-success"><strong>Precio:</strong> $<?php echo htmlspecialchars($producto['precio']); ?></p>
                                <p class="mb-2 text-secondary"><strong>Stock:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalVerMas<?php echo $producto['id']; ?>">Ver Más</a>
                                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                                    <button type="button" class="btn btn-outline-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $producto['id']; ?>">Editar</button>
                                    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#confirmarEliminar<?php echo $producto['id']; ?>">Eliminar</button>
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
                <div class="modal fade" id="modalVerMas<?php echo $producto['id']; ?>" tabindex="-1" aria-labelledby="modalVerMasLabel<?php echo $producto['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content rounded-4 shadow">
                            <div class="modal-header border-0 bg-rosado text-white">
                                <h5 class="modal-title fw-bold" id="modalVerMasLabel<?php echo $producto['id']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-4">
                                    <div class="col-md-5 text-center">
                                        <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="img-fluid rounded-3" style="height: auto; max-height: 600px; object-fit: contain;">
                                    </div>
                                    <div class="col-md-7">
                                        <p class="fw-bold">Descripción:</p>
                                        <p class="text-muted mb-3"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                        <p class="fw-bold">Ingredientes:</p>
                                        <div class="ingredientes-lista mb-3">
                                            <ul class="ps-3">
                                                <?php foreach (explode(',', $producto['ingredientes']) as $ingrediente): ?>
                                                <li class="text-muted small"><?php echo trim($ingrediente); ?></li>
                                                <?php endforeach;
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <p class="fw-bold mb-0">Precio:</p>
                                                <p class="h4 text-success">$<?php echo htmlspecialchars($producto['precio']); ?></p>
                                            </div>
                                            <div>
                                                <p class="fw-bold mb-0">Stock disponible:</p>
                                                <p class="text-muted"><?php echo htmlspecialchars($producto['stock']);
                                                ?></p>
                                            </div>
                                        </div>
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
                                            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                                            <button type="submit" class="btn btn-primary w-100 py-2">
                                                <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                            </button>
                                            <?php } ?>
                                            <?php if (isset($_SESSION['tipo']) == NULL) { ?>
                                            <a href="../views/login.php" class="btn btn-primary w-100 py-2">
                                                <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                            </a>
                                            <?php } ?>
                                        </form>
                                        <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#comentariosSection" aria-expanded="false" aria-controls="comentariosSection">
                                            Ver comentarios y dejar opinión
                                        </button>
                                        <div class="collapse" id="comentariosSection">
                                            <hr>
                                            <h5 class="mt-4">Opiniones de Clientes</h5>
                                            <?php $producto_id = $producto['id'];
                                            $consultaComentarios = $mysql->efectuarConsulta("SELECT * FROM comentarios WHERE producto_id = $producto_id ORDER BY fecha DESC");
                                            while ($comentario = mysqli_fetch_assoc($consultaComentarios)) :
                                            ?>
                                            <div class="mb-3">
                                                <div class="border rounded p-2 mb-2">
                                                    <strong><?php echo htmlspecialchars($comentario['nombre']);
                                                    ?>:</strong>
                                                    <span class="text-warning">
                                                        <?php echo str_repeat("★", $comentario['calificacion']) .
                                                        str_repeat("☆", 5 - $comentario['calificacion']); ?>
                                                    </span>
                                                    <p class="mb-1"><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($comentario['fecha'])); ?></small>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                                            <form action="../controllers/agregar_comentario.php" method="POST" class="mt-3">
                                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="comentario" class="form-label">Tu opinión:</label>
                                                    <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="calificacion" class="form-label">Calificación:</label>
                                                    <select class="form-select" id="calificacion" name="calificacion" required>
                                                        <option value="1">1 - Muy Mala</option>
                                                        <option value="2">2 - Mala</option>
                                                        <option value="3">3 - Regular</option>
                                                        <option value="4">4 - Buena</option>
                                                        <option value="5">5 - Excelente</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Enviar comentario</button>
                                            </form>
                                            <?php } ?>
                                            <?php if (isset($_SESSION['tipo']) == NULL) { ?>
                                            <a href="../views/login.php" class="btn btn-primary w-100 py-2">
                                                Dejar un comentario
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-success text-white">
<h5 class="modal-title" id="modalCarritoLabel">
<i class="bi bi-bag"></i> Mi Carrito
</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">
<div id="carrito-vacio" class="text-center">
<p>Tu carrito está vacío.</p>
</div>
<div id="carrito-contenido" style="display: none;">
<table class="table">
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
<th></th>
</tr>
</thead>
<tbody id="carrito-items">
</tbody>
<tfoot>
<tr>
<td colspan="3" class="text-end fw-bold">Total:</td>
<td class="text-end fw-bold" id="carrito-total"></td>
<td></td>
</tr>
</tfoot>
</table>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
<button type="button" class="btn btn-danger" id="vaciar-carrito">Vaciar Carrito</button>
<button type="button" class="btn btn-success" id="btn-pagar-modal">Pagar</button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
// Obtener elementos del DOM
const carritoContador = document.getElementById('carrito-contador');
const carritoItems = document.getElementById('carrito-items');
const carritoTotal = document.getElementById('carrito-total');
const carritoVacio = document.getElementById('carrito-vacio');
const carritoContenido = document.getElementById('carrito-contenido');
// Cargar carrito desde localStorage
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
// Función para actualizar el contador del carrito
function actualizarContador() {
let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
carritoContador.textContent = totalItems;
carritoContador.style.display = totalItems > 0 ? 'inline-block' : 'none';
}
// Función para eliminar un item del carrito
function eliminarItem(index) {
carrito.splice(index, 1);
localStorage.setItem('carrito', JSON.stringify(carrito));
renderizarCarrito();
actualizarContador();
}
// Función para vaciar el carrito
function vaciarCarrito() {
carrito = [];
localStorage.removeItem('carrito');
renderizarCarrito();
actualizarContador();
}
// Asignar evento al botón de vaciar carrito
const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
if (vaciarCarritoBtn) {
vaciarCarritoBtn.addEventListener('click', vaciarCarrito);
}
// Asignar eventos a los botones de eliminar item
document.addEventListener('click', function(e) {
if (e.target && e.target.classList.contains('eliminar-item')) {
const index = e.target.dataset.index;
eliminarItem(index);
}
});
// Función para renderizar los items del carrito en el modal
function renderizarCarrito() {
if (carrito.length === 0) {
carritoVacio.style.display = 'block';
carritoContenido.style.display = 'none';
document.getElementById('btn-pagar-modal').style.display = 'none'; // Cambiado el ID
document.getElementById('vaciar-carrito').style.display = 'none';
} else {
carritoVacio.style.display = 'none';
carritoContenido.style.display = 'block';
document.getElementById('btn-pagar-modal').style.display = 'inline-block'; // Cambiado el ID
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
const btnPagarModal = document.getElementById('btn-pagar-modal');
if (btnPagarModal) {
btnPagarModal.addEventListener('click', function(event) {
event.preventDefault(); // Prevenir la navegación directa
const carritoActual = JSON.parse(localStorage.getItem('carrito')) || [];
// Enviar el carrito por POST a un script PHP para que lo guarde en sesión
// y luego redirija a pagar.php
const form = document.createElement('form');
form.method = 'POST';
form.action = '../controllers/guardar_carrito_sesion.php'; // Nuevo script
const input = document.createElement('input');
input.type = 'hidden';
input.name = 'carrito';
input.value = JSON.stringify(carritoActual);
form.appendChild(input);
document.body.appendChild(form);
form.submit();
});
}
}
});
// Incrementar/decrementar cantidad en el modal de producto
document.addEventListener('click', function(e) {
// Botones + y - en el modal de producto
if (e.target && (e.target.id === 'incrementar' || e.target.id === 'decrementar')) {
const input = e.target.parentElement.querySelector('input[type="number"]');
let cantidad = parseInt(input.value);
const stock = parseInt(input.max);
if (e.target.id === 'incrementar') {
cantidad = Math.min(cantidad + 1, stock);
} else if (e.target.id === 'decrementar') {
cantidad = Math.max(cantidad - 1, 1);
}
input.value = cantidad;
}
// Botones + y - en el modal del carrito
if (e.target && e.target.matches('.input-group button')) {
const input = e.target.closest('.input-group').querySelector('input[type="number"]');
let cantidad = parseInt(input.value);
const index = e.target.dataset.index;
const stock = carrito[index].stock;
if (e.target.classList.contains('incrementar-cantidad')) {
cantidad = Math.min(cantidad + 1, stock);
} else if (e.target.classList.contains('decrementar-cantidad')) {
cantidad = Math.max(cantidad - 1, 1);
}
input.value = cantidad;
carrito[index].cantidad = cantidad;
localStorage.setItem('carrito', JSON.stringify(carrito));
renderizarCarrito();
actualizarContador();
}
});
// Actualizar cantidad al cambiar el valor del input en el modal del carrito
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
// NUEVO: Manejar el botón "Pagar" en el modal del carrito
document.getElementById('btn-pagar-modal').addEventListener('click', function() {
const carritoData = localStorage.getItem('carrito');
if (carritoData && JSON.parse(carritoData).length > 0) {
// Creamos un formulario dinámicamente
const form = document.createElement('form');
form.method = 'POST';
form.action = '../views/pagar.php';
const input = document.createElement('input');
input.type = 'hidden';
input.name = 'carrito';
input.value = carritoData;
form.appendChild(input);
document.body.appendChild(form);
form.submit();
} else {
alert('Tu carrito está vacío. Agrega productos antes de pagar.');
}
});
});
</script>
</body>
</html>