<?php
require_once '../models/MySQL.php';
session_start();

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
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
    <title>Lista de Repartidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <style>
        .img-repartidor {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .modal-content {
            border-radius: 10px;
        }
        .btn-action {
            min-width: 80px;
        }
        body {
            background-color: #fff5f7;
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
        .btn-primary {
            background-color: #ff6b9d;
            border-color: #ff6b9d;
        }
        .btn-primary:hover {
            background-color: #ff4785;
            border-color: #ff4785;
        }
        .btn-danger {
            background-color: #ff8fab;
            border-color: #ff8fab;
        }
        .btn-danger:hover {
            background-color: #ff6b8b;
            border-color: #ff6b8b;
        }
        .table th {
            background-color: #ff6b9d;
            color: white;
        }
    </style>
</head>
<body>

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
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
        <?php } ?>
      </ul>

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

<div class="container py-4">
    <h2 class="text-center mb-4" style="color: #ff6b9d;">Lista de Repartidores</h2>

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
                            <!-- Botón de eliminar con modal de confirmación -->
                            <button type="button" class="btn btn-danger btn-sm btn-action" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmarEliminar<?= $repartidor['id'] ?>">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                            
                            <!-- Modal de confirmación de eliminación -->
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

<!-- Modales de edición -->
<?php 
// Reiniciamos el puntero del resultado para volver a iterar
$resultado->data_seek(0);
while($repartidor = $resultado->fetch_assoc()): 
?>
<!-- Modal de Edición -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>