<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Correo {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
    }

    // Función para enviar correos de contacto
    public function enviarContacto($nombre, $email, $telefono, $mensaje) {
        try {
            // Configuración SMTP (la misma que en recuperar contraseña)
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'pruebaadso2025@gmail.com'; // Tu correo
            $this->mail->Password = 'aypi xyao docb utjv'; // Contraseña de app
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            // Remitente y destinatario
            $this->mail->setFrom($email, $nombre);
            $this->mail->addAddress('pborja564@gmail.com'); // Correo de la empresa

            // Contenido del correo
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Nuevo mensaje de contacto desde el sitio web';
            $this->mail->Body = "
                <h2>Nuevo mensaje de contacto</h2>
                <p><strong>Nombre:</strong> $nombre</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Teléfono:</strong> $telefono</p>
                <p><strong>Mensaje:</strong><br> $mensaje</p>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
?>