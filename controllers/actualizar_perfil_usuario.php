<?php
require_once '../models/MySQL.php';
session_start();

// Redirigir si no hay sesión activa o no hay correo en la sesión
if (!isset($_SESSION['correo']) || !isset($_SESSION['tipo'])) {
    header('Location: ../views/login.php');
    exit();
}

$mysql = new MySQL();
$mysql->conectar();

// Obtener el correo del usuario directamente de la sesión (este es nuestro identificador único)
$correo_sesion = $mysql->escape_string($_SESSION['correo']);

// Primero, obtenemos el ID del usuario actual y su contraseña hasheada usando su correo de la sesión.
// Esto nos asegura que operamos en el usuario correcto y tenemos el hash para validar la contraseña actual.
$query_get_user_data = "SELECT id_Usuarios, password FROM usuarios WHERE correo = '$correo_sesion'"; // 'password' es el nombre de tu columna de contraseña
$result_get_user_data = $mysql->efectuarConsulta($query_get_user_data);

if (mysqli_num_rows($result_get_user_data) == 0) {
    // Si no encontramos al usuario con el correo de la sesión, es un error grave.
    session_destroy();
    header('Location: ../views/login.php?error=sesion_corrupta');
    exit();
}

$user_data_db = mysqli_fetch_assoc($result_get_user_data);
$id_usuario_db = $user_data_db['id_Usuarios']; // Este es el ID real del usuario en la DB, lo usaremos para las actualizaciones.
$hash_actual_db = $user_data_db['password']; // Contraseña hasheada de la DB


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Lógica para Actualizar Datos Personales ---
    if (isset($_POST['actualizar_datos'])) {
        $nombre = isset($_POST['nombre']) ? $mysql->escape_string($_POST['nombre']) : '';
        $nuevo_correo = isset($_POST['correo']) ? $mysql->escape_string($_POST['correo']) : '';
        $direccion = isset($_POST['direccion']) ? $mysql->escape_string($_POST['direccion']) : '';
        $telefono = isset($_POST['telefono']) ? $mysql->escape_string($_POST['telefono']) : '';

        if (empty($nombre) || empty($nuevo_correo)) {
            header('Location: ../views/editar_perfil.php?error=datos_incompletos');
            exit();
        }

        // Verificar si el nuevo correo ya existe para otro usuario (excepto el actual)
        // Usamos id_usuario_db para excluir al propio usuario
        $query_check_email = "SELECT id_Usuarios FROM usuarios WHERE correo = '$nuevo_correo' AND id_Usuarios != $id_usuario_db";
        $resultado_check_email = $mysql->efectuarConsulta($query_check_email);
        if (mysqli_num_rows($resultado_check_email) > 0) {
            header('Location: ../views/editar_perfil.php?error=correo_existente');
            exit();
        }

        // Actualizar datos del usuario usando su ID_Usuarios
        $query = "UPDATE usuarios SET nombre = '$nombre', correo = '$nuevo_correo', direccion = '$direccion', telefono = '$telefono' WHERE id_Usuarios = $id_usuario_db";

        if ($mysql->efectuarConsulta($query)) {
            // Actualizar la sesión si el nombre o correo han cambiado
            $_SESSION['nombre'] = $nombre;
            $_SESSION['correo'] = $nuevo_correo; // ¡Importante actualizar el correo en la sesión!
            header('Location: ../views/editar_perfil.php?success=perfil_actualizado');
            exit();
        } else {
            header('Location: ../views/editar_perfil.php?error=db_error');
            exit();
        }
    }

    // --- Lógica para Cambiar Contraseña ---
    if (isset($_POST['actualizar_contrasena'])) {
        $contrasena_actual = isset($_POST['contrasena_actual']) ? $mysql->escape_string($_POST['contrasena_actual']) : '';
        $nueva_contrasena = isset($_POST['nueva_contrasena']) ? $_POST['nueva_contrasena'] : '';
        $confirmar_contrasena = isset($_POST['confirmar_contrasena']) ? $_POST['confirmar_contrasena'] : '';

        if (empty($contrasena_actual) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
            header('Location: ../views/editar_perfil.php?error=datos_incompletos');
            exit();
        }

        if ($nueva_contrasena !== $confirmar_contrasena) {
            header('Location: ../views/editar_perfil.php?error=contrasena_no_coincide');
            exit();
        }

        // Verificar la contraseña actual contra el hash obtenido al inicio
        if (password_verify($contrasena_actual, $hash_actual_db)) { // Usa $hash_actual_db
            // Hashear la nueva contraseña
            $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos usando el ID_Usuarios
            $query_update_password = "UPDATE usuarios SET password = '$nueva_contrasena_hash' WHERE id_Usuarios = $id_usuario_db"; // 'password' es el nombre de tu columna de contraseña
            if ($mysql->efectuarConsulta($query_update_password)) {
                header('Location: ../views/editar_perfil.php?success=contrasena_actualizada');
                exit();
            } else {
                header('Location: ../views/editar_perfil.php?error=db_error');
                exit();
            }
        } else {
            header('Location: ../views/editar_perfil.php?error=contrasena_actual_incorrecta');
            exit();
        }
    }
} else {
    // Acceso directo no permitido
    header('Location: ../views/editar_perfil.php');
    exit();
}
?>