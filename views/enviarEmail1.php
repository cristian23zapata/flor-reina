<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recuperar Contraseña - Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/estilo_login.css" />
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow p-4 rounded-4 border-0" style="background-color: #fff0f5;">
        <div class="text-center mb-4">
          <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70" />
          <h4 class="mt-3 text-pink"><i class="bi bi-envelope-arrow-up me-1"></i> Recuperar Contraseña</h4>
        </div>
        <form action="../controllers/enviarCorreo1.php" method="POST">
          <div class="mb-3">
            <label for="correo" class="form-label fw-semibold text-secondary">Correo electrónico</label>
            <input type="email" class="form-control border-pink" id="correo" name="correo" placeholder="nombre@ejemplo.com" required />
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-pink">Enviar</button>
          </div>
        </form>
        <div class="text-center mt-4">
          <a href="../views/index.php" class="btn btn-link text-decoration-none text-pink"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
