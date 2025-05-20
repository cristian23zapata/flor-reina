<?php
require_once '../models/MySQL.php';
session_start();

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Buscar en usuarios
    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios WHERE correo = '$correo'");
    $usuario = $resultado->fetch_assoc();

    // Buscar en repartidores solo si no se encontró en usuarios
    if (!$usuario) {
        $resultado2 = $mysql->efectuarConsulta("SELECT * FROM Repartidores WHERE correo = '$correo'");
        $repartidor = $resultado2->fetch_assoc();
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
        echo "Correo o contraseña incorrectos.";
        header("refresh:3;url=../views/login.php");
    }
}
?>
