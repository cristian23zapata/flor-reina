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
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';

    if ($id_pedido > 0 && !empty($nuevo_estado)) {
        $mysql = new MySQL();
        $mysql->conectar();

        // Validar que el nuevo estado sea uno de los permitidos
        $estados_validos = ['pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado'];
        if (in_array($nuevo_estado, $estados_validos)) {
            $nuevo_estado_escapado = $mysql->escape_string($nuevo_estado);
            $query = "UPDATE pedidos SET estado = '$nuevo_estado_escapado' WHERE id = $id_pedido";

            if ($mysql->efectuarConsulta($query)) {
                // Éxito: redirigir de vuelta a la página de pedidos con un mensaje
                header('Location: ../views/admin_pedidos.php?success=estado_actualizado');
                exit();
            } else {
                // Error en la base de datos
                header('Location: ../views/admin_pedidos.php?error=db_error');
                exit();
            }
        } else {
            // Estado no válido
            header('Location: ../views/admin_pedidos.php?error=estado_invalido');
            exit();
        }
        $mysql->desconectar();
    } else {
        // Datos POST incompletos
        header('Location: ../views/admin_pedidos.php?error=datos_incompletos');
        exit();
    }
} else {
    // Acceso directo no permitido
    header('Location: ../views/admin_pedidos.php');
    exit();
}
?>