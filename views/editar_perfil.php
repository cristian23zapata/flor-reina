<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o no hay correo en la sesión
if (!isset($_SESSION['correo']) || !isset($_SESSION['tipo'])) {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Obtener el correo del usuario directamente de la sesión
$correo_sesion = $mysql->escape_string($_SESSION['correo']);

// Consulta para obtener los datos completos del usuario usando el correo de la sesión
// Incluimos la 'password' (que es el nombre de la columna en tu BD) para futuras validaciones de contraseña
$query_usuario = "SELECT id_Usuarios, nombre, correo, direccion, telefono, password FROM usuarios WHERE correo = '$correo_sesion'";
$resultado_usuario = $mysql->efectuarConsulta($query_usuario);

if (mysqli_num_rows($resultado_usuario) > 0) {
    $usuario = mysqli_fetch_assoc($resultado_usuario);
    // Ahora, $usuario contendrá todos los datos para rellenar el formulario.
} else {
    // Si por alguna razón no se encuentra el usuario con el correo de la sesión,
    // es un error crítico. Destruimos la sesión y redirigimos.
    session_destroy();
    header('Location: ../views/login.php?error=sesion_invalida_perfil');
    exit();
}

// Mensajes de éxito o error (si vienen de la redirección del controlador)
$mensaje_exito = '';
$mensaje_error = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'perfil_actualizado') {
        $mensaje_exito = '¡Tu perfil ha sido actualizado con éxito!';
    } elseif ($_GET['success'] === 'contrasena_actualizada') {
        $mensaje_exito = '¡Tu contraseña ha sido actualizada con éxito!';
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'db_error') {
        $mensaje_error = 'Ocurrió un error al actualizar tu perfil. Inténtalo de nuevo.';
    } elseif ($_GET['error'] === 'datos_incompletos') {
        $mensaje_error = 'Por favor, completa los campos requeridos.';
    } elseif ($_GET['error'] === 'correo_existente') {
        $mensaje_error = 'El correo electrónico que intentas usar ya está registrado por otro usuario.';
    } elseif ($_GET['error'] === 'contrasena_no_coincide') {
        $mensaje_error = 'La nueva contraseña y la confirmación no coinciden.';
    } elseif ($_GET['error'] === 'contrasena_actual_incorrecta') {
        $mensaje_error = 'La contraseña actual es incorrecta.';
    } elseif ($_GET['error'] === 'acceso_denegado') {
        $mensaje_error = 'Error de seguridad. Acceso denegado.'; // Por si acaso.
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina - Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
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
                        <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
                    <?php } ?>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['correo'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle active" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
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
            <h1 class="display-6">Editar Perfil</h1>
            <p class="lead">Actualiza tu información personal y contraseña.</p>
        </div>
    </header>

    <main class="container py-5">
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm rounded-3 p-4">
                    <h4 class="mb-4 text-center">Información Personal</h4>
                    <form action="../controllers/actualizar_perfil_usuario.php" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="actualizar_datos" class="btn btn-primary btn-lg">Actualizar Datos</button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h4 class="mb-4 text-center">Cambiar Contraseña</h4>
                    <form action="../controllers/actualizar_perfil_usuario.php" method="POST">
                        <div class="mb-3">
                            <label for="contrasena_actual" class="form-label">Contraseña Actual:</label>
                            <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                        </div>
                        <div class="mb-3">
                            <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                            <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="actualizar_contrasena" class="btn btn-warning btn-lg text-white">Cambiar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
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
            // Este script es el mismo que en productos.php, admin_pedidos.php y user_pedidos.php
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