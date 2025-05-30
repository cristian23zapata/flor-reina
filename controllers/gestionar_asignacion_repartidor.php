<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o el usuario no es 'repartidor' ni 'admin'
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'repartidor' && $_SESSION['tipo'] !== 'admin')) {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Obtener el ID de la entidad logueada para verificar permisos
// id_entidad_logueada será el id_Usuarios para admin y el id de la tabla repartidores para repartidor
$id_entidad_logueada = null;
if ($_SESSION['tipo'] === 'admin') {
    $id_entidad_logueada = $_SESSION['id_usuario'] ?? null; // Usa id_usuario para admin
} elseif ($_SESSION['tipo'] === 'repartidor') {
    $id_entidad_logueada = $_SESSION['id_repartidor'] ?? null; // Usa id_repartidor para repartidor
}

if (!$id_entidad_logueada) { // Si no se pudo determinar el ID de la entidad logueada
    session_destroy();
    header('Location: ../views/login.php?error=sesion_corrupta_gestion');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;

    if ($id_pedido <= 0) {
        header('Location: ../views/repartidores.php?error=no_data');
        exit();
    }

    // --- Lógica para ASIGNAR un pedido (Solo para Admin) ---
    if (isset($_POST['asignar_pedido'])) {
        if ($_SESSION['tipo'] !== 'admin') {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        $id_repartidor_a_asignar = isset($_POST['id_repartidor']) ? intval($_POST['id_repartidor']) : 0;

        if ($id_repartidor_a_asignar <= 0) {
            header('Location: ../views/repartidores.php?error=repartidor_no_valido');
            exit();
        }

        // Verificar que el ID del repartidor a asignar realmente existe y es de la tabla 'repartidores'
        $query_check_repartidor = "SELECT id FROM repartidores WHERE id = $id_repartidor_a_asignar";
        $result_check_repartidor = $mysql->efectuarConsulta($query_check_repartidor);
        if (mysqli_num_rows($result_check_repartidor) === 0) {
            header('Location: ../views/repartidores.php?error=repartidor_no_valido');
            exit();
        }

        // Asignar el repartidor y cambiar el estado a 'asignado'
        $query = "UPDATE pedidos SET id_repartidor = $id_repartidor_a_asignar, estado = 'asignado' WHERE id = $id_pedido AND id_repartidor IS NULL";
        if ($mysql->efectuarConsulta($query)) {
            if ($mysql->obtenerNumeroFilasAfectadas() > 0) {
                header('Location: ../views/repartidores.php?success=pedido_asignado');
            } else {
                header('Location: ../views/repartidores.php?error=pedido_ya_asignado');
            }
            exit();
        } else {
            header('Location: ../views/repartidores.php?error=db_error');
            exit();
        }
    }

    // --- Lógica para DESASIGNAR un pedido ---
    // Un repartidor se desasigna a sí mismo, un admin desasigna cualquier pedido
    if (isset($_POST['desasignar_pedido']) || isset($_POST['desasignar_pedido_admin'])) {
        $query_check_owner = "";

        if ($_SESSION['tipo'] === 'repartidor') {
            // Un repartidor solo puede desasignarse a sí mismo
            $query_check_owner = "SELECT id FROM pedidos WHERE id = $id_pedido AND id_repartidor = $id_entidad_logueada";
        } elseif ($_SESSION['tipo'] === 'admin') {
            // Un admin puede desasignar cualquier pedido asignado
            $query_check_owner = "SELECT id FROM pedidos WHERE id = $id_pedido AND id_repartidor IS NOT NULL";
        } else {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        $result_check_owner = $mysql->efectuarConsulta($query_check_owner);
        if (mysqli_num_rows($result_check_owner) === 0) {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        // Desasignar el repartidor y cambiar el estado a 'confirmado' o 'preparacion' (decide cuál es el estado por defecto para pedidos listos para asignación)
        // Usaré 'confirmado' como estado base
        $query = "UPDATE pedidos SET id_repartidor = NULL, estado = 'confirmado' WHERE id = $id_pedido";
        if ($mysql->efectuarConsulta($query)) {
            header('Location: ../views/repartidores.php?success=pedido_desasignado');
            exit();
        } else {
            header('Location: ../views/repartidores.php?error=db_error');
            exit();
        }
    }

    // --- Lógica para ACTUALIZAR ESTADO de un pedido (Solo Repartidor) ---
    if (isset($_POST['actualizar_estado'])) {
        if ($_SESSION['tipo'] !== 'repartidor') {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        $nuevo_estado = isset($_POST['nuevo_estado']) ? $mysql->escape_string($_POST['nuevo_estado']) : '';

        // Validar que el nuevo estado sea uno de los permitidos para repartidores
        $estados_validos = ['en_camino', 'entregado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            header('Location: ../views/repartidores.php?error=estado_no_valido');
            exit();
        }

        // Verificar que el pedido está asignado al repartidor logueado
        $query_check_owner = "SELECT id FROM pedidos WHERE id = $id_pedido AND id_repartidor = $id_entidad_logueada";
        $result_check_owner = $mysql->efectuarConsulta($query_check_owner);
        if (mysqli_num_rows($result_check_owner) === 0) {
            header('Location: ../views/repartidores.php?error=no_autorizado_pedido');
            exit();
        }

        // Actualizar el estado del pedido
        $query = "UPDATE pedidos SET estado = '$nuevo_estado' WHERE id = $id_pedido";
        if ($mysql->efectuarConsulta($query)) {
            header('Location: ../views/repartidores.php?success=estado_actualizado');
            exit();
        } else {
            header('Location: ../views/repartidores.php?error=db_error');
            exit();
        }
    }

} else {
    // Acceso directo no permitido
    header('Location: ../views/repartidores.php');
    exit();
}
?>