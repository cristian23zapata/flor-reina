<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no es admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
    $id_repartidor = isset($_POST['id_repartidor']) ? intval($_POST['id_repartidor']) : 0;

    if ($id_pedido > 0 && $id_repartidor > 0) {
        $mysql = new MySQL();
        $mysql->conectar();

        // Actualizar el pedido: asigna el repartidor y cambia el estado a 'enviado'
        // Esto asume que el admin lo "envía" directamente al asignarlo.
        // Si quieres que el repartidor lo "acepte" primero, la lógica sería diferente.
        // Para este paso, lo asignamos y lo marcamos como 'enviado' por el admin.
        $query = "UPDATE pedidos SET estado = 'enviado', id_repartidor = $id_repartidor WHERE id = $id_pedido AND estado = 'confirmado'";

        if ($mysql->efectuarConsulta($query)) {
            // Éxito
            header('Location: ../views/admin_pedidos.php?success=repartidor_asignado');
            exit();
        } else {
            // Error en la base de datos
            header('Location: ../views/admin_pedidos.php?error=db_error_asignar');
            exit();
        }
        $mysql->desconectar();
    } else {
        // Datos POST incompletos
        header('Location: ../views/admin_pedidos.php?error=datos_incompletos_asignar');
        exit();
    }
} else {
    // Acceso directo no permitido
    header('Location: ../views/admin_pedidos.php');
    exit();
}
?>