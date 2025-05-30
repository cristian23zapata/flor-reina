<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o no es un usuario
if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;

    $mysql = new MySQL();
    $mysql->conectar();

    // Obtener el ID del usuario logueado desde la base de datos usando el correo en la sesión
    $correo_usuario_sesion = $mysql->escape_string($_SESSION['correo']);
    $query_id_usuario = "SELECT id_Usuarios FROM usuarios WHERE correo = '$correo_usuario_sesion'";
    $resultado_id_usuario = $mysql->efectuarConsulta($query_id_usuario);

    if (mysqli_num_rows($resultado_id_usuario) > 0) {
        $row_id_usuario = mysqli_fetch_assoc($resultado_id_usuario);
        $id_usuario_logueado = $row_id_usuario['id_Usuarios']; // Este es el ID real del usuario en la DB
    } else {
        // Error crítico: el correo de la sesión no corresponde a un usuario en la DB
        session_destroy();
        header('Location: ../views/login.php?error=sesion_invalida_cancel');
        exit();
    }

    if ($id_pedido > 0) {
        // Verificar el estado actual del pedido y que el pedido pertenezca al usuario LOGUEADO
        $query_check = "SELECT estado FROM pedidos WHERE id = $id_pedido AND id_usuario = $id_usuario_logueado";
        $resultado_check = $mysql->efectuarConsulta($query_check);

        if (mysqli_num_rows($resultado_check) > 0) {
            $pedido_existente = mysqli_fetch_assoc($resultado_check);
            $estado_actual = $pedido_existente['estado'];

            // Solo permitir cancelar si el estado es 'pendiente' o 'confirmado'
            if ($estado_actual === 'pendiente' || $estado_actual === 'confirmado') {
                $query_update = "UPDATE pedidos SET estado = 'cancelado' WHERE id = $id_pedido";

                if ($mysql->efectuarConsulta($query_update)) {
                    header('Location: ../views/user_pedidos.php?success=pedido_cancelado');
                    exit();
                } else {
                    header('Location: ../views/user_pedidos.php?error=db_error');
                    exit();
                }
            } else {
                header('Location: ../views/user_pedidos.php?error=no_se_puede_cancelar');
                exit();
            }
        } else {
            // Pedido no encontrado o no pertenece al usuario
            header('Location: ../views/user_pedidos.php?error=pedido_no_encontrado');
            exit();
        }
        $mysql->desconectar();
    } else {
        // Datos POST incompletos
        header('Location: ../views/user_pedidos.php?error=datos_incompletos');
        exit();
    }
} else {
    // Acceso directo no permitido
    header('Location: ../views/user_pedidos.php');
    exit();
}
?>