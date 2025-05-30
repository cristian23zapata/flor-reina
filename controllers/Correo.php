<?php
// controllers/Correo.php (Ejemplo simplificado)

// Puedes necesitar incluir PHPMailer si no lo tienes ya en tu proyecto.
// Descárgalo desde https://github.com/PHPMailer/PHPMailer/releases
// e incluye los archivos necesarios.
// Por ejemplo, si lo pones en una carpeta 'PHPMailer' dentro de 'controllers':
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

class Correo {
    public function enviarFactura(
        $destinatario_email,
        $destinatario_nombre,
        $numero_pedido,
        $fecha_pedido,
        $datos_cliente, // Array: ['nombre', 'correo', 'direccion', 'telefono']
        $productos,     // Array de productos del carrito
        $subtotal,
        $iva_monto,
        $total
    ) {
        // --- CONTENIDO DEL CORREO HTML BÁSICO ---
        $cuerpo_html = "
        <html>
        <head>
            <title>Confirmación de Pedido Flor Reina</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                h2 { color: #28a745; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #eee; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .text-right { text-align: right; }
                .total-section { margin-top: 20px; border-top: 2px solid #28a745; padding-top: 10px; }
                .total-section p { margin: 5px 0; }
                .total-section .grand-total { font-size: 1.2em; font-weight: bold; color: #28a745; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>¡Gracias por tu compra en Flor Reina!</h2>
                <p>Hola <strong>" . htmlspecialchars($destinatario_nombre) . "</strong>,</p>
                <p>Tu pedido ha sido confirmado exitosamente.</p>

                <h3>Detalles del Pedido</h3>
                <p><strong>Número de Pedido:</strong> " . htmlspecialchars($numero_pedido) . "</p>
                <p><strong>Fecha del Pedido:</strong> " . htmlspecialchars($fecha_pedido) . "</p>

                <h3>Datos de Envío</h3>
                <p><strong>Nombre:</strong> " . htmlspecialchars($datos_cliente['nombre']) . "</p>
                <p><strong>Correo:</strong> " . htmlspecialchars($datos_cliente['correo']) . "</p>
                <p><strong>Dirección:</strong> " . htmlspecialchars($datos_cliente['direccion']) . "</p>
                <p><strong>Teléfono:</strong> " . htmlspecialchars($datos_cliente['telefono']) . "</p>

                <h3>Resumen de Artículos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class='text-right'>Precio Unitario</th>
                            <th class='text-right'>Cantidad</th>
                            <th class='text-right'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach ($productos as $item) {
            $cuerpo_html .= "
                        <tr>
                            <td>" . htmlspecialchars($item['nombre']) . "</td>
                            <td class='text-right'>$" . number_format($item['precio'], 2) . "</td>
                            <td class='text-right'>" . htmlspecialchars($item['cantidad']) . "</td>
                            <td class='text-right'>$" . number_format($item['precio'] * $item['cantidad'], 2) . "</td>
                        </tr>";
        }
        $cuerpo_html .= "
                    </tbody>
                </table>

                <div class='total-section'>
                    <p>Subtotal: <span class='text-right'>$" . number_format($subtotal, 2) . "</span></p>
                    <p>IVA (21%): <span class='text-right'>$" . number_format($iva_monto, 2) . "</span></p>
                    <p class='grand-total'>Total a Pagar: <span class='text-right'>$" . number_format($total, 2) . "</span></p>
                </div>

                <p>Tu pedido será procesado y enviado en un plazo de 24-48 horas. Te enviaremos otra notificación cuando esté en camino.</p>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p>Saludos cordiales,</p>
                <p>El equipo de Flor Reina</p>
            </div>
        </body>
        </html>";
        // --- FIN CONTENIDO DEL CORREO HTML BÁSICO ---

        // Configuración de PHPMailer (ajusta con tus credenciales SMTP)
        // ESTO ES UN EJEMPLO. DEBES CONFIGURAR TU SERVIDOR SMTP REAL.
        // EN UN ENTORNO DE PRUEBA, PUEDES USAR SERVICIOS COMO Mailtrap.io
        // o un servidor SMTP local si lo tienes configurado (ej. Sendmail/Postfix en Linux, hMailServer en Windows).
        /*
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com'; // Servidor SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'user@example.com'; // Usuario SMTP
            $mail->Password   = 'password';         // Contraseña SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // o PHPMailer::ENCRYPTION_SMTPS
            $mail->Port       = 587; // Puerto SMTP (465 para SMTPS, 587 para STARTTLS)

            //Recipients
            $mail->setFrom('no-reply@florreina.es', 'Flor Reina');
            $mail->addAddress($destinatario_email, $destinatario_nombre);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirmacion de Pedido #' . $numero_pedido . ' - Flor Reina';
            $mail->Body    = $cuerpo_html;
            $mail->AltBody = strip_tags($cuerpo_html); // Versión de texto plano

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar el correo: {$mail->ErrorInfo}");
            return false;
        }
        */

        // --- Para propósitos de prueba sin SMTP real (descomentar y probar) ---
        // Simplemente "simula" el envío. En un entorno real, descomentarías PHPMailer.
        // O podrías guardar el correo en un archivo para verificarlo.
        $log_file = '../logs/email_log.txt';
        $log_entry = "--- Correo de Confirmación de Pedido ---\n";
        $log_entry .= "Para: " . $destinatario_email . " (" . $destinatario_nombre . ")\n";
        $log_entry .= "Asunto: Confirmacion de Pedido #" . $numero_pedido . " - Flor Reina\n";
        $log_entry .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $log_entry .= "Contenido HTML:\n" . $cuerpo_html . "\n";
        $log_entry .= "--------------------------------------\n\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        return true; // Simula que el envío fue exitoso
        // --- FIN de la simulación ---
    }
}
?>