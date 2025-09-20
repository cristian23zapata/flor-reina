<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $tipo_transporte = $_POST['transporte'];

    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['foto']['tmp_name']);
    
        if (!array_key_exists($tipo, $permitidos)) {
            die("Solo se permiten imágenes JPG y PNG.");
        }
    
        // Generar nombre único y guardar la foto
        $ext = $permitidos[$tipo];
        $nombreUnico = 'foto_' . date('Ymd_Hisv') . $ext;
        $ruta = 'assets/imagen_repartidor/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta;
        
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaAbsoluta)) {
            die("Error al subir la foto.");
        }
    } else {
        die("Debe seleccionar una foto.");
    }

    if ($password !== $confirmar_password) {
        header("refresh:3;url=../views/gestionar_repartidores.php?estado=error");
        exit();
    }

    // Verificar si el correo ya está registrado
    $resultado = $mysql->efectuarConsulta("SELECT * FROM repartidores WHERE correo = '$correo'");
    if ($resultado->num_rows > 0) {
        header("refresh:3;url=../views/gestionar_repartidores.php?estado=error");
        exit();
    }

    // Insertar el nuevo usuario en la base de datos
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $mysql->efectuarConsulta("INSERT INTO repartidores (nombre, correo, telefono, password, tipo_transporte, foto_identificacion) VALUES ('$nombre', '$correo', '$telefono', '$hashed_password', '$tipo_transporte', '$ruta')");

    header("refresh:3;url=../views/gestionar_repartidores.php?estado=exito");
}