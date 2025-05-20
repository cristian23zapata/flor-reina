<?php
require_once '../controllers/contacto_correo.php';

function mostrarMensaje($mensaje, $tipo = "info") {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Resultado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="background-color: #fff0f5;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow p-4 rounded-4 border-0 text-center">
                        <div class="mb-4">
                            <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70">
                        </div>
                        <div class="alert alert-' . $tipo . '">
                            ' . $mensaje . '
                        </div>
                        <a href="../views/contacto.php" class="btn btn-primary mt-3">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        mostrarMensaje("Completa todos los campos obligatorios.", "danger");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        mostrarMensaje("Correo electrónico inválido.", "danger");
        exit();
    }

    // Enviar correo
    $correo = new Correo();
    if ($correo->enviarContacto($nombre, $email, $telefono, $mensaje)) {
        header("Location: ../views/contacto.php?success=true");
    } else {
        header("Location: ../views/contacto.php?error=envio_fallido");
    }
} else {
    mostrarMensaje("Acceso no permitido.", "warning");
}
?>