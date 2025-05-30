<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    // Eliminar el producto de la base de datos
    $consulta = "DELETE FROM productos WHERE id = $id;";
    $mysql->efectuarConsulta($consulta);
    
    // Desconectar de la base de datos
    $mysql->desconectar();
    
    echo "Producto eliminado con éxito. <br><br>";
    
    header("refresh:3;url=../views/productos.php");
} else {
    echo "Método no permitido.";
}