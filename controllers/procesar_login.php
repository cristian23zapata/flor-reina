<?php
session_start();
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Buscar en usuarios
    $query = "SELECT * FROM Usuarios WHERE correo = ?";
    $stmt = $mysql->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    // Buscar en repartidores solo si no se encontró en usuarios
    if (!$usuario) {
        $query = "SELECT * FROM Repartidores WHERE correo = ?";
        $stmt = $mysql->prepare($query);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $repartidor = $resultado->fetch_assoc();
    }

    $verificar = null;
    $datos = null;
    $tipo = null;

    if ($usuario) {
        $verificar = $usuario['password'];
        $datos = $usuario;
        $tipo = $usuario['tipo']; // 'cliente' o 'administrador'
    } elseif (isset($repartidor)) {
        $verificar = $repartidor['password'];
        $datos = $repartidor;
        $tipo = $repartidor['tipo']; // 'repartidor'
    }

    if ($verificar && password_verify($password, $verificar)) {
        $_SESSION['nombre'] = $datos['nombre'];
        $_SESSION['correo'] = $datos['correo'];
        $_SESSION['tipo'] = $tipo;

        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../views/login.php?estado=contraseña_incorrecta");
    }
}
?>