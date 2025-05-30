<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Flor Reina</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_login.css">
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow p-4 rounded-4 border-0" style="background-color: #fff0f5;">
        <div class="text-center mb-4">
          <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70">
          <h4 class="mt-3 text-pink"><i class="bi bi-person-circle me-1"></i> Iniciar Sesión</h4>
        </div>
        <form action="../controllers/procesar_login.php" method="POST">
          <div class="mb-3">
            <label for="correo" class="form-label fw-semibold text-secondary">Correo electrónico</label>
            <input type="email" class="form-control border-pink" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label fw-semibold text-secondary">Contraseña</label>
            <input type="password" class="form-control border-pink" id="password" name="password" placeholder="********" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-pink">Ingresar</button>
          </div>
        </form>
        <div class="text-center mt-3">
          <a href="#" class="small text-decoration-none text-muted">¿Olvidaste tu contraseña?</a><br>
          <a href="../views/enviarEmail.php" class="small text-decoration-none text-pink">Click Aquí para Recuperarla</a> <br> <br>
          <a href="../views/registro.php" class="small text-decoration-none text-pink">Registrarse</a>
        </div>
        <div class="text-center mt-4">
          <a href="../index.php" class="btn btn-link text-decoration-none text-pink"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
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

                if ($estado === 'exito') {
                    echo "
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Registro exitoso. Ahora puedes iniciar sesión.',
                            confirmButtonText: 'Aceptar'
                        });
                    ";
                } elseif ($estado === 'error') {
                    echo "
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: '$mensajeError',
                            confirmButtonText: 'Intentar de nuevo'
                        });
                    ";
                } elseif ($estado === 'invalid_credentials') {
                    echo "
                        Swal.fire({
                            icon: 'error',
                            title: 'Correo o contraseña incorrectos',
                            text: 'Por favor, verifica tus datos e intenta nuevamente.',
                            confirmButtonText: 'Aceptar'
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


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>