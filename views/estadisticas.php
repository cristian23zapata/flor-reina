<?php
// Iniciar sesión y verificar rol de administrador
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Incluir tu modelo de conexión
require_once '../models/MySQL.php';
$mysql = new MySQL();
$mysql->conectar();

// Obtener rango de fechas del filtro
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date']   ?? '';

// Inicializar métricas
$totalSemana = $totalSemanaAntes = 0.0;
$totalMes    = $totalMesAntes    = 0.0;
$totalPedidos = 0;
$topProducts  = [];
$statusCounts = [];

// Construir cláusula WHERE si hay filtro
$whereClause = '';
if ($start_date && $end_date) {
    $start_datetime = $start_date . ' 00:00:00';
    $end_datetime   = $end_date   . ' 23:59:59';
    $whereClause    = "WHERE fecha_pedido BETWEEN '$start_datetime' AND '$end_datetime'";
}

// 1) Total de pedidos
$sqlPedidos = "SELECT COUNT(*) AS total_pedidos FROM pedidos $whereClause";
$res = $mysql->efectuarConsulta($sqlPedidos);
if ($row = mysqli_fetch_assoc($res)) {
    $totalPedidos = (int)$row['total_pedidos'];
}
mysqli_free_result($res);

// 2) Top 5 productos más vendidos (unidades)
$sqlTop = "
  SELECT p.nombre, SUM(d.cantidad) AS unidades
    FROM detallepedidos d
    JOIN productos p ON d.id_producto = p.id
    JOIN pedidos   o ON d.id_pedido   = o.id
    $whereClause
   GROUP BY d.id_producto
   ORDER BY unidades DESC
   LIMIT 5
";
$res = $mysql->efectuarConsulta($sqlTop);
while ($row = mysqli_fetch_assoc($res)) {
    $topProducts[] = [
      'nombre'   => $row['nombre'],
      'unidades' => (int)$row['unidades']
    ];
}
mysqli_free_result($res);

// 3) Distribución por estado de pedido
$sqlStatus = "
  SELECT estado, COUNT(*) AS conteo
    FROM pedidos
    $whereClause
   GROUP BY estado
   ORDER BY FIELD(estado,'pendiente','confirmado','enviado','entregado','cancelado')
";
$res = $mysql->efectuarConsulta($sqlStatus);
while ($row = mysqli_fetch_assoc($res)) {
    $statusCounts[$row['estado']] = (int)$row['conteo'];
}
mysqli_free_result($res);

// 4) Comparativas (solo si NO hay filtro de fechas)
if (!$whereClause) {
    // Semana actual y anterior
    $sqlSemana = "
      SELECT SUM(total_pedido) AS total
        FROM pedidos
       WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ";
    $sqlSemanaAntes = "
      SELECT SUM(total_pedido) AS total
        FROM pedidos
       WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
         AND fecha_pedido <  DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ";
    // Mes actual y anterior
    $sqlMes = "
      SELECT SUM(total_pedido) AS total
        FROM pedidos
       WHERE YEAR(fecha_pedido)=YEAR(CURDATE())
         AND MONTH(fecha_pedido)=MONTH(CURDATE())
    ";
    $sqlMesAntes = "
      SELECT SUM(total_pedido) AS total
        FROM pedidos
       WHERE YEAR(fecha_pedido)=YEAR(CURDATE()-INTERVAL 1 MONTH)
         AND MONTH(fecha_pedido)=MONTH(CURDATE()-INTERVAL 1 MONTH)
    ";

    // Ejecutar y asignar
    foreach ([
      'totalSemana'      => $sqlSemana,
      'totalSemanaAntes' => $sqlSemanaAntes,
      'totalMes'         => $sqlMes,
      'totalMesAntes'    => $sqlMesAntes
    ] as $var => $sql) {
        $r = $mysql->efectuarConsulta($sql);
        $val = 0.0;
        if ($rr = mysqli_fetch_assoc($r)) {
            $val = (float)($rr['total'] ?? 0);
        }
        mysqli_free_result($r);
        $$var = $val;
    }
}

