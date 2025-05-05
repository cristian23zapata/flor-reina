<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Flor Reina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg p-4">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Flor Reina" height="70">
                <h4 class="mt-3"><i class="bi bi-person-circle me-1"></i> Iniciar Sesión</h4>
            </div>
            <form action="../controllers/procesar_login.php" method="POST">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
                <div class="text-center mt-3">
                    <a href="#" class="small text-decoration-none">¿Olvidaste tu contraseña?</a>
                    <br>
                    <a href="../views/registro.php" class="small text-decoration-none">Registrarse</a>
                </div>
                <div class="text-center mt-4">
                    <a href="../views/index.php" class="btn btn-link text-decoration-none"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>