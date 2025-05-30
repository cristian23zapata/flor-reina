<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];


    $ruta = $_POST['imagenActual']; 

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

    if (!empty($titulo) || !empty($contenido)) {
        
        $consulta = "UPDATE articulos SET titulo = '$titulo', contenido = '$contenido', imagen = '$ruta' WHERE id = " . $_POST['id'] . ";";
        $mysql->efectuarConsulta($consulta);
    
        $mysql->desconectar();
        
        echo "Articulo Actualizado con exito. <br><br>";
        
        header("refresh:3;url=../views/blog.php");
    }
}