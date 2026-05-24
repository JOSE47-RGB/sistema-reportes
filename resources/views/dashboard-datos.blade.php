<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Datos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { overflow-x: hidden; background: #f4f7fb; }

        #sidebar {
            width: 250px;
            height: 100vh;
            background: #212529;
            color: white;
            position: fixed;
        }

        #content {
            margin-left: 250px;
            padding: 25px;
        }

        .sidebar-link {
            display: block;
            padding: 14px;
            color: white;
            text-decoration: none;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: #343a40;
            color: #0d6efd;
        }

        .card-box {
            background: white;
            border: none;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,.08);
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 4px;
            box-shadow: 0 3px 10px rgba(0,0,0,.08);
        }

        canvas {
            max-height: 260px;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h3 class="text-center py-3 border-bottom">Sistema</h3>

    <a href="{{ route('dashboard.datos') }}" class="sidebar-link active">📊 Dashboard</a>
    <a href="{{ route('dashboard') }}" class="sidebar-link">📂 Importar Archivo</a>
    <a href="#" class="sidebar-link">📈 Reportes</a>
    <a href="#" class="sidebar-link">⚙️ Configuración</a>

    <div class="position-absolute bottom-0 w-100 p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-danger w-100">🚪 Cerrar sesión</button>
        </form>
    </div>
</div>

<div id="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-dark">☰ Menú</button>
        <h3>Bienvenido {{ Auth::user()->name }}</h3>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-box">
                <h6>Clientes</h6>
                <h3>{{ $totalClientes }}</h3>
                <canvas id="chartClientes"></canvas>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box">
                <h6>Productos</h6>
                <h3>{{ $totalProductos }}</h3>
                <canvas id="chartProductos"></canvas>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box">
                <h6>Ventas</h6>
                <h3>{{ $totalVentas }}</h3>
                <canvas id="chartVentasMini"></canvas>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card-box">
                <h6>Total Vendido</h6>
                <h3>Q {{ number_format($montoTotal, 2) }}</h3>
                <canvas id="chartMonto"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="chart-card">
                <h5>Ventas por Producto</h5>
                <canvas id="ventasProducto"></canvas>
            </div>
        </div>

        <div class="col-md-5">
            <div class="chart-card">
                <h5>Distribución de Ventas</h5>
                <canvas id="ventasDona"></canvas>
            </div>
        </div>
    </div>

</div>

<script>
    const productos = @json($ventasPorProducto->pluck('Producto'));
    const totales = @json($ventasPorProducto->pluck('total'));

    new Chart(document.getElementById('ventasProducto'), {
        type: 'bar',
        data: {
            labels: productos,
            datasets: [{
                label: 'Ventas',
                data: totales
            }]
        }
    });

    new Chart(document.getElementById('ventasDona'), {
        type: 'doughnut',
        data: {
            labels: productos,
            datasets: [{
                data: totales
            }]
        }
    });

    function miniChart(id, data) {
        new Chart(document.getElementById(id), {
            type: 'line',
            data: {
                labels: data.map((_, i) => i + 1),
                datasets: [{
                    data: data,
                    tension: .4,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }

    miniChart('chartClientes', [5,8,6,10,7,12,9]);
    miniChart('chartProductos', [3,6,4,8,5,9,7]);
    miniChart('chartVentasMini', [4,9,5,7,3,6,8]);
    miniChart('chartMonto', [10,12,8,15,11,14,13]);
</script>

</body>
</html>