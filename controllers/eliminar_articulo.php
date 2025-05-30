<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    // Eliminar el producto de la base de datos
    $consulta = "DELETE FROM articulos WHERE id = $id;";
    $mysql->efectuarConsulta($consulta);
    
    // Desconectar de la base de datos
    $mysql->desconectar();

    header("Location: ../views/blog.php");
    exit();
} else {
    echo "Método no permitido.";
}