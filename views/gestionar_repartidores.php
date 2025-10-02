<?php
require_once '../models/MySQL.php';

session_start();
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
$resultado = $mysql->efectuarConsulta("SELECT * FROM repartidores;");
$mysql->desconectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flor Reina - Gestionar Repartidores</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
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
      
      /* Estilos específicos para la gestión de repartidores */
      .img-repartidor {
          max-width: 150px;
          max-height: 150px;
          object-fit: cover;
          border-radius: 8px;
      }
      
      .btn-action {
          min-width: 80px;
      }
      
      .card-repartidor {
          border-radius: 15px;
          box-shadow: 0 4px 8px rgba(0,0,0,0.1);
          transition: transform 0.3s;
          border: none;
      }
      
      .card-repartidor:hover {
          transform: translateY(-5px);
      }
      
      .table th {
          background-color: #ff6b9d; /* Cambiado a rosa */
          color: white;
      }
      
      .modal-content {
          border-radius: 10px;
      }
      
      /* Estilos para el botón de registrar nuevo repartidor */
      .btn-registrar {
          background-color: #ff6b9d; /* Cambiado a rosa */
          border-color: #ff6b9d; /* Cambiado a rosa */
          color: white;
      }
      
      .btn-registrar:hover {
          background-color: #e55a8a; /* Rosa más oscuro */
          border-color: #e55a8a; /* Rosa más oscuro */
          color: white;
      }
      
      .border-pink {
          border-color: #ff6b9d !important; /* Rosa */
      }
      
      /* Estilos para botones primarios (cambiados a rosa) */
      .btn-primary {
          background-color: #ff6b9d; /* Rosa */
          border-color: #ff6b9d; /* Rosa */
      }
      
      .btn-primary:hover {
          background-color: #e55a8a; /* Rosa más oscuro */
          border-color: #e55a8a; /* Rosa más oscuro */
      }
      
      .btn-outline-primary {
          color: #ff6b9d; /* Rosa */
          border-color: #ff6b9d; /* Rosa */
      }
      
      .btn-outline-primary:hover {
          background-color: #ff6b9d; /* Rosa */
          border-color: #ff6b9d; /* Rosa */
          color: white;
      }
      
      /* Estilos para encabezados de modales */
      .modal-header {
          background-color: #ff6b9d; /* Rosa */
          color: white;
      }
      
      .modal-header .btn-close {
          filter: invert(1); /* Hace que la X sea blanca */
      }
       #userDropdown.btn-outline-primary {
        color: #ff6b9d;         
        border-color: #ff6b9d;
        border-radius: 25rem;
    }
    #userDropdown.btn-outline-primary:hover,
    #userDropdown.btn-outline-primary:focus {
        background-color: #ff6b9d;
        color: white;
        border-color: #ff6b9d;
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
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 300px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="bi bi-gear"></i>Gestión de Repartidores</h1>
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
                        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
            <?php endif; ?>
        </div>

        <!-- Botón para abrir el modal de registro -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-registrar" data-bs-toggle="modal" data-bs-target="#modalRegistrarRepartidor">
                <i class="bi bi-person-plus"></i> Registrar nuevo repartidor
            </button>
        </div>

        <!-- Lista de repartidores -->
        <div class="container py-4">
            <h2 class="text-center mb-4" style="color: #ff6b9d;">Lista de Repartidores Registrados</h2>

            <?php if ($resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Tipo Transporte</th>
                                <th>Foto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($repartidor = $resultado->fetch_assoc()): ?>
                            <tr class="bg-white">
                                <td><?= htmlspecialchars($repartidor['id']) ?></td>
                                <td><?= htmlspecialchars($repartidor['nombre']) ?></td>
                                <td><?= htmlspecialchars($repartidor['correo']) ?></td>
                                <td><?= htmlspecialchars($repartidor['telefono']) ?></td>
                                <td><?= htmlspecialchars($repartidor['tipo_transporte']) ?></td>
                                <td>
                                    <img src="../<?= htmlspecialchars($repartidor['foto_identificacion']) ?>" 
                                         alt="Foto repartidor" 
                                         class="img-repartidor">
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm btn-action" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarRepartidor<?= $repartidor['id'] ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-action" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmarEliminar<?= $repartidor['id'] ?>">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                    
                                    <!-- Modal de confirmación para eliminar -->
                                    <div class="modal fade" id="confirmarEliminar<?= $repartidor['id'] ?>" tabindex="-1" 
                                         aria-labelledby="confirmarEliminarLabel<?= $repartidor['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="confirmarEliminarLabel<?= $repartidor['id'] ?>">Confirmar eliminación</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Estás seguro de que deseas eliminar al repartidor <strong><?= htmlspecialchars($repartidor['nombre']) ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="../controllers/eliminar_repartidor.php" method="POST">
                                                        <input type="hidden" name="id" value="<?= $repartidor['id'] ?>">
                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    No se encontraron repartidores registrados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para registrar nuevo repartidor -->
<div class="modal fade" id="modalRegistrarRepartidor" tabindex="-1" aria-labelledby="modalRegistrarRepartidorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarRepartidorLabel">
                    <i class="bi bi-person-plus me-2"></i> Registrar nuevo repartidor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../controllers/registrar_repartidor.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre completo</label>
                                <input type="text" class="form-control" name="nombre" title="Solo letras y espacios" pattern="^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Correo electrónico</label>
                                <input type="email" class="form-control" name="correo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="number" class="form-control" name="telefono" title="solo numeros" pattern="^[0-9]+$" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Contraseña</label>
                                <input type="password" class="form-control border-pink" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_password" class="form-label fw-bold">Confirmar contraseña</label>
                                <input type="password" class="form-control border-pink" id="confirmar_password" name="confirmar_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de transporte</label>
                                <select class="form-control" name="transporte" required>
                                    <option value="">Selecciona una opción</option>
                                    <option value="bicicleta">Bicicleta</option>
                                    <option value="moto">Motocicleta</option>
                                    <option value="auto">Automóvil</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto de identificación</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept=".jpg, .jpeg, .png" required>
                                <small class="text-muted">Formatos: JPG, PNG (Máx. 2MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Registrar repartidor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Reiniciamos el puntero del resultado para volver a iterar
$resultado->data_seek(0);
while($repartidor = $resultado->fetch_assoc()): 
?>
<!-- Modal para editar repartidor -->
<div class="modal fade" id="modalEditarRepartidor<?= $repartidor['id'] ?>" tabindex="-1" 
     aria-labelledby="modalEditarRepartidorLabel<?= $repartidor['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" action="../controllers/actualizar_repartidor.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarRepartidorLabel<?= $repartidor['id'] ?>">
                    <i class="bi bi-person-badge me-2"></i> Editar Repartidor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" name="id" value="<?= $repartidor['id'] ?>">
                        <input type="hidden" name="fotoActual" value="<?= $repartidor['foto_identificacion'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($repartidor['nombre']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo electrónico</label>
                            <input type="email" name="correo" class="form-control" 
                                   value="<?= htmlspecialchars($repartidor['correo']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?= htmlspecialchars($repartidor['telefono']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Transporte</label>
                            <select name="tipo_transporte" class="form-select" required>
                                <option value="Moto" <?= ($repartidor['tipo_transporte'] == 'Moto') ? 'selected' : '' ?>>Moto</option>
                                <option value="Bicicleta" <?= ($repartidor['tipo_transporte'] == 'Bicicleta') ? 'selected' : '' ?>>Bicicleta</option>
                                <option value="Automóvil" <?= ($repartidor['tipo_transporte'] == 'Automóvil') ? 'selected' : '' ?>>Automóvil</option>
                                <option value="Caminando" <?= ($repartidor['tipo_transporte'] == 'Caminando') ? 'selected' : '' ?>>Caminando</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title fw-bold">Foto de Identificación</h6>
                                
                                <div class="mb-3">
                                    <img src="../<?= htmlspecialchars($repartidor['foto_identificacion']) ?>" 
                                         alt="Foto actual" 
                                         class="img-fluid rounded border" 
                                         style="max-height: 200px;">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Cambiar foto</label>
                                    <input type="file" name="foto_identificacion" 
                                           class="form-control" accept="image/*">
                                    <small class="text-muted">Formatos: JPG, PNG (Máx. 2MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<!-- SweetAlert2 para notificaciones -->
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
            <?php elseif ($_GET['estado'] === 'actualizado'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Repartidor actualizado con éxito',
                confirmButtonText: 'Aceptar'
            });
            <?php elseif ($_GET['estado'] === 'eliminado'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Repartidor eliminado con éxito',
                confirmButtonText: 'Aceptar'
            });
        <?php elseif ($_GET['estado'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?= htmlspecialchars($_GET["mensaje"] ?? "Hubo un error. Las contraseñas no coinciden o el correo ya está registrado") ?>',
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
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle");

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