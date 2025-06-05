<?php
require_once '../models/MySQL.php';

session_start();
if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
}

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { 
    header("Location: ../views/index.php");
    exit();
}

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
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <style>
      /* SIDEBAR BASE */
      .sidebar {
          background-color: #ffe6f0; /* Fondo rosa claro */
          border-right: 1px solid #f8c8dc; /* Borde más suave rosado */
          min-width: 220px;
          transition: all 0.3s ease;
          padding: 1rem 0.5rem;
      }

      /* LOGO */
      .sidebar .navbar-brand {
          display: flex;
          align-items: center;
          justify-content: center;
          padding-bottom: 1rem;
          font-weight: bold;
          color: #d63384;
      }

      /* NAV LINKS */
      .sidebar .nav-link {
          display: flex;
          align-items: center;
          padding: 0.75rem 1rem;
          color: #444;
          font-weight: 500;
          border-radius: 0.375rem;
          transition: background 0.2s ease;
      }

      .sidebar .nav-link i {
          margin-right: 0.75rem;
          color: #d63384;
      }

      .sidebar .nav-link span {
          white-space: nowrap;
      }

      .sidebar .nav-link:hover,
      .sidebar .nav-link:focus {
          background-color: #fddbe9; /* Hover rosado suave */
          color: #d63384;
      }

      /* TOGGLE BUTTON */
      .toggle-btn {
          border: none;
          background: none;
          font-size: 1.25rem;
          color: #d63384;
      }

      /* COLLAPSED SIDEBAR */
      .sidebar.collapsed {
          min-width: 60px !important;
          overflow: hidden;
          background-color: #ffe6f0; /* Mantener fondo cuando colapsa */
      }

      .sidebar.collapsed .nav-link span,
      .sidebar.collapsed .navbar-brand span {
          display: none;
      }

      .sidebar.collapsed .nav-link {
          text-align: center;
      }

      .sidebar.collapsed .navbar-brand {
          padding: 0.5rem 0;
      }

      .sidebar.collapsed .bi {
          margin-right: 0;
          font-size: 1.25rem;
      }

      /* MOBILE SIDEBAR */
      @media (max-width: 991.98px) {
          .sidebar {
              position: fixed;
              top: 0;
              left: -250px;
              height: 100vh;
              width: 220px;
              z-index: 1050;
              background-color: #ffe6f0; /* Fondo rosa también en móvil */
              box-shadow: 0 0 10px rgba(0,0,0,0.1);
              transition: left 0.3s ease-in-out;
          }

          .sidebar.show {
              left: 0;
          }
      }
      
      /* Form container styles */
      .form-container {
          max-width: 100%;
          margin: auto;
          padding: 30px;
          background-color: white;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.1);
      }
    </style>
</head>
<body>

<!-- Botón para abrir sidebar en móvil -->
<button class="btn btn-outline-secondary d-lg-none m-3" id="mobileSidebarToggle">
    <i class="bi bi-list"></i>
</button>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 220px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            <button class="toggle-btn d-none d-lg-inline" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/creacion.php"><i class="bi bi-plus-circle"></i><span> CREAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/registrar.php"><i class="bi bi-person-plus"></i><span> REGISTRAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
            <?php } ?>

        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0" >Creacion de productos y articulos</h1>
            <?php if (isset($_SESSION['correo'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <?php if ($_SESSION['tipo'] === 'user') { ?>
                            <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php } ?>
                        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
        </div>

        <!-- Seleccion De Creacion -->
        <div class="text-center my-5">
            <div class="btn-group mt-3" role="group" aria-label="Selector de formulario">
                <button type="button" class="btn btn-outline-primary px-4" onclick="mostrarFormulario('producto')">Crear Producto</button>
                <button type="button" class="btn btn-outline-secondary px-4" onclick="mostrarFormulario('articulo')">Crear Artículo</button>
            </div>
        </div>

        <!-- Formulario de PRODUCTO -->
        <div id="form-producto" style="display: block; max-width: 100%;">
            <div class="container my-5">
                <h2 class="mb-4 text-center">Crear nuevo producto</h2>
                <form action="../controllers/crear_productos.php" method="POST" enctype="multipart/form-data" class="form-container">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del producto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio ($)</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock disponible</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="1" required>
                    </div>
                    
                    <!--ingredientes-->
                    <div class="mb-3">
                        <label for="ingredientes" class="form-label">Ingredientes</label>
                        <div id="nuevos-ingredientes">
                            <div class="input-group mb-2 w-50">
                                <input type="text" name="ingredientes[]" class="form-control" pattern="^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$" placeholder="Escribe un ingrediente">
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" onclick="agregarCampo()">
                            <i class="bi bi-plus-circle"></i> Agregar otro ingrediente
                        </button>
                    </div>
                    <!--fin ingredientes-->
                    
                    <div class="mb-4">
                        <label for="imagen" class="form-label">Imagen del producto</label>
                        <input class="form-control" type="file" id="imagen" name="imagen" accept=".jpg, .jpeg, .png" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-success px-5">Crear producto</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulario de ARTÍCULO -->
        <div id="form-articulo" style="display: none;">
            <div class="container my-5">
                <h2 class="mb-4 text-center">Crear nuevo artículo</h2>
                <form action="../controllers/crear_articulos.php" method="POST" enctype="multipart/form-data" class="form-container">
                    
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título del artículo</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" required>
                    </div>

                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido</label>
                        <textarea class="form-control" id="contenido" name="contenido" title="Solo letras y espacios" pattern="^[0-9a-zA-ZÁÉÍÓÚáéíóúÑñ\s.:]+$" rows="6" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="imagen" class="form-label">Imagen de portada</label>
                        <input class="form-control" type="file" id="imagen" name="imagen" accept=".jpg, .jpeg, .png" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-success px-5">Crear artículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= ($_GET['tipo'] === 'producto') ? 'Producto' : 'Artículo' ?> registrado con éxito',
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

        // Eliminar los parámetros de la URL sin recargar
        if (window.history.replaceState) {
            const url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: url }, "", url);
        }
    </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar functionality
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');

    // Desktop toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('bi-chevron-left');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-left');
            }
        });
    }

    // Mobile toggle
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Close on outside click
        document.addEventListener('click', function (event) {
            const isClickInside = sidebar.contains(event.target) || mobileSidebarToggle.contains(event.target);
            if (!isClickInside && window.innerWidth < 992) {
                sidebar.classList.remove('show');
            }
        });
    }
});

// Form toggle functionality
function mostrarFormulario(tipo) {
    const formProducto = document.getElementById('form-producto');
    const formArticulo = document.getElementById('form-articulo');

    if (tipo === 'producto') {
        formProducto.style.display = 'block';
        formArticulo.style.display = 'none';
    } else {
        formProducto.style.display = 'none';
        formArticulo.style.display = 'block';
    }
}

// Ingredient fields functionality
function agregarCampo() {
    const contenedor = document.getElementById('nuevos-ingredientes');
    const nuevoCampo = document.createElement('div');
    nuevoCampo.className = 'input-group mb-2 w-50';
    nuevoCampo.innerHTML = `
        <input type="text" name="ingredientes[]" class="form-control" pattern="^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$" placeholder="Escribe un ingrediente">
        <button type="button" class="btn btn-outline-danger" onclick="eliminarCampo(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    contenedor.appendChild(nuevoCampo);
}

function eliminarCampo(boton) {
    const grupo = boton.parentNode;
    grupo.parentNode.removeChild(grupo);
}
</script>
</body>
</html>