<?php
// Página de estadísticas – Panel de administración
// Esta versión replica la estructura y estética del panel de administración (rosa y elegante)
// e incluye KPIs, gráficas interactivas y un apartado de reportes. Además mantiene la
// barra lateral y el botón de usuario igual que otras páginas de admin.

session_start();
// Redirigir a la página principal si no es administrador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../models/MySQL.php';

// Conectar a la base de datos
$mysql = new MySQL();
$mysql->conectar();

// Función para devolver un único valor de una consulta SQL
function fetch_value($mysql, $sql, $fallback = 0) {
    $res = $mysql->efectuarConsulta($sql);
    $value = $fallback;
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_row($res);
        if ($row && isset($row[0]) && $row[0] !== null) {
            $value = $row[0];
        }
    }
    mysqli_free_result($res);
    return $value;
}

// Función para devolver un array asociativo de todas las filas
function fetch_all_assoc($mysql, $sql) {
    $out = [];
    $res = $mysql->efectuarConsulta($sql);
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $out[] = $row;
        }
    }
    mysqli_free_result($res);
    return $out;
}

// ------------- Cálculo de métricas -------------
// Ajusta los nombres de columnas o tablas si varían en tu base de datos

// Ventas totales (solo pedidos entregados)
$total_ventas = fetch_value($mysql, "SELECT COALESCE(SUM(total_pedido), 0) FROM pedidos WHERE estado = 'entregado'", 0);

// Pedidos de hoy
$pedidos_hoy = fetch_value($mysql, "SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()", 0);

// Ticket promedio (promedio de total_pedido en pedidos entregados)
$ticket_prom = fetch_value($mysql, "SELECT COALESCE(AVG(total_pedido),0) FROM pedidos WHERE estado = 'entregado'", 0);

