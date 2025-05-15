<?php
require_once '../models/MySQL.php';

$mysql = new MySQL();
$mysql->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $tipo_transporte = $_POST['transporte'];
    $foto = $_FILES['foto'];

    if ($password !== $confirmar_password) {
        echo "Las contraseñas no coinciden.";
        exit();
    }

    // Verificar si el correo ya está registrado
    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios WHERE correo = '$correo'");
    if ($resultado->num_rows > 0) {
        echo "El correo ya está registrado.";
        exit();
    }

    // Insertar el nuevo usuario en la base de datos
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $mysql->efectuarConsulta("INSERT INTO Usuarios (nombre, correo, password, tipo) VALUES ('$nombre', '$correo', '$hashed_password', 'user')");

    echo "Registro exitoso. Redirigiendo a la página de inicio de sesión...";
    header("refresh:3;url=../views/login.php");
}