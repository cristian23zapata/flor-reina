<?php
session_start();

require_once '../models/MySQL.php';

// Mostrar mensajes de éxito/error
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    echo '<div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            ¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
} 

$mysql = new MySQL;
$mysql->conectar();
$resultado = $mysql->efectuarConsulta("SELECT * FROM usuarios;");
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
<body>
<style>
/* Estilos para la sección de contacto */
.contact-section {
    display: flex;
    flex-direction: column;
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
    gap: 2rem;
    background: linear-gradient(to right, #ffe6f0, #fff0f5);
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Desktop */
@media (min-width: 992px) {
    .contact-section {
        flex-direction: row;
        padding: 2rem;
        gap: 3rem;
    }
}

.contact-info, .contact-form {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    width: 100%;
}

@media (min-width: 992px) {
    .contact-info, .contact-form {
        flex: 1;
        padding: 2rem;
    }
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

/* Ajustes para móvil en los textos */
@media (max-width: 576px) {
    .contact-info p {
        font-size: 0.9rem;
        flex-wrap: wrap;
    }
    
    .contact-info h3, .contact-form h3 {
        font-size: 1.3rem;
    }
}

.whatsapp-section, .pqrs-section {
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1.5rem;
}

.whatsapp-section {
    background-color: #f0f8ff;
    border-left: 4px solid #25D366;
}

.pqrs-section {
    background-color: #fff8f0;
    border-left: 4px solid #FFA500;
}

.whatsapp-btn {
    background-color: #25D366;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    text-decoration: none;
    font-size: 0.9rem;
}

@media (min-width: 768px) {
    .whatsapp-btn {
        font-size: 1rem;
    }
}

.whatsapp-btn:hover {
    background-color: #128C7E;
    color: white;
}

.small-text {
    font-size: 0.85rem;
    color: #666;
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
    min-height: 120px;
    resize: vertical;
}

@media (min-width: 768px) {
    textarea.form-control {
        min-height: 150px;
    }
}

.btn-primary {
    background-color: #3498db;
    border: none;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    transition: background-color 0.3s;
    width: 100%;
}

@media (min-width: 576px) {
    .btn-primary {
        width: auto;
    }
}

.btn-primary:hover {
    background-color: rgb(124, 152, 170);
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
  margin: 0;
  padding: 0;
}

body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* El contenido principal debe crecer para empujar el footer */
main {
  flex: 1 0 auto;
}

/* El footer no debe crecer */
footer {
  flex-shrink: 0;
  margin-top: auto;
}

/* Ajustes para alertas */
.alert {
    margin: 1rem;
}

@media (min-width: 768px) {
    .alert {
        margin: 1rem auto;
        max-width: 80%;
    }
}

/* Mejoras para el formulario en móvil */
.form-description {
    margin-bottom: 1.5rem;
    color: #666;
}

/* Asegurar que los contenedores no se desborden */
.container-fluid {
    padding-left: 15px;
    padding-right: 15px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="../index.php">
      <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60">
    </a>

    <!-- Contenedor para botón de usuario y hamburguesa en móvil -->
    <div class="d-flex d-lg-none align-items-center gap-2">
      <!-- Botón de usuario en móvil -->
      <?php if (isset($_SESSION['correo'])): ?>
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuMobile">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
              <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
              <li><hr class="dropdown-divider"></li>
            <?php } ?>
            <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="../views/login.php" class="btn btn-outline-primary">
          <i class="bi bi-person-circle"></i>
        </a>
      <?php endif; ?>

      <!-- Botón hamburguesa -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../views/productos_usuario.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/blog_usuario.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/contacto.php">Contacto</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/user_pedidos.php">Mis Pedidos</a></li>
      </ul>

      <!-- Botón de usuario visible en desktop -->
      <div class="d-none d-lg-flex align-items-center gap-2 ms-auto">
        <?php if (isset($_SESSION['correo'])): ?>
          <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuDesktop" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i>
              <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuDesktop">
              <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php } ?>
              <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar Sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="../views/login.php" class="btn btn-outline-primary d-flex align-items-center">
            <i class="bi bi-person-circle me-1"></i>
            <span>Login</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Contenido principal -->
<main>
    <div class="contact-section">
        <div class="contact-info">
            <h3>Información de contacto</h3>
            <p><i class="bi bi-telephone"></i> +57 321 213 8319</p>
            <p><i class="bi bi-envelope"></i> sugerencias@yogures.com</p>
            <p><i class="bi bi-geo-alt"></i> Calle 8va #5-55</p>
            <p><i class="bi bi-clock"></i> Lun a Vie: 9 am - 4 pm | Sáb: 9 am - 12 m</p>
            
            <div class="whatsapp-section">
                <h4>Escríbenos a WhatsApp</h4>
                <a href="https://wa.me/3146318358" class="btn btn-success whatsapp-btn">
                    <i class="bi bi-whatsapp"></i> Chatear con asesores
                </a>
                <p class="small-text mt-2">Si no estamos disponibles, déjanos un correo y te responderemos en 20-36 horas.</p>
            </div>
            
            <div class="pqrs-section">
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
                
                echo '<div class="alert alert-danger alert-dismissible fade show text-center mt-3" role="alert">
                        '.$mensaje.'
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
            ?>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 The Rains. Todos los derechos reservados.</p>
        <small>Contacto: info@tralemda.com | Tel: +34 666 999 125</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/carrito.js"></script>
<script src="../assets/js/contacto.js"></script>

<script>
function validarTelefono(input) {
    const telefonoError = document.getElementById('telefono-error');
    const valor = input.value.replace(/\D/g, ''); // Remover caracteres no numéricos
    
    if (valor.length !== 10 && valor.length > 0) {
        telefonoError.style.display = 'block';
    } else {
        telefonoError.style.display = 'none';
    }
    
    // Actualizar el valor solo con números
    input.value = valor;
}
</script>

</body>
</html>