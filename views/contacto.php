<?php
require_once '../models/MySQL.php';


// Mostrar mensajes de éxito/error
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    echo '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            ¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}



session_start();

    if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");

    exit();
  } 
    
    $mysql = new MySQL;
    $mysql->conectar();

    $resultado = $mysql->efectuarConsulta("SELECT * FROM Usuarios;");

    $mysql->desconectar();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacto | Tu Tienda</title>
    <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
    <link rel="stylesheet" href="../assets/css/estilo_contacto.css">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
</head>

<style>
/* Estilos para la sección de contacto */
.contact-section {
    display: flex;
    justify-content: space-between;
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    gap: 3rem;
    background-color:linear-gradient(to right, #ffe6f0, #fff0f5);
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.contact-info, .contact-form {
    flex: 1;
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.contact-info h3, .contact-form h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    border-bottom: 2px solid #3498db;
    padding-bottom: 0.5rem;
}

.contact-info p {
    margin-bottom: 1rem;
    color: #555;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.whatsapp-section {
    background-color: #f0f8ff;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #25D366;
}

.whatsapp-btn {
    background-color: #25D366;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.small-text {
    font-size: 0.85rem;
    color: #666;
}

.pqrs-section {
    background-color: #fff8f0;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #FFA500;
}

.form-group {
    margin-bottom: 1.2rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52,152,219,0.25);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.btn-primary {
    background-color: #3498db;
    border: none;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color:rgb(124, 152, 170);
}

/* Estilo para validación de teléfono */
#telefono:invalid {
    border-color: #ff4444;
}

#telefono:valid {
    border-color: #00C851;
}

html, body {
  height: 100%;
}

body {
  display: flex;
  flex-direction: column;
}

/* El contenido principal debe crecer para empujar el footer */
main {
  flex: 1 0 auto;
}

/* El footer no debe crecer */
footer {
  flex-shrink: 0;
}   
</style>


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
        <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
          <div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
        <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
    </ul>
</div>
        <?php else: ?>
          <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
        <?php endif; ?>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
          <button class="btn btn-outline-success position-relative" data-bs-toggle="modal" data-bs-target="#modalCarrito" id="btn-carrito">
    <i class="bi bi-bag"></i> Carrito
    <span id="carrito-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
      0
    </span>
  </button>
        <?php } ?>
      </div>
    </div>
  </div>
  
</nav>
<!-- Contenido principal -->
<div class="contact-section">
  <div class="contact-info">
    <h3>Información de contacto</h3>
    <p><i class="bi bi-telephone"></i> +57 321 213 8319</p>
    <p><i class="bi bi-envelope"></i> sugerencias@yogures.com</p>
    <p><i class="bi bi-geo-alt"></i> Calle 8va #5-55</p>
    <p><i class="bi bi-clock"></i> Lun a Vie: 9 am - 4 pm | Sáb: 9 am - 12 m</p>
    
    <div class="whatsapp-section mt-4">
      <h4>Escríbenos a WhatsApp</h4>
      <a href="https://wa.me/3146318358" class="btn btn-success whatsapp-btn">
        <i class="bi bi-whatsapp"></i> Chatear con asesores
      </a>
      <p class="small-text mt-2">Si no estamos disponibles, déjanos un correo y te responderemos en 20-36 horas.</p>
    </div>
    
    <div class="pqrs-section mt-4">
      <h5>PQRS</h5>
      <p class="small-text">Para peticiones, quejas, reclamos y sugerencias:</p>
      <p class="small-text"><strong>flor-reina@yogures.com</strong></p>
    </div>
  </div>
  
  <div class="contact-form">
    <h3>Contáctanos</h3>
    <p class="form-description">Si tienes alguna duda o requerimiento, completa el formulario:</p>
    

    <!-- Formulario de contacto -->
    <form method="POST" action="../controllers/procesar_contacto.php">
  <div class="form-group">
    <input type="text" class="form-control" name="nombre" placeholder="Nombre" required> 
  </div>
  
  <div class="form-group">
      <input type="tel" class="form-control" id="telefono" name="telefono" 
        placeholder="Teléfono de Contacto (10 dígitos)" 
        pattern="[0-9]{10}" 
        maxlength="10" 
        required
       oninput="validarTelefono(this)">
    <small id="telefono-error" class="text-danger" style="display:none;">
      El teléfono debe tener exactamente 10 dígitos numéricos
    </small>
  </div>

  <div class="form-group">
    <input type="email" class="form-control" name="email" placeholder="Correo Electrónico" required> 
  </div>
  
  <div class="form-group">
    <textarea class="form-control" name="mensaje" placeholder="Mensaje" rows="5" required></textarea> 
  </div>
  
  <button type="submit" class="btn btn-primary">Enviar mensaje</button>
</form>

      <?php
// Mostrar mensajes de error
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $mensaje = "";
    
    switch ($error) {
        case 'campos_vacios':
            $mensaje = "Por favor completa todos los campos obligatorios.";
            break;
        case 'email_invalido':
            $mensaje = "El correo electrónico proporcionado no es válido.";
            break;
        case 'envio_fallido':
            $mensaje = "Hubo un error al enviar el mensaje. Por favor inténtalo de nuevo más tarde.";
            break;
        default:
            $mensaje = "Ocurrió un error desconocido.";
    }
    
    echo '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            '.$mensaje.'
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>
    </form>
  </div>
</div>




 <!-- Modal del Carrito (se abre desde la derecha) -->
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-slideout modal-lg">
    <div class="modal-content h-100 rounded-start-4">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold " id="modalCarritoLabel">
          <i class="bi bi-cart3"></i> Tu Carrito de Compras
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body overflow-auto p-3">
        <div id="carrito-vacio" class="text-center py-5">
          <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">Tu carrito está vacío</p>
        </div>
        <div id="carrito-contenido" style="display: none;">
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead>
                <tr class="border-bottom">
                  <th>Producto</th>
                  <th style="width: 140px;">Cantidad</th>
                  <th style="width: 100px;" class="text-end">Precio</th>
                  <th style="width: 100px;" class="text-end">Subtotal</th>
                  <th style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody id="carrito-items">
                <!-- Los items del carrito se generan dinámicamente aquí -->
              </tbody>
              <tfoot class="border-top">
                <tr>
                  <td colspan="3" class="text-end fw-bold">Total:</td>
                  <td class="text-end fw-bold" id="carrito-total">$0.00</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-arrow-left"></i> Seguir comprando
        </button>
        <div>
          <button type="button" class="btn btn-outline-danger me-2" id="vaciar-carrito">
            <i class="bi bi-trash"></i> Vaciar
          </button>
          <a href="../views/pagar.php" class="btn btn-success" id="btn-pagar">
            <i class="bi bi-credit-card"></i> Pagar
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Footer -->


<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
      <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
      <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/carrito.js"></script>
  
  <script src="../assets/js/contacto.js"></scrip>
</body>
</html>


