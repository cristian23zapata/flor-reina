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
        // Config SMTP
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'pruebaadso2025@gmail.com'; // tu cuenta SMTP
        $this->mail->Password = 'aypi xyao docb utjv';       // contraseña de app
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;

        // Remitente (mejor usar tu propio correo) y respuesta al cliente
        $this->mail->setFrom('pruebaadso2025@gmail.com', 'Formulario de Contacto | Flor Reina');
        $this->mail->addAddress('pborja564@gmail.com', 'Flor Reina');
        $this->mail->addReplyTo($email, $nombre); // así puedes responder directo al cliente

        // Sanitizar/formatos
        $safeNombre   = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $safeEmail    = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeTelefono = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
        $safeMensaje  = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

        // HTML con estilo rosa 🌸
        $html = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Nuevo mensaje de contacto</title>
</head>
<body style='margin:0;padding:20px;background:#fff0f5;font-family:Arial,Helvetica,sans-serif;color:#333;'>
  <div style='max-width:640px;margin:0 auto;background:#ffffff;border:2px solid #f8bbd0;border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.08);overflow:hidden;'>
    
    <div style='background:linear-gradient(135deg,#fce4ec,#f8bbd0);padding:24px 20px;text-align:center;'>
      <h1 style='margin:0;font-size:22px;color:#880e4f;'>💌 Nuevo mensaje de contacto</h1>
      <p style='margin:6px 0 0 0;font-size:13px;color:#6a1b3f;'>Sitio web — Flor Reina</p>
    </div>

    <div style='padding:22px 20px 4px 20px;'>
      <h2 style='margin:0 0 12px 0;font-size:18px;color:#c2185b;'>Datos del remitente</h2>
      <div style='display:block;margin-bottom:12px;'>
        <span style='display:inline-block;background:#fce4ec;border:1px solid #f8bbd0;color:#ad1457;border-radius:999px;padding:8px 12px;font-size:13px;margin:4px 6px 4px 0;'>
          👤 <strong>$safeNombre</strong>
        </span>
        <span style='display:inline-block;background:#fce4ec;border:1px solid #f8bbd0;color:#ad1457;border-radius:999px;padding:8px 12px;font-size:13px;margin:4px 6px 4px 0;'>
          ✉️ <a href='mailto:$safeEmail' style='color:#ad1457;text-decoration:none;'>$safeEmail</a>
        </span>
        <span style='display:inline-block;background:#fce4ec;border:1px solid #f8bbd0;color:#ad1457;border-radius:999px;padding:8px 12px;font-size:13px;margin:4px 6px 4px 0;'>
          📞 <a href='tel:$safeTelefono' style='color:#ad1457;text-decoration:none;'>$safeTelefono</a>
        </span>
      </div>
    </div>

    <div style='padding:0 20px 8px 20px;'>
      <h2 style='margin:0 0 10px 0;font-size:18px;color:#c2185b;'>Mensaje</h2>
      <div style='background:#fff7fb;border:1px solid #f8bbd0;border-radius:10px;padding:16px;'>
        <p style='margin:0;font-size:14px;line-height:1.7;color:#444;'>$safeMensaje</p>
      </div>
    </div>

    <div style='padding:12px 20px 24px 20px;text-align:center;'>
      <a href='mailto:$safeEmail' 
         style='display:inline-block;background:#d81b60;color:#fff;text-decoration:none;font-weight:bold;padding:12px 18px;border-radius:10px;margin:10px 6px;'>
         Responder a $safeNombre
      </a>
      <a href='tel:$safeTelefono' 
         style='display:inline-block;background:#ad1457;color:#fff;text-decoration:none;font-weight:bold;padding:12px 18px;border-radius:10px;margin:10px 6px;'>
         Llamar
      </a>
      <p style='margin:14px 0 0 0;font-size:12px;color:#6a1b3f;'>Este mensaje fue enviado desde el formulario de contacto del sitio.</p>
    </div>

    <div style='background:#fce4ec;border-top:1px solid #f8bbd0;padding:12px 20px;text-align:center;color:#880e4f;font-size:12px;'>
      🌷 Flor Reina — Cuidamos cada detalle para ti 🌷
    </div>

  </div>
</body>
</html>";

        $this->mail->isHTML(true);
        $this->mail->Subject = 'Nuevo mensaje de contacto desde el sitio web';
        $this->mail->Body    = $html;

        // Versión texto plano (fallback)
        $this->mail->AltBody = "Nuevo mensaje de contacto - Flor Reina\n\n"
            . "Nombre: $nombre\n"
            . "Email: $email\n"
            . "Teléfono: $telefono\n"
            . "Mensaje:\n$mensaje\n";

        $this->mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error al enviar correo de contacto: ' . $this->mail->ErrorInfo);
        return false;
    }
}
}
?>