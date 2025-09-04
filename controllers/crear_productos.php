<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $descripcion = filter_var($_POST['descripcion'], FILTER_SANITIZE_STRING);;
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    //procesar ingredientes
    $ingredientes = $_POST['ingredientes'];
    $ingredientes_filtrados = array_filter(array_map('trim', $ingredientes));
    $ingredientes_str = implode(',', $ingredientes_filtrados);
    
    if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $tipo = mime_content_type($_FILES['imagen']['tmp_name']);
    
        if (!array_key_exists($tipo, $permitidos)) {
            die("Solo se permiten imágenes JPG y PNG.");
        }
    
        // Generar nombre único y guardar la imagen
        $ext = $permitidos[$tipo];
        $nombreUnico = 'imagen_' . date('Ymd_Hisv') . $ext;
        $ruta = 'assets/imagenes/' . $nombreUnico;
        $rutaAbsoluta = __DIR__ . '/../' . $ruta;
        
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaAbsoluta)) {
            die("Error al subir la imagen.");
        }
    } else {
        die("Debe seleccionar una imagen.");
    }

    if (!empty($nombre) && !empty($descripcion) && !empty($precio) && !empty($stock)) {
        

        $consulta = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen, ingredientes) VALUES ('$nombre', '$descripcion', $precio, $stock, '$ruta', '$ingredientes_str')";

        $mysql->efectuarConsulta($consulta);
    
        $mysql->desconectar();
        
        header("Location: ../views/creacion.php?estado=exito&tipo=producto");
        exit();
    }else {
        header("Location: ../views/creacion.php?estado=error&mensaje=" . urlencode("Faltan campos requeridos"));
        exit();
    }
}