// Cerrar conexión
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Estadísticas de Ventas - Flor Reina</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>



 <style>
        /* SIDEBAR BASE */
        .sidebar {
            background-color: #ffe6f0; /* Fondo rosa claro */
            border-right: 1px solid #f8c8dc; /* Borde más suave rosado */
            min-width: 220px;
            transition: all 0.3s ease;
            padding: 1rem 0.5rem;
        }

        /* LOGO */
        .sidebar .navbar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 1rem;
            font-weight: bold;
            color: #d63384;
        }

        /* NAV LINKS */
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #444;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: background 0.2s ease;
        }

        .sidebar .nav-link i {
            margin-right: 0.75rem;
            color: #d63384;
        }

        .sidebar .nav-link span {
            white-space: nowrap;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link:focus {
            background-color: #fddbe9; /* Hover rosado suave */
            color: #d63384;
        }

        /* TOGGLE BUTTON */
        .toggle-btn {
            border: none;
            background: none;
            font-size: 1.25rem;
            color: #d63384;
        }

        /* COLLAPSED SIDEBAR */
        .sidebar.collapsed {
            min-width: 60px !important;
            overflow: hidden;
            background-color: #ffe6f0; /* Mantener fondo cuando colapsa */
        }

        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .navbar-brand span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
        }

        .sidebar.collapsed .navbar-brand {
            padding: 0.5rem 0;
        }

        .sidebar.collapsed .bi {
            margin-right: 0;
            font-size: 1.25rem;
        }

        /* MOBILE SIDEBAR */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100vh;
                width: 220px;
                z-index: 1050;
                background-color: #ffe6f0; /* Fondo rosa también en móvil */
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                transition: left 0.3s ease-in-out;
            }

            .sidebar.show {
                left: 0;
            }
        }
        
        /* Estilo del contenedor del filtro */
        .filter-sidebar {
            background-color: #ffffff; /* Fondo blanco para el filtro */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Sombra suave */
            padding: 20px;
            margin-bottom: 20px; /* Espacio debajo del filtro en pantallas pequeñas */
        }

        /* Ajustes para el acordeón del filtro */
        .filter-sidebar .accordion-button {
            font-size: 1.1em;
            color: #d63384; /* Color del texto del botón del acordeón */
            background-color: #f8f9fa; /* Fondo del botón del acordeón */
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        /* Estilo cuando el acordeón está expandido */
        .filter-sidebar .accordion-button:not(.collapsed) {
            background-color: #f4e6eb; /* Fondo más claro cuando está abierto */
            color: #ac4563; /* Color de texto más oscuro cuando está abierto */
        }
        .filter-sidebar .accordion-body {
            padding-top: 15px;
            padding-bottom: 0;
        }
        .filter-sidebar .form-check-label {
            font-size: 0.95em;
            color: #5a5a5a;
        }

        /* Estilo para los botones dentro del filtro */
        .filter-sidebar .btn-primary {
            background-color: #d1567b;
            border-color: #d1567b;
        }
        .filter-sidebar .btn-primary:hover {
            background-color: #ac4563;
            border-color: #ac4563;
        }
        .filter-sidebar .btn-outline-secondary {
            border-color: #6c757d; /* Color gris de Bootstrap */
            color: #6c757d;
        }
        .filter-sidebar .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }

        /* Media queries para que el filtro sea sticky en pantallas grandes */
        @media (min-width: 768px) {
            .filter-sidebar {
                position: sticky; /* Hace que el filtro se quede fijo al hacer scroll */
                top: 20px; /* Distancia desde la parte superior de la ventana */
                align-self: flex-start; /* Ayuda al sticky en contenedores flex */
                max-height: calc(100vh - 40px); /* Para que no ocupe más de la altura de la ventana */
                overflow-y: auto; /* Permite scroll si el contenido del filtro es muy largo */
            }
        }
    </style>
</head>
<body>

<!-- Botón para abrir sidebar en móvil -->
<button class="btn btn-outline-secondary d-lg-none m-3" id="mobileSidebarToggle">
    <i class="bi bi-list"></i>
</button>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 220px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            <button class="toggle-btn d-none d-lg-inline" id="sidebarToggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/creacion.php"><i class="bi bi-plus-circle"></i><span> CREAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/registrar.php"><i class="bi bi-person-plus"></i><span> REGISTRAR</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> Estadísticas</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> Insumos</span></a></li>

            <?php } elseif (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'repartidor') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> MIS ENTREGAS</span></a></li>
            <?php } ?>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'user') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/productos_usuario.php"><i class="bi bi-flower1"></i><span> Productos</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog_usuario.php"><i class="bi bi-newspaper"></i><span> Blog</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/contacto.php"><i class="bi bi-envelope"></i><span> Contacto</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/user_pedidos.php"><i class="bi bi-box-seam"></i><span> Mis Pedidos</span></a></li>
            <?php } ?>
        </ul>
    </nav>
