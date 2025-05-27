<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carrito'])) {
    $_SESSION['carrito_para_pagar'] = json_decode($_POST['carrito'], true);
    header('Location: ../views/pagar.php');
    exit();
} else {
    header('Location: ../views/productos.php'); // O a la página de carrito
    exit();
}
?>