<?php
//Clase para gestionar la conexion a la base de datos
class MySQL {

    //Datos de conexion
    private $ipServidor = "localhost";
    private $usuarioBase = "root";
    private $contrasena = "";
    private $nombreBaseDatos = "florreina_bd";

    private $conexion;

    //Metodo para conectar a la base de datos
    public function conectar(){
        $this->conexion = mysqli_connect($this->ipServidor, $this->usuarioBase, $this->contrasena, $this->nombreBaseDatos);

        //Validar si hubo un error en la conexion
        if (!$this->conexion){
            die("Error al conectar a la base de datos : " . mysqli_connect_error());
        }

        //Establecer codificacion utf8
        mysqli_set_charset($this->conexion, "utf8");
    }

    //Metodo para desconectar la base de datos
    public function desconectar(){
        if($this->conexion){
            mysqli_close($this->conexion);
        }
    }

    //Metodo para ejecutar una consulta y devolver su resultaddo
    public function efectuarConsulta($consulta){
        //Verificar que la codificacion sea utf8 antes de ejecutar
        mysqli_query($this->conexion, "SET NAMES 'utf8'");
        mysqli_query($this->conexion, "SET CHARACTER SET 'utf8'");

        $resultado = mysqli_query($this->conexion, $consulta);

        if(!$resultado){
            echo "Error en la consulta : " .mysqli_error($this->conexion);
        }

        return $resultado;
    }

}
?>