<body class="bg-light">
  <div class="container my-4">
    <h1 class="mb-4">📊 Estadísticas de Ventas</h1>



    <!-- Filtro por fechas -->
    <form method="post" class="bg-white p-3 rounded shadow-sm mb-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Fecha inicio</label>
          <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha fin</label>
          <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button class="btn btn-primary w-100">
            <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtro
          </button>
        </div>
      </div>
    </form>

    <!-- Métricas principales -->
    <div class="row gy-3">
      <?php if ($whereClause): ?>
        <div class="col-md-6">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <i class="bi bi-basket-fill display-6 text-secondary"></i>
              <h5 class="mt-2">Pedidos en el período</h5>
              <h3><?= number_format($totalPedidos,0,',','.') ?></h3>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="col-md-4">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <i class="bi bi-currency-dollar display-6 text-success"></i>
              <h5 class="mt-2">Ventas Mes Actual</h5>
              <h3>$<?= number_format($totalMes,0,',','.') ?></h3>
              <?php 
                $pct = $totalMesAntes>0 
                  ? round((($totalMes-$totalMesAntes)/$totalMesAntes)*100) 
                  : null;
              ?>
              <small class="<?= $pct>=0?'text-success':'text-danger' ?>">
                <?= $pct!==null ? ($pct.'%') : 'N/A' ?> vs mes anterior
              </small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <i class="bi bi-calendar-week display-6 text-info"></i>
              <h5 class="mt-2">Ventas Semana Actual</h5>
              <h3>$<?= number_format($totalSemana,0,',','.') ?></h3>
              <?php 
                $pct2 = $totalSemanaAntes>0 
                  ? round((($totalSemana-$totalSemanaAntes)/$totalSemanaAntes)*100) 
                  : null;
              ?>
              <small class="<?= $pct2>=0?'text-success':'text-danger' ?>">
                <?= $pct2!==null ? ($pct2.'%') : 'N/A' ?> vs semana ant.
              </small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm text-center">
            <div class="card-body">
              <i class="bi bi-basket-fill display-6 text-secondary"></i>
              <h5 class="mt-2">Total Pedidos</h5>
              <h3><?= number_format($totalPedidos,0,',','.') ?></h3>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Productos top y gráficos -->
    <div class="row mt-4">
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
          <div class="card-header">
            <i class="bi bi-trophy-fill me-1"></i> Top 5 Productos
          </div>
          <ul class="list-group list-group-flush">
            <?php if ($topProducts): ?>
              <?php foreach ($topProducts as $p): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <?= htmlspecialchars($p['nombre']) ?>
                  <span class="badge bg-primary rounded-pill"><?= $p['unidades'] ?></span>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="list-group-item text-muted">Sin datos</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="card shadow-sm">
              <div class="card-header text-center">
                <i class="bi bi-pie-chart-fill me-1"></i> Ventas por Producto
              </div>
              <div class="card-body">
                <canvas id="chartProd"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow-sm">
              <div class="card-header text-center">
                <i class="bi bi-pie-chart-fill me-1"></i> Pedidos por Estado
              </div>
              <div class="card-body">
                <canvas id="chartStatus"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Datos de Ventas por Producto
    const prodLabels = <?= json_encode(array_column($topProducts,'nombre')) ?>;
    const prodData   = <?= json_encode(array_column($topProducts,'unidades')) ?>;
    const prodColors = ['#FF9AA2','#FFB7B2','#FFDAC1','#E2F0CB','#B5EAD7','#C7CEEA'];

    new Chart(document.getElementById('chartProd'), {
      type: 'pie',
      data: {
        labels: prodLabels,
        datasets: [{ data: prodData, backgroundColor: prodLabels.map((_,i)=>prodColors[i%prodColors.length]) }]
      },
      options: { plugins:{ legend:{ position:'bottom' } } }
    });

    // Datos de Pedidos por Estado
    const statusLabels = <?= json_encode(array_map('ucfirst',array_keys($statusCounts))) ?>;
    const statusData   = <?= json_encode(array_values($statusCounts)) ?>;
    const statusColorMap = {
      pendiente:  '#fff3cd',
      confirmado: '#cff4fc',
      enviado:    '#e2e3e5',
      entregado:  '#d1e7dd',
      cancelado:  '#f8d7da'
    };
    const statusColors = Object.keys(<?= json_encode($statusCounts) ?>).map(s => statusColorMap[s]);

    new Chart(document.getElementById('chartStatus'), {
      type: 'pie',
      data: {
        labels: statusLabels,
        datasets: [{ data: statusData, backgroundColor: statusColors }]
      },
      options: { plugins:{ legend:{ position:'bottom' } } }
    });
  </script>
</body>
</html>
