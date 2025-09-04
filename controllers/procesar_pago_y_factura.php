<?php
session_start();
require_once '../models/MySQL.php';
require_once '../controllers/Correo.php'; // Incluir la clase Correo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['correo']) || $_SESSION['tipo'] !== 'user') {
        $_SESSION['error_pago'] = "Debes iniciar sesión para procesar el pedido.";
        header('Location: ../views/login.php?redirect=pagar');
        exit();
    }

    $correo_usuario = $_SESSION['correo'];
    $carrito_para_pagar = $_SESSION['carrito_para_pagar'] ?? [];
    $total_a_pagar = $_POST['total_a_pagar'] ?? 0;
    // El método de pago ya no se selecciona, lo establecemos por defecto
    $metodo_pago = "ContraEntrega"; // O cualquier otro método de pago por defecto

    if (empty($carrito_para_pagar) || $total_a_pagar <= 0) {
        $_SESSION['error_pago'] = "No hay productos en el carrito o el total es inválido.";
        header('Location: ../views/carrito.php');
        exit();
    }

    $mysql = new MySQL();
    $mysql->conectar();

    // Obtener id_Usuarios, dirección, teléfono y nombre del usuario
    $query_usuario = "SELECT id_Usuarios, direccion, telefono, nombre FROM usuarios WHERE correo = ?";
    $stmt_usuario = $mysql->prepare($query_usuario);
    $stmt_usuario->bind_param("s", $correo_usuario);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $usuario_db_data = $result_usuario->fetch_assoc();
    $stmt_usuario->close();

    if (!$usuario_db_data || empty($usuario_db_data['direccion']) || empty($usuario_db_data['telefono'])) {
        $_SESSION['error_pago'] = "Por favor, completa tu dirección y teléfono antes de confirmar el pedido.";
        header('Location: ../views/pagar.php');
        exit();
    }

    $id_usuario = $usuario_db_data['id_Usuarios']; // Usar id_Usuarios
    $direccion_envio = $usuario_db_data['direccion'];
    $telefono_contacto = $usuario_db_data['telefono'];
    $nombre_usuario = $usuario_db_data['nombre'];

    // Generar un número de pedido único
    $numero_pedido = 'PEDIDO-' . uniqid();
    $fecha_pedido = date('Y-m-d H:i:s');

    // Iniciar transacción
    $mysql->getConexion()->begin_transaction(); // Acceder a la conexión subyacente para begin_transaction

    try {
        // 1. Insertar en la tabla Pedidos
        $query_pedido = "INSERT INTO pedidos (numero_pedido, id_usuario, fecha_pedido, total_pedido, direccion_envio, telefono_contacto, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_pedido = $mysql->prepare($query_pedido);
        $estado_inicial = 'pendiente';
        $stmt_pedido->bind_param("sisdsss", $numero_pedido, $id_usuario, $fecha_pedido, $total_a_pagar, $direccion_envio, $telefono_contacto, $estado_inicial);

        if (!$stmt_pedido->execute()) {
            throw new Exception("Error al guardar el pedido: " . $stmt_pedido->error);
        }
        $id_nuevo_pedido = $mysql->getConexion()->insert_id; // Obtener el ID del pedido recién insertado
        $stmt_pedido->close();

        // 2. Insertar en la tabla DetallePedidos
        $query_detalle = "INSERT INTO detallepedidos (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal_linea) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_detalle = $mysql->prepare($query_detalle);

        foreach ($carrito_para_pagar as $item) {
            $id_producto = $item['id'];
            $nombre_producto = $item['nombre'];
            $precio_unitario = $item['precio'];
            $cantidad = $item['cantidad'];
            $subtotal_linea = $precio_unitario * $cantidad;

            $stmt_detalle->bind_param("iisidd", $id_nuevo_pedido, $id_producto, $nombre_producto, $precio_unitario, $cantidad, $subtotal_linea);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al guardar detalle del pedido para producto " . $nombre_producto . ": " . $stmt_detalle->error);
            }
        }
        $stmt_detalle->close();

        // Calcular IVA para la factura (21% de ejemplo)
        $subtotal_factura = 0;
        foreach ($carrito_para_pagar as $item) {
            $subtotal_factura += $item['precio'] * $item['cantidad'];
        }
        $iva_porcentaje = 0.21;
        $iva_monto_factura = $subtotal_factura * $iva_porcentaje;
        $total_factura = $subtotal_factura + $iva_monto_factura;

        // 3. Enviar correo de factura
        $correo = new Correo();
        $envio_factura_exitoso = $correo->enviarFactura(
            $correo_usuario,
            $nombre_usuario,
            $numero_pedido,
            $fecha_pedido,
            [
                'nombre' => $nombre_usuario,
                'direccion' => $direccion_envio,
                'telefono' => $telefono_contacto
            ],
            $carrito_para_pagar,
            $subtotal_factura,
            $iva_monto_factura,
            $total_factura
        );

        if (!$envio_factura_exitoso) {
            error_log("No se pudo enviar la factura por correo electrónico para el pedido " . $numero_pedido);
            // Puedes decidir si esto es un error crítico o solo una advertencia
        }

        // Confirmar transacción
        $mysql->getConexion()->commit();

        // Limpiar el carrito de la sesión
        unset($_SESSION['carrito_para_pagar']);
        unset($_SESSION['carrito']); // También limpiar el carrito principal

        $_SESSION['success_pago'] = "¡Tu pedido #" . $numero_pedido . " ha sido procesado exitosamente! Se ha enviado una factura a tu correo.";
        header('Location: ../views/confirmacion_pedido.php?numero_pedido=' . urlencode($numero_pedido));
        exit();

    } catch (Exception $e) {
        $mysql->getConexion()->rollback(); // Revertir transacción si algo falla
        $_SESSION['error_pago'] = "Ocurrió un error al procesar tu pedido: " . $e->getMessage();
        error_log("Error en procesar_pago_y_factura: " . $e->getMessage()); // Registrar el error en el log
        header('Location: ../views/pagar.php');
        exit();
    } finally {
        $mysql->desconectar();
    }

} else {
    $_SESSION['error_pago'] = "Acceso no permitido.";
    header('Location: ../views/pagar.php');
    exit();
}
?>