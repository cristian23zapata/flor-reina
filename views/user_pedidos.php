<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o no es un usuario
if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Obtener el ID del usuario desde la base de datos usando el correo en la sesión
$correo_usuario_sesion = $mysql->escape_string($_SESSION['correo']);
$query_id_usuario = "SELECT id_Usuarios FROM usuarios WHERE correo = '$correo_usuario_sesion'";
$resultado_id_usuario = $mysql->efectuarConsulta($query_id_usuario);

if (mysqli_num_rows($resultado_id_usuario) > 0) {
    $row_id_usuario = mysqli_fetch_assoc($resultado_id_usuario);
    $id_usuario = $row_id_usuario['id_Usuarios']; // Obtener el ID real para la consulta de pedidos
} else {
    // Si por alguna razón no se encuentra el ID del usuario con el correo de la sesión,
    // es un error crítico. Deberíamos cerrar la sesión y redirigir.
    session_destroy();
    header('Location: ../views/login.php?error=sesion_invalida_pedidos');
    exit();
}

// Consulta para obtener los pedidos del usuario logueado
$consulta_pedidos = "SELECT p.*, u.nombre AS nombre_usuario
                     FROM pedidos p
                     JOIN usuarios u ON p.id_usuario = u.id_Usuarios
                     WHERE p.id_usuario = " . $id_usuario . "
                     ORDER BY p.fecha_pedido DESC";

$resultado_pedidos = $mysql->efectuarConsulta($consulta_pedidos);

// ... el resto del código del archivo user_pedidos.php sigue igual ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina - Mis Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
        .estado-pendiente { background-color: #fff3cd; color: #664d03; }
        .estado-confirmado { background-color: #d1e7dd; color: #0f5132; }
        .estado-enviado { background-color: #cff4fc; color: #055160; }
        .estado-entregado { background-color: #d4edda; color: #155724; }
        .estado-cancelado { background-color: #f8d7da; color: #842029; }
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
                        <li class="nav-item"><a class="nav-link" href="../views/creacion.php">CREAR</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/registrar.php">REGISTRAR</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/admin_pedidos.php">Pedidos</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link active" href="../views/user_pedidos.php">Mis Pedidos</a></li> <?php } ?>
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

    <header class="bg-light py-4 text-center">
        <div class="container">
            <h1 class="display-6">Mis Pedidos</h1>
            <p class="lead">Revisa el estado de tus compras.</p>
        </div>
    </header>

    <main class="container py-5">
        <div class="row">
            <?php if (mysqli_num_rows($resultado_pedidos) > 0): ?>
                <?php while ($pedido = mysqli_fetch_assoc($resultado_pedidos)): ?>
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm rounded-3 border-0 <?= 'estado-' . $pedido['estado'] ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pedido #<?php echo htmlspecialchars($pedido['numero_pedido']); ?></h5>
                                <span class="badge bg-primary fs-6"><?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                                        <p><strong>Total:</strong> $<?php echo number_format($pedido['total_pedido'], 2); ?></p>
                                        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                                        <p><strong>Teléfono de Contacto:</strong> <?php echo htmlspecialchars($pedido['telefono_contacto']); ?></p>
                                        <?php if ($pedido['estado'] === 'enviado' && $pedido['id_repartidor']):
                                            // Obtener nombre del repartidor si el pedido está en estado "enviado"
                                            $consulta_repartidor = $mysql->efectuarConsulta("SELECT nombre FROM repartidores WHERE id = " . $pedido['id_repartidor']);
                                            $repartidor = mysqli_fetch_assoc($consulta_repartidor);
                                            ?>
                                            <p><strong>Enviado por:</strong> <?php echo htmlspecialchars($repartidor['nombre']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Productos:</h6>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            // Obtener detalles de los productos para este pedido
                                            $consulta_detalle = "SELECT * FROM detallepedidos WHERE id_pedido = " . $pedido['id'];
                                            $resultado_detalle = $mysql->efectuarConsulta($consulta_detalle);
                                            while ($detalle = mysqli_fetch_assoc($resultado_detalle)):
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0">
                                                    <?php echo htmlspecialchars($detalle['nombre_producto']); ?> (x<?php echo $detalle['cantidad']; ?>)
                                                    <span>$<?php echo number_format($detalle['subtotal_linea'], 2); ?></span>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <?php if ($pedido['estado'] === 'pendiente' || $pedido['estado'] === 'confirmado'): ?>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarPedidoUsuarioModal<?php echo $pedido['id']; ?>">
                                            Cancelar Pedido
                                        </button>
                                    <?php endif; ?>
                                    <div class="modal fade" id="cancelarPedidoUsuarioModal<?php echo $pedido['id']; ?>" tabindex="-1" aria-labelledby="cancelarPedidoUsuarioModalLabel<?php echo $pedido['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="cancelarPedidoUsuarioModalLabel<?php echo $pedido['id']; ?>">Cancelar Pedido #<?php echo $pedido['numero_pedido']; ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Estás seguro de que quieres **cancelar** este pedido? Esta acción es irreversible.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <form action="../controllers/cancelar_pedido_usuario.php" method="POST">
                                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">
                                                        <button type="submit" class="btn btn-danger">Cancelar Pedido</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        No has realizado ningún pedido todavía.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

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
            // Este script es el mismo que en productos.php y admin_pedidos.php
            // Idealmente, se movería a un archivo JS externo y se incluiría.
            const carritoContador = document.getElementById('carrito-contador');
            const carritoItems = document.getElementById('carrito-items');
            const carritoTotal = document.getElementById('carrito-total');
            const carritoVacio = document.getElementById('carrito-vacio');
            const carritoContenido = document.getElementById('carrito-contenido');

            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

            function actualizarContador() {
                let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
                carritoContador.textContent = totalItems;
                carritoContador.style.display = totalItems > 0 ? 'inline-block' : 'none';
            }

            function eliminarItem(index) {
                carrito.splice(index, 1);
                localStorage.setItem('carrito', JSON.stringify(carrito));
                renderizarCarrito();
                actualizarContador();
            }

            function vaciarCarrito() {
                carrito = [];
                localStorage.removeItem('carrito');
                renderizarCarrito();
                actualizarContador();
            }

            const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
            if (vaciarCarritoBtn) {
                vaciarCarritoBtn.addEventListener('click', vaciarCarrito);
            }

            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('eliminar-item')) {
                    const index = e.target.dataset.index;
                    eliminarItem(index);
                }
            });

            function renderizarCarrito() {
                if (carrito.length === 0) {
                    carritoVacio.style.display = 'block';
                    carritoContenido.style.display = 'none';
                    document.getElementById('btn-pagar-modal').style.display = 'none';
                    document.getElementById('vaciar-carrito').style.display = 'none';
                } else {
                    carritoVacio.style.display = 'none';
                    carritoContenido.style.display = 'block';
                    document.getElementById('btn-pagar-modal').style.display = 'inline-block';
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

            document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
                renderizarCarrito();
            });

            actualizarContador();

            document.getElementById('btn-pagar-modal').addEventListener('click', function() {
                const carritoData = localStorage.getItem('carrito');
                if (carritoData && JSON.parse(carritoData).length > 0) {
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