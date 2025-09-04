<?php
session_start();

require_once '../models/MySQL.php';

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
}

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { 
    header("Location: ../index.php");
    exit();
}

$mysql = new MySQL;
$mysql->conectar();
$resultado = $mysql->efectuarConsulta("SELECT * FROM usuarios;");
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
    <link rel="stylesheet" href="../assets/css/estilo_registrar.css">
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
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> Estadísticas</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> Insumos</span></a></li>
            <?php } ?>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Registrar nuevo repartidor</h1>
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

        <div class="form-container" style="max-width: 100%; margin: auto; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
            <form action="../controllers/registrar_repartidor.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" name="nombre" title="Solo letras y espacios" pattern="^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" name="correo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono" title="solo numeros" pattern="^[0-9]+$" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control border-pink" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirmar_password" class="form-label">Confirmar contraseña</label>
                    <input type="password" class="form-control border-pink" id="confirmar_password" name="confirmar_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de transporte</label>
                    <select class="form-control" name="transporte" required>
                        <option value="">Selecciona una opción</option>
                        <option value="bicicleta">Bicicleta</option>
                        <option value="moto">Motocicleta</option>
                        <option value="auto">Automóvil</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto de identificación</label>
                    <input type="file" class="form-control" id="foto" name="foto" accept=".jpg, .jpeg, .png" required>
                </div>
                <button type="submit" class="btn btn-outline-primary">Registrar repartidor</button>
            </form>
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
                text: 'Repartidor registrado con éxito',
                confirmButtonText: 'Aceptar'
            });
        <?php elseif ($_GET['estado'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'contraseña no coinciden o correo ya registrado',
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
</script>
</body>
</html>
