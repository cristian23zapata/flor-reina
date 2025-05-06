<?php

require_once 'MySQL.php';

class Usuarios {

    private $conexion;

    public function __construct() {
        $this->conexion = new MySQL();
    }

    public function obtenerUsuarios() {
        $conn = $this->conexion->conectar();
        $sql = "SELECT correo FROM usuarios";  // Asumiendo que tienes una tabla 'usuarios' con la columna 'correo'
        $resultado = $conn->query($sql);

        $usuarios = [];
        if ($resultado->num_rows > 0) {
            while($row = $resultado->fetch_assoc()) {
                $usuarios[] = $row['correo'];
            }
        }
        return $usuarios;
    }
}
?>
