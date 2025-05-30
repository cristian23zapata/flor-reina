<?php
session_start();

// Redirigir si no hay datos de pedido confirmados en la sesión
if (!isset($_SESSION['pedido_confirmado'])) {
    header('Location: ../views/productos.php');
    exit();
}

$pedido = $_SESSION['pedido_confirmado'];
unset($_SESSION['pedido_confirmado']); // Limpiar los datos después de mostrarlos

$numeroPedido = $pedido['numero_pedido'];
$fechaPedido = $pedido['fecha_pedido'];
$datosUsuario = $pedido['datos_usuario'];
$carrito = $pedido['carrito'];
$subtotal = $pedido['subtotal'];
$ivaMonto = $pedido['iva_monto'];
$total = $pedido['total'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura de Compra - Flor Reina</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .recibo-container { max-width: 800px; margin: 30px auto; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); overflow: hidden; }
        .recibo-header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 20px; text-align: center; }
        .datos-cliente { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        @media print {
            .navbar, .btn, footer { display: none; }
            .recibo-container {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="alert alert-success text-center py-4 mb-4">
            <h3>¡Gracias por tu compra!</h3>
            <p>Tu pedido ha sido confirmado y la factura ha sido enviada a tu correo electrónico.</p>
        </div>

        <div class="recibo-container p-4">
            <div class="recibo-header">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" class="logo-recibo mx-auto d-block mb-3" style="max-height: 80px;">
                <h2 class="mb-0">Factura de Compra</h2>
                <span class="badge bg-light text-success mt-2">Pedido Confirmado</span>
            </div>

            <div class="recibo-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5><i class="bi bi-receipt"></i> Número de Pedido</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($numeroPedido); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="bi bi-calendar"></i> Fecha del Pedido</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($fechaPedido); ?></p>
                    </div>
                </div>

                <h5 class="mt-4"><i class="bi bi-person-check"></i> Datos del Cliente</h5>
                <div class="datos-cliente">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($datosUsuario['nombre'] ?? 'N/A'); ?></p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['correo'] ?? 'N/A'); ?></p>
                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($datosUsuario['direccion'] ?? 'No especificada'); ?></p>
                    <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($datosUsuario['telefono'] ?? 'No especificado'); ?></p>
                </div>

                <h5 class="mt-4"><i class="bi bi-cart-check"></i> Resumen del Pedido</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito as $item): ?>
                            <tr class="producto-item">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../<?php echo htmlspecialchars($item['imagen']); ?>"
                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                        <?php echo htmlspecialchars($item['nombre']); ?>
                                    </div>
                                </td>
                                <td class="text-end">$<?php echo number_format($item['precio'], 2); ?></td>
                                <td class="text-center"><?php echo $item['cantidad']; ?></td>
                                <td class="text-end">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="totales bg-light rounded p-3 mt-3">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (21%):</span>
                                <span>$<?php echo number_format($ivaMonto, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="recibo-footer bg-light p-3 text-center">
                <p class="mb-2">¡Esperamos verte de nuevo!</p>
                <p class="text-muted small mb-0">Tu pedido será procesado y enviado en un plazo de 24-48 horas.</p>
                <p class="text-muted small mb-0">Revisa tu bandeja de entrada para la confirmación por correo.</p>

                <div class="mt-3">
                    <a href="../views/productos.php" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-left"></i> Seguir comprando
                    </a>
                    <button class="btn btn-info" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir Factura
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
            <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>