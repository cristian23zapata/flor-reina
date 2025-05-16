<?php
require_once '../models/MySQL.php';



// Verificar si hay productos en el carrito
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : (isset($_COOKIE['carrito']) ? json_decode($_COOKIE['carrito'], true) : []);

$mysql = new MySQL();
$mysql->conectar();


// Calcular totales
$subtotal = 0;
$iva = 0.21; // 21% de IVA
$total = 0;

foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$impuestos = $subtotal * $iva;
$total = $subtotal + $impuestos;

// Generar número de pedido único
$numeroPedido = 'PED-' . date('Ymd') . '-' . strtoupper(uniqid());
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirmación de Pago - Flor Reina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .recibo-container {
      max-width: 800px;
      margin: 30px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .recibo-header {
      background: linear-gradient(135deg, #28a745, #218838);
      color: white;
      padding: 20px;
      text-align: center;
    }
    .recibo-body {
      padding: 30px;
    }
    .recibo-footer {
      background: #f8f9fa;
      padding: 20px;
      text-align: center;
      border-top: 1px solid #eee;
    }
    .producto-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }
    .totales {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      margin-top: 20px;
    }
    .logo-recibo {
      max-height: 80px;
      margin-bottom: 15px;
    }
    .badge-estado {
      font-size: 1rem;
      padding: 8px 15px;
      border-radius: 20px;
    }
    .datos-cliente {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="../index.php">
      <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2">
       
      </div>
    </div>
  </div>
</nav>

<!-- Contenido principal -->
<div class="container py-5">
  <div class="recibo-container">
    <!-- Encabezado del recibo -->
    <div class="recibo-header">
      <img src="../assets/imagenes/logo.png" alt="Flor Reina" class="logo-recibo">
      <h2 class="mb-0">Confirmación de Pedido</h2>
      <span class="badge bg-light text-success badge-estado mt-2">Pago Completado</span>
    </div>
    
    <!-- Cuerpo del recibo -->
    <div class="recibo-body">
      <!-- Información del pedido -->
      <div class="row mb-4">
        <div class="col-md-6">
          <h5><i class="bi bi-receipt"></i> Número de Pedido</h5>
          <p class="text-muted"><?php echo $numeroPedido; ?></p>
        </div>
        <div class="col-md-6">
          <h5><i class="bi bi-calendar"></i> Fecha</h5>
          <p class="text-muted"><?php echo date('d/m/Y H:i'); ?></p>
        </div>
      </div>
      
      <!-- Datos del cliente -->
      <div class="datos-cliente">
        <h5><i class="bi bi-person"></i> Datos del Cliente</h5>
        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>Nombre:</strong> 
            <p class="mb-1"><strong>Correo:</strong> 
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($datosUsuario['telefono'] ?? 'No proporcionado'); ?></p>
            <p class="mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($datosUsuario['direccion'] ?? 'No proporcionada'); ?></p>
          </div>
        </div>
      </div>
      
      <!-- Resumen del pedido -->
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
                  <img src="../<?php echo $item['imagen']; ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
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
      
      <!-- Totales -->
      <div class="totales">
        <div class="row">
          <div class="col-md-6 offset-md-6">
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal:</span>
              <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>IVA (21%):</span>
              <span>$<?php echo number_format($impuestos, 2); ?></span>
            </div>
            <div class="d-flex justify-content-between fw-bold fs-5">
              <span>Total:</span>
              <span>$<?php echo number_format($total, 2); ?></span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Método de pago -->
      <div class="mt-4">
        <h5><i class="bi bi-credit-card"></i> Método de Pago</h5>
        <div class="d-flex align-items-center">
          <i class="bi bi-credit-card-fill fs-3 me-3 text-success"></i>
          <div>
            <p class="mb-0">Tarjeta de crédito terminada en ****4242</p>
            <small class="text-muted">Pago procesado por Stripe</small>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Pie del recibo -->
    <div class="recibo-footer">
      <p class="mb-2">¡Gracias por tu compra en Flor Reina!</p>
      <p class="text-muted small mb-0">Tu pedido será procesado y enviado en un plazo de 24-48 horas.</p>
      <p class="text-muted small mb-0">Recibirás un correo electrónico con los detalles de envío.</p>
      
      <div class="mt-3">
        <a href="../views/productos.php" class="btn btn-outline-primary me-2">
          <i class="bi bi-arrow-left"></i> Volver a la tienda
        </a>
        <button class="btn btn-success" onclick="window.print()">
          <i class="bi bi-printer"></i> Imprimir Recibo
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; 2025 Flor Reina. Todos los derechos reservados.</p>
    <small>Contacto: info@florreina.es | Tel: +34 666 999 123</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Vaciar el carrito después de la compra
  document.addEventListener('DOMContentLoaded', function() {
    // Limpiar el carrito del localStorage
    localStorage.removeItem('carrito');
    
    // Opcional: Limpiar el carrito de la sesión PHP si lo estás usando
    <?php unset($_SESSION['carrito']); ?>
  });
</script>
</body>
</html>