<?php
require_once '../models/MySQL.php';
$mysql = new MySQL();
$mysql->conectar();
$mysqli = $mysql->getConexion();

if ($mysqli->connect_error) {
    die('Conexión fallida: ' . $mysqli->connect_error);
}

function mostrarMensaje($mensaje, $tipo = "info") {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Recuperar contraseña</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body style="background-color: #fff0f5;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow p-4 rounded-4 border-0 text-center">
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

if (isset($_GET['codigo']) && isset($_GET['correo'])) {
    $codigo = $_GET['codigo'];
    $correo = $_GET['correo'];

    $stmt = $mysqli->prepare("SELECT * FROM recuperacion WHERE codigo = ? AND correo = ?");
    $stmt->bind_param("ss", $codigo, $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nueva_contraseña = $_POST['nueva_contraseña'];

            if (!empty($nueva_contraseña)) {
                $nueva_contraseña_hash = password_hash($nueva_contraseña, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE correo = ?");
                $stmt->bind_param("ss", $nueva_contraseña_hash, $correo);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                 // Eliminamos el código de recuperación
                 $stmt_del = $mysqli->prepare("DELETE FROM recuperacion WHERE codigo = ? AND correo = ?");
                    $stmt_del->bind_param("ss", $codigo, $correo);
                    $stmt_del->execute();
                    mostrarMensaje("Contraseña cambiada con éxito.", "success");
                } else {
                     mostrarMensaje("No se pudo actualizar la contraseña. Verifica el enlace o contacta al soporte.", "danger");
                }

                mostrarMensaje("Contraseña cambiada con éxito.", "success");
            } else {
                mostrarMensaje("Por favor, ingrese una nueva contraseña.", "danger");
            }
        } else {
            // Mostrar formulario con estilo
            echo '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Recuperar contraseña</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
                <link href="../assets/css/estilo_recuperar.css" rel="stylesheet">
            </head>
            <body style="background-color: #fff0f5;">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="card shadow p-4 rounded-4 border-0">
                                <div class="mb-4 text-center">
                                    <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="70">
                                    <h4 class="mt-3 text-pink">Cambiar contraseña</h4>
                                </div>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="nueva_contraseña" class="form-label fw-semibold">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-pink">Cambiar Contraseña</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>';
        }
    } else {
        mostrarMensaje("El código de recuperación no existe o ha expirado.", "danger");
    }
}

$mysqli->close();
?>
