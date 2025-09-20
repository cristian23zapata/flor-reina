<?php
// views/insumos.php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../models/MySQL.php';
$mysql = new MySQL();
$mysql->conectar();

// Procesar mensajes de operación
$alert = '';
if (isset($_GET['estado'])) {
    if ($_GET['estado'] === 'exito') {
        $alert = '<div class="alert alert-success">¡Operación realizada con éxito!</div>';
    } elseif ($_GET['estado'] === 'error') {
        $msg = htmlspecialchars($_GET['mensaje'] ?? 'Hubo un error');
        $alert = "<div class=\"alert alert-danger\">$msg</div>";
    }
}

// Obtener todos los insumos
$query = "SELECT id, nombre, descripcion, ingredientes, precio, stock, imagen, estado 
            FROM productos
           ORDER BY nombre";
$res = $mysql->efectuarConsulta($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Insumos - Flor Reina</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    

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
            color: #d63384; /* Color del texto del botón del acordeón */
            background-color: #f8f9fa; /* Fondo del botón del acordeón */
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        /* Estilo cuando el acordeón está expandido */
        .filter-sidebar .accordion-button:not(.collapsed) {
            background-color: #f4e6eb; /* Fondo más claro cuando está abierto */
            color: #ac4563; /* Color de texto más oscuro cuando está abierto */
        }
        .filter-sidebar .accordion-body {
            padding-top: 15px;
            padding-bottom: 0;
        }
        .filter-sidebar .form-check-label {
            font-size: 0.95em;
            color: #5a5a5a;
        }

        /* Estilo para los botones dentro del filtro */
        .filter-sidebar .btn-primary {
            background-color: #d1567b;
            border-color: #d1567b;
        }
        .filter-sidebar .btn-primary:hover {
            background-color: #ac4563;
            border-color: #ac4563;
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
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> PRODUCTOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>
  <div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-box-seam"></i> Gestión de Insumos</h1>
    <?= $alert ?>

    <table class="table table-striped table-hover bg-white shadow-sm rounded">
      <thead class="table-dark">
        <tr>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Ingredientes</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Estado</th>
          <th class="text-center">Acción</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <tr>
          <!-- Columna de Imagen -->
          <td>
            <?php if (!empty($row['imagen'])): ?>
              <img src="../<?= htmlspecialchars($row['imagen']) ?>"
                   alt="<?= htmlspecialchars($row['nombre']) ?>"
                   style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>

          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= htmlspecialchars($row['descripcion']) ?></td>
          <td><?= htmlspecialchars($row['ingredientes']) ?></td>
          <td>$<?= number_format($row['precio'], 0, ',', '.') ?></td>
          <td><?= (int)$row['stock'] ?></td>
          <td>
            <?php if ($row['estado'] === 'activo'): ?>
              <span class="badge bg-success">Activo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <form action="../controllers/toggle_insumo.php" method="post" class="d-inline">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <?php if ($row['estado'] === 'activo'): ?>
                <button type="submit" name="accion" value="deshabilitar" 
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('¿Deshabilitar este insumo?');">
                  <i class="bi bi-x-circle"></i>
                </button>
              <?php else: ?>
                <button type="submit" name="accion" value="habilitar" 
                        class="btn btn-sm btn-outline-success"
                        onclick="return confirm('¿Habilitar este insumo?');">
                  <i class="bi bi-check-circle"></i>
                </button>
              <?php endif; ?>
            </form>
          </td>
        </tr>
      <?php endwhile;
      mysqli_free_result($res);
      ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
