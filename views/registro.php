<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Flor Reina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_registro.css">
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg p-4 rounded-4 border-0" style="background-color: #fff0f5;">
        <div class="text-center mb-4">
          <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70">
          <h4 class="mt-3 text-pink"><i class="bi bi-person-plus-fill me-1"></i> Registro</h4>
        </div>
        <form action="../controllers/procesar_registro.php" method="POST">
          <div class="mb-3">
            <label for="nombre" class="form-label text-secondary fw-semibold">Nombre completo</label>
            <input type="text" class="form-control border-pink" title="Solo letras y espacios" pattern="^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
          </div>
          <div class="mb-3">
            <label for="correo" class="form-label text-secondary fw-semibold">Correo electrónico</label>
            <input type="email" class="form-control border-pink" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label text-secondary fw-semibold">Contraseña</label>
            <input type="password" class="form-control border-pink" id="password" name="password" placeholder="********" required>
          </div>
          <div class="mb-3">
            <label for="confirmar_password" class="form-label text-secondary fw-semibold">Confirmar contraseña</label>
            <input type="password" class="form-control border-pink" id="confirmar_password" name="confirmar_password" placeholder="********" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-pink">Registrarse</button>
          </div>
          <div class="text-center mt-3">
            <span class="small">¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none text-pink">Inicia sesión</a></span>
          </div>
        </form>
        <div class="text-center mt-4">
          <a href="../index.php" class="btn btn-link text-decoration-none text-pink"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['estado'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php
                $estado = $_GET['estado'];
                $mensajeError = htmlspecialchars($_GET['mensaje'] ?? "Hubo un error", ENT_QUOTES, 'UTF-8');

            if ($estado === 'correo_existente') {
                    echo "
                        Swal.fire({
                            icon: 'error',
                            title: 'Correo Existente',
                            text: 'El correo electrónico ya está registrado. Por favor, utiliza otro correo.',
                            confirmButtonText: 'Aceptar'
                        });
                    ";
                }elseif ($estado === 'error') {
                    echo "
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Contraseña no coincide. Por favor, verifica tus contraseñas.',
                            confirmButtonText: 'Intentar de nuevo'
                        });
                    ";
                }
            ?>

            // ✅ Limpia la URL
            if (window.history.replaceState) {
                const cleanUrl = window.location.origin + window.location.pathname;
                window.history.replaceState({}, '', cleanUrl);
            }
        });
    </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
