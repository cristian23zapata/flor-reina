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
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Correo {
    public function enviarFactura(
        $destinatario_email,
        $destinatario_nombre,
        $numero_pedido,
        $fecha_pedido,
        $datos_cliente,
        $productos,
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
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background-color: #fff0f5; 
            padding: 20px; 
        }
        .container { 
            width: 100%; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
            background: #fff; 
            border: 2px solid #f8bbd0; 
            border-radius: 12px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { 
            color: #d81b60; 
            text-align: center;
        }
        h3 { 
            color: #ad1457; 
            border-bottom: 1px solid #f8bbd0; 
            padding-bottom: 5px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #f8bbd0; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #fce4ec; 
            color: #880e4f; 
        }
        .text-right { 
            text-align: right; 
        }
        .total-section { 
            margin-top: 20px; 
            border-top: 2px solid #f48fb1; 
            padding-top: 10px; 
        }
        .total-section p { 
            margin: 5px 0; 
        }
        .total-section .grand-total { 
            font-size: 1.2em; 
            font-weight: bold; 
            color: #c2185b; 
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🌸 ¡Gracias por tu compra en Flor Reina! 🌸</h2>
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

        <p>Tu pedido será confirmado y enviado en un plazo de 24-48 horas. Mantente atento y Revisa el estado de tus compras.</p>
        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        <p>Saludos cordiales,</p>
        <p><strong>El equipo de Flor Reina</strong></p>

        <div class='footer'>
            🌷 Flor Reina - Cuidamos cada detalle para ti 🌷
        </div>
    </div>
</body>
</html>";

        // --- FIN CONTENIDO DEL CORREO HTML BÁSICO ---

        
       
        
        
$mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';      // servidor SMTP real
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pborja564@gmail.com';    // tu correo/usuario SMTP
            $mail->Password   = 'jgna xupn ntqf snom';            // la contraseña SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;                        // o 465 según tu proveedor

            // Remitente y destinatario
            $mail->setFrom('pborja564@gmail.com', 'Flor Reina');
            $mail->addAddress($destinatario_email, $destinatario_nombre);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Confirmacion de Pedido #' . $numero_pedido . ' - Flor Reina';
            $mail->Body    = $cuerpo_html;
            $mail->AltBody = strip_tags($cuerpo_html);

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Registra el error en el log
            error_log("Error al enviar el correo: {$mail->ErrorInfo}");
            return false;
        }

}
}
?>