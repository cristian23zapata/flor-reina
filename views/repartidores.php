<?php
require_once '../models/MySQL.php';

session_start();

    if (!isset($_SESSION['correo'])) {
    header("refresh:1;url=../views/login.php");
    exit();
    }

    $mysql = new MySQL;
    $mysql->conectar();

    $resultado = $mysql->efectuarConsulta("SELECT * FROM repartidores;");

    $mysql->desconectar();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo_nav.css">
    <link rel="stylesheet" href="../assets/css/estilo_tabla.css">
</head>
<body>

<!-- Navbar -->
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
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
        <li class="nav-item"><a class="nav-link active" href="../views/creacion.php">CREAR</a></li>
        <li class="nav-item"><a class="nav-link active" href="../views/registrar.php">REGISTRAR</a></li>
        <li class="nav-item"><a class="nav-link" href="../views/repartidores.php">Repartidores</a></li>
        <?php } ?>
        <li class="nav-item"><a class="nav-link" href="../views/productos.php">Productos</a></li>
        
      </ul>

      <form class="d-flex me-3" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar productos..." aria-label="Buscar">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['correo'])): ?>
          <span class="fw-bold"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
          <a href="../controllers/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
        <?php else: ?>
          <a href="../views/login.php"><button class="btn btn-outline-primary"><i class="bi bi-person-circle"></i> Login</button></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<h2 style="text-align:center;">Lista de Repartidores</h2>

<?php

if ($resultado->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Telefono</th><th>Tipo Transporte</th><th>Foto Identificacion</th><th>ACCIONES</th></tr>";

    // Mostrar cada fila
    while($fila = $resultado->fetch_assoc()) {
    echo "<tr>";
        echo "<td>" . $fila["id"] . "</td>";
        echo "<td>" . $fila["nombre"] . "</td>";
        echo "<td>" . $fila["correo"] . "</td>";
        echo "<td>" . $fila["telefono"] . "</td>";
        echo "<td>" . $fila["tipo_transporte"] . "</td>";
        echo "<td><img class='imgId' src='../" . $fila["foto_identificacion"] . "' alt='Foto' style='width:100px;height:100px;'></td>";
        echo "<td>
            <a href='editar.php?id=" . $fila["id"] . "' class='btn-editar'>Editar</a>
            <a href='eliminar.php?id=" . $fila["id"] . "' class='btn-eliminar' onclick='return confirm(\"¿Seguro que deseas eliminar este repartidor?\")'>Eliminar</a>
        </td>";
    echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No se encontraron usuarios.</p>";
}

?>

</body>
</html>
