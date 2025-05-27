<?php
session_start();
require_once '../models/MySQL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
        // Redirigir a login si no está logueado o no es un usuario
        header('Location: ../views/login.php');
        exit();
    }

    $correo_usuario = $_SESSION['correo'];
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (empty($direccion) || empty($telefono)) {
        $_SESSION['error_pago'] = "La dirección y el teléfono son campos obligatorios.";
        header('Location: ../views/pagar.php');
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();

    // Actualizar la dirección y el teléfono del usuario
    $query_update = "UPDATE Usuarios SET direccion = ?, telefono = ? WHERE correo = ?";
    $stmt_update = $mysql->prepare($query_update);
    $stmt_update->bind_param("sss", $direccion, $telefono, $correo_usuario);

    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = "Tus datos han sido actualizados. Puedes continuar con tu pedido.";
        // Redirigir de nuevo a la página de pagar para que ahora vea el resumen
        header('Location: ../views/pagar.php');
        exit();
    } else {
        $_SESSION['error_pago'] = "Error al actualizar tus datos. Inténtalo de nuevo.";
        error_log("Error actualizando datos de usuario: " . $stmt_update->error);
        header('Location: ../views/pagar.php');
        exit();
    }

    $stmt_update->close();
    $mysql->desconectar();

} else {
    // Redirigir si no es una solicitud POST
    header('Location: ../views/pagar.php');
    exit();
}
?>