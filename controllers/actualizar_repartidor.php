<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $tipo_transporte = $_POST['tipo_transporte'];
    $fotoActual = $_POST['fotoActual'];

    $ruta = $fotoActual;

    if (isset($_FILES['foto_identificacion']) && $_FILES['foto_identificacion']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['foto_identificacion']['tmp_name']);

        if (!array_key_exists($tipo, $permitidos)) {
            die("Solo se permiten imágenes JPG y PNG.");
        }

        $ext = $permitidos[$tipo];
        $nombreUnico = 'foto_' . date('Ymd_Hisv') . $ext;
        $ruta = 'assets/imagenes/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta;

        if (!move_uploaded_file($_FILES['foto_identificacion']['tmp_name'], $rutaAbsoluta)) {
            die("Error al subir la imagen.");
        }
    }

    if (!empty($nombre) && !empty($correo) && !empty($telefono) && !empty($tipo_transporte)) {
        $consulta = "UPDATE repartidores SET 
                        nombre = '$nombre', 
                        correo = '$correo', 
                        telefono = '$telefono', 
                        tipo_transporte = '$tipo_transporte', 
                        foto_identificacion = '$ruta' 
                     WHERE id = $id";

        $mysql->efectuarConsulta($consulta);
        $mysql->desconectar();

        header("Location: ../views/gestionar_repartidores.php?estado=exito");
        exit();
    } else {
        header("Location: ../views/gestionar_repartidores.php?estado=error");
        exit();
    }
}
?>
