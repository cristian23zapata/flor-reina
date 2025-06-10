<?php
// controllers/toggle_insumo.php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../views/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['accion'])) {
    require_once '../models/MySQL.php';
    $mysql = new MySQL();
    $mysql->conectar();

    $id     = intval($_POST['id']);
    $accion = $_POST['accion'] === 'habilitar' ? 'activo' : 'inactivo';

    $sql = "UPDATE productos 
               SET estado = '$accion' 
             WHERE id = $id";
    $res = $mysql->efectuarConsulta($sql);

    if ($res) {
        header("Location: ../views/insumos.php?estado=exito");
    } else {
        $err = urlencode('No se pudo actualizar el estado.');
        header("Location: ../views/insumos.php?estado=error&mensaje=$err");
    }
    // No hay método cerrar(); se cierra al terminar el script
    exit();
} else {
    header("Location: ../views/insumos.php");
    exit();
}
