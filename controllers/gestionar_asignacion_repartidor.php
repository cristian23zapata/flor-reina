<?php
session_start();
require_once '../models/MySQL.php';

// Redirigir si no hay sesión activa o el usuario no es 'repartidor' ni 'admin'
if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'repartidor' && $_SESSION['tipo'] !== 'admin')) {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();
$conn = $mysql->getConexion(); // Get the mysqli connection object

// Obtener el ID de la entidad logueada para verificar permisos
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
        $query_check_repartidor = "SELECT id FROM repartidores WHERE id = ?";
        $stmt_check_repartidor = $conn->prepare($query_check_repartidor);
        $stmt_check_repartidor->bind_param("i", $id_repartidor_a_asignar);
        $stmt_check_repartidor->execute();
        $result_check_repartidor = $stmt_check_repartidor->get_result();
        if ($result_check_repartidor->num_rows === 0) {
            $stmt_check_repartidor->close();
            header('Location: ../views/repartidores.php?error=repartidor_no_valido');
            exit();
        }
        $stmt_check_repartidor->close();

        // Asignar el repartidor y cambiar el estado a 'asignado'
        $query_assign = "UPDATE pedidos SET id_repartidor = ?, estado = 'enviado' WHERE id = ? AND id_repartidor IS NULL";
        $stmt_assign = $conn->prepare($query_assign);
        $stmt_assign->bind_param("ii", $id_repartidor_a_asignar, $id_pedido);
        
        if ($stmt_assign->execute()) {
            if ($stmt_assign->affected_rows > 0) {
                header('Location: ../views/repartidores.php?success=pedido_asignado');
            } else {
                header('Location: ../views/repartidores.php?error=pedido_ya_asignado');
            }
            $stmt_assign->close();
            exit();
        } else {
            error_log("Error al asignar pedido: " . $stmt_assign->error);
            header('Location: ../views/repartidores.php?error=db_error');
            $stmt_assign->close();
            exit();
        }
    }

    // --- Lógica para DESASIGNAR un pedido ---
    if (isset($_POST['desasignar_pedido']) || isset($_POST['desasignar_pedido_admin'])) {
        $query_check_owner = "";
        $types = "";
        $params = [];

        if ($_SESSION['tipo'] === 'repartidor') {
            $query_check_owner = "SELECT id FROM pedidos WHERE id = ? AND id_repartidor = ?";
            $types = "ii";
            $params = [$id_pedido, $id_entidad_logueada];
        } elseif ($_SESSION['tipo'] === 'admin') {
            $query_check_owner = "SELECT id FROM pedidos WHERE id = ? AND id_repartidor IS NOT NULL";
            $types = "i";
            $params = [$id_pedido];
        } else {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        $stmt_check_owner = $conn->prepare($query_check_owner);
        if (!$stmt_check_owner) {
            error_log("Error al preparar check_owner: " . $conn->error);
            header('Location: ../views/repartidores.php?error=db_error');
            exit();
        }
        $stmt_check_owner->bind_param($types, ...$params);
        $stmt_check_owner->execute();
        $result_check_owner = $stmt_check_owner->get_result();
        if ($result_check_owner->num_rows === 0) {
            $stmt_check_owner->close();
            header('Location: ../views/repartidores.php?error=no_autorizado'); // Or specific error like 'pedido_no_asignado_a_ti'
            exit();
        }
        $stmt_check_owner->close();

        // Desasignar el repartidor y cambiar el estado a 'confirmado'
        $query_unassign = "UPDATE pedidos SET id_repartidor = NULL, estado = 'confirmado' WHERE id = ?";
        $stmt_unassign = $conn->prepare($query_unassign);
        $stmt_unassign->bind_param("i", $id_pedido);
        
        if ($stmt_unassign->execute()) {
            header('Location: ../views/repartidores.php?success=pedido_desasignado');
            $stmt_unassign->close();
            exit();
        } else {
            error_log("Error al desasignar pedido: " . $stmt_unassign->error);
            header('Location: ../views/repartidores.php?error=db_error');
            $stmt_unassign->close();
            exit();
        }
    }

    // --- Lógica para ACTUALIZAR ESTADO de un pedido (Solo Repartidor) ---
    if (isset($_POST['actualizar_estado'])) {
        if ($_SESSION['tipo'] !== 'repartidor') {
            header('Location: ../views/repartidores.php?error=no_autorizado');
            exit();
        }

        $nuevo_estado = $_POST['nuevo_estado'] ?? '';

        // Validar que el nuevo estado sea uno de los permitidos para repartidores
        $estados_validos = ['en_camino', 'entregado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            header('Location: ../views/repartidores.php?error=estado_no_valido');
            exit();
        }

        // Verificar que el pedido está asignado al repartidor logueado
        $query_check_owner = "SELECT id FROM pedidos WHERE id = ? AND id_repartidor = ?";
        $stmt_check_owner = $conn->prepare($query_check_owner);
        if (!$stmt_check_owner) {
            error_log("Error al preparar check_owner para update estado: " . $conn->error);
            header('Location: ../views/repartidores.php?error=db_error');
            exit();
        }
        $stmt_check_owner->bind_param("ii", $id_pedido, $id_entidad_logueada);
        $stmt_check_owner->execute();
        $result_check_owner = $stmt_check_owner->get_result();
        if ($result_check_owner->num_rows === 0) {
            $stmt_check_owner->close();
            header('Location: ../views/repartidores.php?error=no_autorizado_pedido');
            exit();
        }
        $stmt_check_owner->close();

        // Actualizar el estado del pedido
        $query_update_state = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt_update_state = $conn->prepare($query_update_state);
        $stmt_update_state->bind_param("si", $nuevo_estado, $id_pedido); // "s" for string, "i" for integer
        
        if ($stmt_update_state->execute()) {
            header('Location: ../views/repartidores.php?success=estado_actualizado');
            $stmt_update_state->close();
            exit();
        } else {
            error_log("Error al actualizar estado: " . $stmt_update_state->error);
            header('Location: ../views/repartidores.php?error=db_error');
            $stmt_update_state->close();
            exit();
        }
    }

} else {
    // Acceso directo no permitido
    header('Location: ../views/repartidores.php');
    exit();
}

$mysql->desconectar();
?>