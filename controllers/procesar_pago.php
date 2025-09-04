<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../models/MySQL.php';
require_once '../controllers/Correo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
        $_SESSION['error_pago'] = "Debes iniciar sesión para procesar el pedido.";
        header('Location: ../views/login.php?redirect=pagar');
        exit();
    }

    $correo_usuario = $_SESSION['correo'];
    $nombre_usuario = $_SESSION['nombre'] ?? 'Cliente';
    $carrito_para_pagar = $_SESSION['carrito_para_pagar'] ?? [];

    if (empty($carrito_para_pagar)) {
        $_SESSION['error_pago'] = "No hay productos en el carrito para procesar el pedido.";
        header('Location: ../views/productos.php');
        exit();
    }

    $total_a_pagar = $_POST['total_a_pagar'] ?? 0;
    $subtotal_factura = $_POST['subtotal'] ?? 0;
    $iva_monto_factura = $_POST['iva_monto'] ?? 0;

    $mysql = new MySQL();
    $mysql->conectar();

    $query_usuario_datos = "SELECT id_Usuarios, direccion, telefono FROM usuarios WHERE correo = ?";
    $stmt_usuario_datos = $mysql->prepare($query_usuario_datos);
    $stmt_usuario_datos->bind_param("s", $correo_usuario);
    $stmt_usuario_datos->execute();
    $result_usuario_datos = $stmt_usuario_datos->get_result();
    $usuario_db_data = $result_usuario_datos->fetch_assoc();
    $stmt_usuario_datos->close();

    if (!$usuario_db_data || empty($usuario_db_data['direccion']) || empty($usuario_db_data['telefono'])) {
        $_SESSION['error_pago'] = "Tus datos de dirección o teléfono están incompletos. Por favor, actualízalos.";
        header('Location: ../views/pagar.php');
        exit();
    }

    $id_usuario = $usuario_db_data['id_Usuarios'];
    $direccion_envio = $usuario_db_data['direccion'];
    $telefono_contacto = $usuario_db_data['telefono'];

    $numero_pedido = 'PED-' . date('Ymd') . '-' . strtoupper(uniqid());
    $fecha_pedido = date('Y-m-d H:i:s');
    $estado_pedido = 'pendiente';

    // Iniciar transacción usando el método getConexion()
    $mysql->getConexion()->begin_transaction();

    try {
        $query_insert_pedido = "INSERT INTO pedidos (numero_pedido, id_Usuario, fecha_pedido, total_pedido, direccion_envio, telefono_contacto, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_pedido = $mysql->prepare($query_insert_pedido);
        $stmt_insert_pedido->bind_param("sisdsss", $numero_pedido, $id_usuario, $fecha_pedido, $total_a_pagar, $direccion_envio, $telefono_contacto, $estado_pedido);

        if (!$stmt_insert_pedido->execute()) {
            throw new Exception("Error al insertar el pedido: " . $stmt_insert_pedido->error);
        }
        // Obtener el ID del pedido recién insertado usando getConexion()->insert_id
        $id_nuevo_pedido = $mysql->getConexion()->insert_id;
        $stmt_insert_pedido->close();

        $query_insert_detalle = "INSERT INTO detallepedidos (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal_linea) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_detalle = $mysql->prepare($query_insert_detalle);

        foreach ($carrito_para_pagar as $item) {
            $id_producto = $item['id'];
            $nombre_producto = $item['nombre'];
            $precio_unitario = $item['precio'];
            $cantidad = $item['cantidad'];
            $subtotal_linea = $precio_unitario * $cantidad;

            $stmt_insert_detalle->bind_param("iisidd", $id_nuevo_pedido, $id_producto, $nombre_producto, $precio_unitario, $cantidad, $subtotal_linea);
            if (!$stmt_insert_detalle->execute()) {
                throw new Exception("Error al insertar detalle del pedido para producto: " . $nombre_producto . " - " . $stmt_insert_detalle->error);
            }
        }
        $stmt_insert_detalle->close();

        $correo = new Correo();
        $datos_cliente_correo = [
            'nombre' => $nombre_usuario,
            'correo' => $correo_usuario,
            'direccion' => $direccion_envio,
            'telefono' => $telefono_contacto
        ];

        $envio_correo_exitoso = $correo->enviarFactura(
            $correo_usuario,
            $nombre_usuario,
            $numero_pedido,
            $fecha_pedido,
            $datos_cliente_correo,
            $carrito_para_pagar,
            $subtotal_factura,
            $iva_monto_factura,
            $total_a_pagar
        );

        if (!$envio_correo_exitoso) {
            error_log("Error: No se pudo enviar el correo de confirmación para el pedido " . $numero_pedido);
        }

        // Confirmar la transacción
        $mysql->getConexion()->commit();

        unset($_SESSION['carrito_para_pagar']);
        unset($_SESSION['carrito']);

        $_SESSION['success_message'] = "Tu pedido #" . $numero_pedido . " ha sido confirmado y una copia ha sido enviada a tu correo.";
        header('Location: ../views/confirmacion_pedido.php?numero_pedido=' . urlencode($numero_pedido));
        exit();

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $mysql->getConexion()->rollback();
        $_SESSION['error_message'] = "Lo sentimos, hubo un error al procesar tu pedido. Por favor, inténtalo de nuevo. " . $e->getMessage();
        error_log("Error en procesar_pago.php: " . $e->getMessage());
        header('Location: ../views/pagar.php');
        exit();
    } finally {
        $mysql->desconectar();
    }

} else {
    $_SESSION['error_message'] = "Acceso no permitido al procesador de pago.";
    header('Location: ../views/productos.php');
    exit();
}
?>