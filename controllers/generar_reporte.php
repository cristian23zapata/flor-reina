<?php
require_once '../config/db.php';

$tipo = $_GET['tipo'] ?? '';
switch ($tipo) {
    case 'semanal':
        $sql = "SELECT DATE(fecha_pedido) as fecha, SUM(total_pedido) as total
                FROM pedidos
                WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
                GROUP BY DATE(fecha_pedido)";
        $titulo = 'Reporte semanal de ventas';
        break;
    case 'mensual':
        $sql = "SELECT DATE_FORMAT(fecha_pedido,'%Y-%m') as mes, SUM(total_pedido) as total
                FROM pedidos GROUP BY mes";
        $titulo = 'Reporte mensual de ventas';
        break;
    case 'producto':
        $sql = "SELECT nombre_producto, SUM(cantidad) as unidades_vendidas
                FROM detallepedidos GROUP BY nombre_producto";
        $titulo = 'Reporte de ventas por producto';
        break;
    case 'cliente':
        $sql = "SELECT u.nombre, COUNT(p.id) as pedidos, SUM(p.total_pedido) as total
                FROM usuarios u
                JOIN pedidos p ON p.id_usuario = u.id_Usuarios
                GROUP BY u.nombre";
        $titulo = 'Reporte de ventas por cliente';
        break;
    default:
        exit('Tipo de reporte no válido');
}
$result = $conn->query($sql);

// Generar HTML simple con estilo tipo correo
echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
echo '<link rel="stylesheet" href="../assets/css/reportes.css">';
echo "<title>$titulo</title></head><body>";
echo "<h2 class='titulo-reporte'>$titulo</h2>";
echo "<table class='tabla-reporte'><thead><tr>";
foreach (array_keys($result->fetch_assoc()) as $col) {
    echo "<th>" . htmlspecialchars($col) . "</th>";
}
echo '</tr></thead><tbody>';
$result->data_seek(0);
while ($fila = $result->fetch_assoc()) {
    echo '<tr>';
    foreach ($fila as $dato) {
        echo '<td>' . htmlspecialchars($dato) . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
echo '</body></html>'; 
?>
