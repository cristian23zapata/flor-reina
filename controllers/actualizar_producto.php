<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    $ruta = $_POST['imagenActual']; 
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
    }

    if (!empty($nombre) || !empty($descripcion) || !empty($precio) || !empty($stock)) {
        
        $consulta = "UPDATE productos SET nombre = '$nombre', descripcion = '$descripcion', precio = $precio, ingredientes = '$ingredientes_str', stock = $stock, imagen = '$ruta' WHERE id = " . $_POST['id'] . ";";

        $mysql->efectuarConsulta($consulta);
    
        $mysql->desconectar();
        
        header("Location: ../views/productos.php?estado=exito");
        exit();
    }
}