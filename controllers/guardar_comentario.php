<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'];
    $nombre = $_POST['nombre'];
    $comentario = $_POST['comentario'];
    $calificacion = $_POST['calificacion'];

    $consulta = "INSERT INTO comentarios (producto_id, nombre, comentario, calificacion, fecha) VALUES ('$producto_id', '$nombre', '$comentario', '$calificacion', NOW())";

    $mysql->efectuarConsulta($consulta);
    
    $mysql->desconectar();

    header("Location: ../views/productos_usuario.php"); // Redirige
    exit;
}
?>