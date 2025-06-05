<?php
require_once '../models/MySQL.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        header('Location: ../views/login.php?error=no_data');
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();

    // Intentar autenticar en la tabla 'usuarios'
    $query_user = "SELECT id_Usuarios, nombre, correo, password, tipo FROM usuarios WHERE correo = '" . $mysql->escape_string($correo) . "'";
    $result_user = $mysql->efectuarConsulta($query_user);

    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user_data = mysqli_fetch_assoc($result_user);

        if (password_verify($contrasena, $user_data['password'])) {
            // Login exitoso como usuario o admin
            $_SESSION['id_usuario'] = $user_data['id_Usuarios']; // Guarda el ID de usuario
            $_SESSION['nombre'] = $user_data['nombre'];
            $_SESSION['correo'] = $user_data['correo'];
            $_SESSION['tipo'] = $user_data['tipo']; // 'admin' o 'user'

            // Redireccionar según el tipo de usuario
            if ($user_data['tipo'] === 'admin') {
                header('Location: ../views/admin_pedidos.php'); // O a donde vaya tu panel de admin principal
            } 
            if ($user_data['tipo'] === 'user') { // Si es un usuario normal
                // Aquí podrías agregar más lógica si es necesario, por ejemplo:
                header('Location: ../index.php'); // Página principal para clientes
            }
            exit();
        }
    }

    // Si no se encontró en 'usuarios' o la contraseña no coincidió, intentar en la tabla 'repartidores'
    $query_repartidor = "SELECT id, nombre, correo, password FROM repartidores WHERE correo = '" . $mysql->escape_string($correo) . "'";
    $result_repartidor = $mysql->efectuarConsulta($query_repartidor);

    if ($result_repartidor && mysqli_num_rows($result_repartidor) > 0) {
        $repartidor_data = mysqli_fetch_assoc($result_repartidor);

        if (password_verify($contrasena, $repartidor_data['password'])) {
            // Login exitoso como repartidor
            $_SESSION['id_repartidor'] = $repartidor_data['id']; // Guarda el ID específico del repartidor
            $_SESSION['nombre'] = $repartidor_data['nombre']; // Se usa 'nombre' para la navbar
            $_SESSION['correo'] = $repartidor_data['correo'];
            $_SESSION['tipo'] = 'repartidor'; // Define el tipo 'repartidor' en la sesión

            header('Location: ../views/repartidores.php'); // Redirige al panel de repartidores
            exit();
        }
    }

    // Si ninguna de las autenticaciones fue exitosa
    header('Location: ../views/login.php?estado=invalid_credentials');
    exit();

} else {
    header('Location: ../views/login.php');
    exit();
}
?>