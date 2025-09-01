<?php
// Incluir las clases necesarias
require_once 'Correo1.php';

function mostrarMensaje($mensaje, $tipo = "info") {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Resultado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body style="background-color: #fff0f5;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow p-4 rounded-4 border-0 text-center" style="background-color: #ffffff;">
                        <div class="mb-4">
                            <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70">
                        </div>
                        <div class="alert alert-' . $tipo . ' fw-semibold">
                            ' . $mensaje . '
                        </div>
                        <a href="../index.php" class="btn btn-pink mt-3"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

// Verificamos si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoDestino = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    if (filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
        $correo = new Correo();
        $correo->recuperarContrasena($correoDestino);
        mostrarMensaje("Se ha enviado un enlace de recuperación a tu correo.", "success");
    } else {
        mostrarMensaje("La dirección de correo no es válida.", "danger");
    }
} else {
    mostrarMensaje("No se ha enviado el formulario.", "warning");
}
?>
