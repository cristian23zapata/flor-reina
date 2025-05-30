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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
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
        <li class="nav-item"><a class="nav-link active" href="../views/admin_pedidos.php">PEDIDOS</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">REPARTIDORES</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/gestionar_repartidores.php">Gestion Repartidores</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
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
      </div>
    </div>
  </div>
</nav>

<!-- Seleccion De Creacion -->
<div class="text-center my-5">
  <h3>¿Qué deseas crear?</h3>
  <div class="btn-group mt-3" role="group" aria-label="Selector de formulario">
    <button type="button" class="btn btn-outline-primary px-4" onclick="mostrarFormulario('producto')">Crear Producto</button>
    <button type="button" class="btn btn-outline-secondary px-4" onclick="mostrarFormulario('articulo')">Crear Artículo</button>
  </div>
</div>

<!-- Formulario de PRODUCTO -->
<div id="form-producto" style="display: block;">
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Producto registrado con éxito',
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

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($_GET['estado'] === 'exito'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Articulo registrado con éxito',
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

<script>
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
</script>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
        <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
    </div>
</footer>


<script>
    function agregarCampo() {
  const contenedor = document.getElementById('nuevos-ingredientes');
  const nuevoCampo = document.createElement('div');
  nuevoCampo.className = 'input-group mb-2 w-50';
  nuevoCampo.innerHTML = `
    <input type="text" name="ingredientes[]" class="form-control" placeholder="Escribe un ingrediente">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