// Clientes activos en los últimos 30 días (usuarios diferentes con pedidos)
$clientes_activos = fetch_value($mysql, "SELECT COUNT(DISTINCT id_usuario) FROM pedidos WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 0);

// Ventas de los últimos 14 días (para gráfica de líneas)
$ventas_14 = fetch_all_assoc($mysql, "
    SELECT DATE(fecha_pedido) AS d, COALESCE(SUM(total_pedido),0) AS t
    FROM pedidos
    WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
    GROUP BY DATE(fecha_pedido)
    ORDER BY d
");

// Top 5 productos por unidades vendidas
$top_prod = fetch_all_assoc($mysql, "
    SELECT p.nombre AS nombre, COALESCE(SUM(dp.cantidad),0) AS cant
    FROM detallepedidos dp
    JOIN productos p ON p.id = dp.id_producto
    GROUP BY p.id, p.nombre
    ORDER BY cant DESC
    LIMIT 5
");

// Cantidad de pedidos por estado
$por_estado = fetch_all_assoc($mysql, "
    SELECT estado, COUNT(*) AS c
    FROM pedidos
    GROUP BY estado
    ORDER BY c DESC
");

// Desconectar
$mysql->desconectar();

// Preparar datos para JavaScript
$ventas_labels = array_column($ventas_14, 'd');
$ventas_data   = array_map('floatval', array_column($ventas_14, 't'));

$top_labels  = array_column($top_prod, 'nombre');
$top_data    = array_map('intval', array_column($top_prod, 'cant'));

$estado_labels = array_column($por_estado, 'estado');
$estado_data   = array_map('intval', array_column($por_estado, 'c'));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Estadísticas - Flor Reina</title>
  <link rel="icon" type="image/png" href="../assets/imagenes/icono.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/estilo_nav.css">
  <link rel="stylesheet" href="../assets/css/estilo_creacion.css">
  <style>
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

            .estado-pendiente { background-color: #fff3cd; color: #664d03; } 
            .estado-confirmado { background-color: #d1e7dd; color: #0f5132; } 
            .estado-enviado { background-color: #cff4fc; color: #055160; } 
            .estado-entregado { background-color: #d4edda; color: #155724; } 
            .estado-cancelado { background-color: #f8d7da; color: #842029; } 
            
            .main-content {
                padding: 20px;
                width: 100%;
            }
            
            .filter-buttons .btn {
                margin-right: 5px;
                margin-bottom: 5px;
            }
            
            .card-pedido {
                margin-bottom: 20px;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .card-header-pedido {
                font-weight: bold;
                padding: 15px;
            }
            
            .producto-item {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                background-color: inherit;
                color: inherit;
                border-bottom: 1px solid #eee;
            }
           .card-pedido .list-group-item {
                background-color: inherit;
                color: inherit;
            }
            .sidebar .nav-link:focus,
            .sidebar .nav-link:active {
                outline: none;
                box-shadow: none;
            }

            .sidebar .nav-link:focus-visible {
                outline: none;
                box-shadow: none;
            }
            #userDropdown.btn-outline-primary {
                color: #ff6b9d;         
                border-color: #ff6b9d;
                border-radius: 25rem;
            }
            #userDropdown.btn-outline-primary:hover,
            #userDropdown.btn-outline-primary:focus {
                background-color: #ff6b9d;
                color: white;
                border-color: #ff6b9d;
            }            
}
  </style>
</head>
<body class="bg-light">
  <!-- Botón para abrir sidebar en móvil -->
  <button class="btn btn-outline-secondary d-lg-none m-3" id="mobileSidebarToggle"><i class="bi bi-list"></i></button>
  <div class="d-flex">
    <!-- SIDEBAR -->
    <nav id="sidebar" class="border-end p-3 sidebar" style="min-width: 300px; min-height: 100vh;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a class="navbar-brand d-block text-center" href="">
                <img src="../assets/imagenes/logo.png" alt="Flor Reina" height="60" class="sidebar-logo">
                <span class="ms-2">Flor Reina</span>
            </a>
            
        </div>

        <ul class="nav flex-column">
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') { ?>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/admin_pedidos.php"><i class="bi bi-cart"></i><span> PEDIDOS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/repartidores.php"><i class="bi bi-truck"></i><span> REPARTIDORES</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/gestionar_repartidores.php"><i class="bi bi-gear"></i><span> GESTION REPARTIDORES</span></a></li>
                
                <li class="nav-item"><a class="nav-link text-dark" href="../views/blog.php"><i class="bi bi-newspaper"></i><span> BLOG</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/estadisticas.php"><i class="bi bi-bar-chart"></i><span> ESTADISTICAS</span></a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="../views/insumos.php"><i class="bi bi-box-seam"></i><span> INSUMOS</span></a></li>
            <?php } ?>
        </ul>
    </nav>
    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-grow-1">
     
          
        
      <!-- Contenido -->
      <main>
        <div class="container-fluid">
             
             <div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="fw-bold mb-0"><i class="bi bi-bar-chart"></i> Estadísticas</h1>

  <?php if (isset($_SESSION['correo'])): ?>
    <div class="dropdown">
      <button class="btn btn-outline-primary dropdown-toggle" id="userDropdown" type="button"
              data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <?php if ($_SESSION['tipo'] === 'user') { ?>
          <li><a class="dropdown-item" href="../views/editar_perfil.php">Editar Perfil</a></li>
          <li><hr class="dropdown-divider"></li>
        <?php } ?>
        <li><a class="dropdown-item" href="../controllers/logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  <?php else: ?>
    <a href="../views/login.php" class="btn btn-outline-primary">
      <i class="bi bi-person-circle"></i> Login
    </a>
  <?php endif; ?>
</div>
             
          <!-- KPIs -->
          <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card p-3 rounded-4 shadow-soft h-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small">Ventas Totales</div>
                    <div class="h4 mb-0">$<?php echo number_format((float)$total_ventas, 0, ',', '.'); ?></div>
                  </div>
                  <div class="icon-wrap"><i class="bi bi-cash-stack"></i></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card p-3 rounded-4 shadow-soft h-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small">Pedidos Hoy</div>
                    <div class="h4 mb-0"><?php echo (int)$pedidos_hoy; ?></div>
                  </div>
                  <div class="icon-wrap"><i class="bi bi-bag-check"></i></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card p-3 rounded-4 shadow-soft h-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small">Ticket Promedio</div>
                    <div class="h4 mb-0">$<?php echo number_format((float)$ticket_prom, 0, ',', '.'); ?></div>
                  </div>
                  <div class="icon-wrap"><i class="bi bi-receipt"></i></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="kpi-card p-3 rounded-4 shadow-soft h-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small">Clientes Activos (30d)</div>
                    <div class="h4 mb-0"><?php echo (int)$clientes_activos; ?></div>
                  </div>
                  <div class="icon-wrap"><i class="bi bi-people"></i></div>
                </div>
              </div>
            </div>
          </div>
          <!-- Gráficas -->
          <div class="row g-4">
            <div class="col-12 col-xl-7">
              <div class="card rounded-4 shadow-soft">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Ventas últimos 14 días</h5>
                  </div>
                  <canvas id="chartVentas" height="110"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12 col-xl-5">
              <div class="card rounded-4 shadow-soft h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Pedidos por estado</h5>
                  </div>
                  <canvas id="chartEstados" height="110"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card rounded-4 shadow-soft">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Top 5 productos</h5>
                  </div>
                    <canvas id="chartTop" height="110"></canvas>
                </div>
              </div>
            </div>
          </div>
          <!-- Reportes -->
          <h2 class="fw-bold mt-5 mb-3"><i class="bi bi-file-earmark-text"></i> Reportes</h2>
          <div class="row g-4">
            <div class="col-12 col-md-6 col-xl-3">
              <div class="report-card p-4 h-100">
                <div class="icon-wrapper">
                  <i class="bi bi-calendar2-week"></i>
                </div>
                <h6 class="fw-bold">Reporte semanal</h6>
                <p class="small text-muted mb-3">Resumen de ventas y pedidos de la última semana.</p>
                <a href="../controllers/generar_reporte.php?tipo=semanal" class="btn btn-outline-primary w-100">Generar</a>
              </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <div class="report-card p-4 h-100">
                <div class="icon-wrapper">
                  <i class="bi bi-calendar-month"></i>
                </div>
                <h6 class="fw-bold">Reporte mensual</h6>
                <p class="small text-muted mb-3">Informe detallado de ingresos y productos del mes.</p>
                <a href="../controllers/generar_reporte.php?tipo=mensual" class="btn btn-outline-primary w-100">Generar</a>
              </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <div class="report-card p-4 h-100">
                <div class="icon-wrapper">
                  <i class="bi bi-graph-up"></i>
                </div>
                <h6 class="fw-bold">Reporte por producto</h6>
                <p class="small text-muted mb-3">Comparativa de ventas por producto.</p>
                <a href="../controllers/generar_reporte.php?tipo=producto" class="btn btn-outline-primary w-100">Generar</a>
              </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <div class="report-card p-4 h-100">
                <div class="icon-wrapper">
                  <i class="bi bi-people"></i>
                </div>
                <h6 class="fw-bold">Reporte de clientes</h6>
                <p class="small text-muted mb-3">Actividad y recurrencia de clientes.</p>
                <a href="../controllers/generar_reporte.php?tipo=cliente" class="btn btn-outline-primary w-100">Generar</a>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- JS: Bootstrap y Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    // Sidebar toggles
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebar');
      const desktopToggle = document.getElementById('sidebarToggle');
      const mobileToggle = document.getElementById('mobileSidebarToggle');
      if (desktopToggle) {
        desktopToggle.addEventListener('click', () => {
          sidebar.classList.toggle('collapsed');
          const icon = desktopToggle.querySelector('i');
          if (sidebar.classList.contains('collapsed')) {
            icon.classList.remove('bi-chevron-left');
            icon.classList.add('bi-chevron-right');
          } else {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-left');
          }
        });
      }
      if (mobileToggle) {
        mobileToggle.addEventListener('click', () => { sidebar.classList.toggle('show'); });
        document.addEventListener('click', (e) => {
          const isInside = sidebar.contains(e.target) || mobileToggle.contains(e.target);
          if (!isInside && window.innerWidth < 992) sidebar.classList.remove('show');
        });
      }
    });
    // Datos para charts (desde PHP)
    const ventasLabels = <?php echo json_encode($ventas_labels, JSON_UNESCAPED_UNICODE); ?>;
    const ventasData   = <?php echo json_encode($ventas_data, JSON_UNESCAPED_UNICODE); ?>;
    const topLabels    = <?php echo json_encode($top_labels, JSON_UNESCAPED_UNICODE); ?>;
    const topData      = <?php echo json_encode($top_data, JSON_UNESCAPED_UNICODE); ?>;
    const estadoLabels = <?php echo json_encode($estado_labels, JSON_UNESCAPED_UNICODE); ?>;
    const estadoData   = <?php echo json_encode($estado_data, JSON_UNESCAPED_UNICODE); ?>;
    // Paleta
    const palette = {
      primary:  '#d63384',
      p2:       '#f06292',
      p3:       '#ff80ab',
      lilac:    '#a78bfa',
      mint:     '#34d399',
      gold:     '#f59e0b'
    };
    // Gráfica de líneas – Ventas
    const ctxVentas = document.getElementById('chartVentas');
    new Chart(ctxVentas, {
      type: 'line',
      data: {
        labels: ventasLabels,
        datasets: [{
          label: 'Ventas',
          data: ventasData,
          tension: .35,
          borderColor: palette.primary,
          backgroundColor: 'rgba(214,51,132,0.12)',
          pointRadius: 3,
          pointBackgroundColor: palette.primary,
          fill: true
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => '$' + Intl.NumberFormat('es-CO').format(v) } }
        },
        plugins: { legend: { display: false } }
      }
    });
    // Gráfica de doughnut – Estados
    const ctxEstados = document.getElementById('chartEstados');
    new Chart(ctxEstados, {
      type: 'doughnut',
      data: {
        labels: estadoLabels,
        datasets: [{
          data: estadoData,
          backgroundColor: [palette.primary, palette.mint, palette.lilac, palette.gold, palette.p2, palette.p3],
          borderWidth: 0
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    // Gráfica de barras – Top productos
    const ctxTop = document.getElementById('chartTop');
    new Chart(ctxTop, {
      type: 'bar',
      data: {
        labels: topLabels,
        datasets: [{
          label: 'Unidades vendidas',
          data: topData,
          backgroundColor: palette.primary
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
      }
    });
  </script>
</body>
</html>