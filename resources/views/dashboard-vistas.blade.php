<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vistas BI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            overflow-x: hidden;
            background-color: #f5f6fa;
        }

        #sidebar {
            width: 250px;
            height: 100vh;
            background: #212529;
            color: white;
            position: fixed;
            transition: 0.3s;
            z-index: 1000;
        }

        #content {
            margin-left: 250px;
            padding: 20px;
        }

        .sidebar-link {
            display: block;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar-link:hover {
            background: #343a40;
            color: #0d6efd;
        }

        .sidebar-link.active {
            background: #0d6efd;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .logout-btn {
            border: none;
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #bb2d3b;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .grid-vistas {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            grid-template-rows: auto auto auto;
            gap: 20px;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: auto;
            max-height: 390px;
        }

        .panel-grande {
            grid-row: span 2;
            max-height: 800px;
        }

        .panel-ancho {
            grid-column: span 2;
        }

        .panel h5 {
            margin-bottom: 15px;
            font-weight: bold;
        }

        table {
            font-size: 13px;
        }

        th {
            white-space: nowrap;
        }

        td {
            white-space: nowrap;
        }

        .kpi-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e9ecef;
        }

        .kpi-card h3 {
            margin: 0;
            font-weight: bold;
            color: #0d6efd;
        }

        .kpi-card p {
            margin: 0;
            color: #6c757d;
        }

        @media (max-width: 1200px) {
            .grid-vistas {
                grid-template-columns: 1fr;
            }

            .panel-grande,
            .panel-ancho {
                grid-column: span 1;
                grid-row: span 1;
            }

            #content {
                margin-left: 250px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="text-center py-3 border-bottom">
            <span class="logo">Sistema</span>
        </div>

        <a href="{{ route('dashboard.datos') }}" class="sidebar-link">
             Dashboard
        </a>

        <a href="{{ route('dashboard.vistas') }}" class="sidebar-link active">
            Vistas BI
        </a>

        <a href="{{ route('dashboard') }}" class="sidebar-link">
             Importar Archivo
        </a>

        <a href="#" class="sidebar-link">
            Reportes
        </a>

        <a href="#" class="sidebar-link">
            Configuración
        </a>

        <div class="position-absolute bottom-0 w-100 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn w-100">
                    Cerrar sesión
                </button>
            </form>
        </div>

    </div>

    <!-- CONTENIDO -->
    <div id="content">

        <div class="topbar mb-4">
            <h3 class="m-0">Panel de Vistas BI</h3>

            <h5 class="m-0">
                Bienvenido {{ Auth::user()->name }}
            </h5>
        </div>

        <!-- RESUMEN -->
        <div class="panel mb-4">
            <h5>Resumen General</h5>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="kpi-card">
                        <h3>{{ $resumen->TotalClientes ?? 0 }}</h3>
                        <p>Clientes</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-card">
                        <h3>{{ $resumen->TotalProductos ?? 0 }}</h3>
                        <p>Productos</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-card">
                        <h3>{{ $resumen->TotalVentas ?? 0 }}</h3>
                        <p>Ventas</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-card">
                        <h3>Q {{ number_format($resumen->MontoTotalVendido ?? 0, 2) }}</h3>
                        <p>Total vendido</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID DE VISTAS -->
        <div class="grid-vistas">

            <!-- VENTAS DETALLE -->
            <div class="panel panel-grande">
                <h5>Ventas Detalladas</h5>

                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Venta</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Fecha</th>
                            <th>Región</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ventasDetalle as $v)
                            <tr>
                                <td>{{ $v->VentaID }}</td>
                                <td>{{ $v->Cliente }}</td>
                                <td>{{ $v->Producto }}</td>
                                <td>{{ $v->Categoria }}</td>
                                <td>{{ $v->Fecha }}</td>
                                <td>{{ $v->Region }}</td>
                                <td>{{ $v->Cantidad }}</td>
                                <td>Q {{ number_format($v->Total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- VENTAS POR PRODUCTO -->
            <div class="panel">
                <h5>Ventas por Producto</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ventasProducto as $v)
                            <tr>
                                <td>{{ $v->Producto }}</td>
                                <td>{{ $v->Categoria }}</td>
                                <td>{{ $v->CantidadVendida }}</td>
                                <td>Q {{ number_format($v->TotalVendido, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- VENTAS POR CLIENTE -->
            <div class="panel">
                <h5>Ventas por Cliente</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Ciudad</th>
                            <th>Ventas</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ventasCliente as $v)
                            <tr>
                                <td>{{ $v->Nombre }}</td>
                                <td>{{ $v->Ciudad }}</td>
                                <td>{{ $v->TotalVentas }}</td>
                                <td>Q {{ number_format($v->MontoTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- VENTAS POR UBICACIÓN -->
            <div class="panel">
                <h5>Ventas por Ubicación</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Región</th>
                            <th>País</th>
                            <th>Ventas</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ventasUbicacion as $v)
                            <tr>
                                <td>{{ $v->Region }}</td>
                                <td>{{ $v->Pais }}</td>
                                <td>{{ $v->TotalVentas }}</td>
                                <td>Q {{ number_format($v->TotalVendido, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- OPINIONES -->
            <div class="panel">
                <h5>Opiniones</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($opiniones as $o)
                            <tr>
                                <td>{{ $o->Cliente }}</td>
                                <td>{{ $o->Producto }}</td>
                                <td>{{ $o->Comentario }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- TOP PRODUCTOS -->
            <div class="panel panel-ancho">
                <h5>Top Productos Vendidos</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad Vendida</th>
                            <th>Total Vendido</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($topProductos as $t)
                            <tr>
                                <td>{{ $t->Producto }}</td>
                                <td>{{ $t->Categoria }}</td>
                                <td>{{ $t->CantidadVendida }}</td>
                                <td>Q {{ number_format($t->TotalVendido, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- BITACORA -->
            <div class="panel panel-ancho">
                <h5>Bitácora de Auditoría</h5>

                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($bitacora as $b)
                            <tr>
                                <td>{{ $b->usuario }}</td>
                                <td>{{ $b->accion }}</td>
                                <td>{{ $b->tabla_afectada }}</td>
                                <td>{{ $b->descripcion }}</td>
                                <td>{{ $b->fecha }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>