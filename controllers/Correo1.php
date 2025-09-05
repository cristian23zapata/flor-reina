<?php
require '../vendor/autoload.php';  // Incluye PHPMailer si usas Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función para generar un código aleatorio de 20 caracteres
function generarCodigo() {
    return bin2hex(random_bytes(10));  // Genera un código de 20 caracteres hexadecimales
}

class Correo {

    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
    }

    // Función para enviar el correo con el enlace de recuperación
    public function enviarCorreo($destinatario, $asunto, $mensaje) {
        try {
            // Configuración del servidor SMTP de Gmail
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';  // Dirección SMTP de Gmail
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'pruebaadso2025@gmail.com';  // Tu correo SMTP
            $this->mail->Password = 'aypi xyao docb utjv';  // Contraseña de la aplicación para Gmail
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            // Remitente
            $this->mail->setFrom('pruebaadso2025@gmail.com', 'Flor de Reina');
            // Destinatario
            $this->mail->addAddress($destinatario);

            // Contenido del correo
            $this->mail->isHTML(true);
            $this->mail->Subject = $asunto;
            $this->mail->Body    = $mensaje;

            // Enviar el correo
            $this->mail->send();
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$this->mail->ErrorInfo}";
        }
    }

    // Función para manejar la recuperación de contraseña
    public function recuperarContrasena($correo) {
        // Conectar a la base de datos
        $mysqli = new mysqli('localhost', 'u810917883_florreina_bd', 'DE~0kp~5gO', 'u810917883_florreina_bd');

        // Verificar la conexión
        if ($mysqli->connect_error) {
            die('Conexión fallida: ' . $mysqli->connect_error);
        }

        // Generar el código de recuperación
        $codigo = generarCodigo();

        // Insertar el código y el correo en la base de datos
        $stmt = $mysqli->prepare("INSERT INTO recuperacion (correo, codigo) VALUES (?, ?)");
        $stmt->bind_param("ss", $correo, $codigo);
        $stmt->execute();

        // Crear el enlace con el código de recuperación
        $enlace = "florreina.proyectosadso.com/views/recuperar1.php?codigo=" . $codigo . "&correo=" . urlencode($correo);

        // Enviar el correo con el enlace de recuperación
        $asunto = 'Recuperacion de Contrasena';
       $mensaje = "
    <div style='
        max-width: 500px;
        margin: 20px auto;
        padding: 20px;
        background-color: #ffe6f2;
        border: 1px solid #f5c6d6;
        border-radius: 12px;
        font-family: Arial, sans-serif;
        color: #333;
        text-align: center;
        '>
        <h2 style='color:#d63384;'>🌸 Recuperación de Contraseña</h2>
    <p style='font-size: 15px;'>
        Hola,<br><br>
        Hemos recibido una solicitud para restablecer tu contraseña en <b>Flor de Reina</b>.
    </p>
    <p style='margin: 20px 0;'>
        <a href='" . $enlace . "' 
           style='
                display: inline-block;
                padding: 12px 20px;
                background-color: #d63384;
                color: #fff;
                text-decoration: none;
                border-radius: 8px;
                font-weight: bold;
           '>
            🔑 Restablecer Contraseña
        </a>
    </p>
    <p style='font-size: 13px; color:#555;'>
        Si no realizaste esta solicitud, puedes ignorar este mensaje.<br>
        Tu cuenta seguirá segura 💖
    </p>
    </div>
    ";
        // Llamar a la función de enviarCorreo
        $this->enviarCorreo($correo, $asunto, $mensaje);

        $mysqli->close();
    }
}
?>
