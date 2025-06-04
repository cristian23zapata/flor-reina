<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    // Eliminar el repartidor de la base de datos
    $consulta = "DELETE FROM repartidores WHERE id = $id;";
    $mysql->efectuarConsulta($consulta);
    
    // Desconectar de la base de datos
    $mysql->desconectar();
    
    header("Location: ../views/gestionar_repartidores.php?estado=eliminado");
    exit();
} else {
    echo "Método no permitido.";
}