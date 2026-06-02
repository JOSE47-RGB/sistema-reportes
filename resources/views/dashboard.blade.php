<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Importación</title>

    <!-- Bootstrap -->
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

        #sidebar.collapsed {
            width: 70px;
        }

        #content {
            margin-left: 250px;
            transition: 0.3s;
            padding: 20px;
        }

        #content.expanded {
            margin-left: 70px;
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

        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: white;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .upload-box {
            border: 2px dashed #ced4da;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #fafafa;
            transition: 0.3s;
        }

        .upload-box:hover {
            border-color: #0d6efd;
            background: #f0f7ff;
        }

        .upload-icon {
            font-size: 50px;
        }

        .btn-upload {
            padding: 10px 20px;
            font-weight: bold;
        }

        .file-info {
            font-size: 14px;
            color: #6c757d;
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

        <a href="{{ route('dashboard') }}" class="sidebar-link active">
            Importar Archivo
        </a>

        <a href="#" class="sidebar-link">
            Reportes
        </a>

        <a href="#" class="sidebar-link">
            Configuración
        </a>

        <!-- CERRAR SESIÓN -->
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

        <!-- TOPBAR -->
        <div class="topbar mb-4">

            <button onclick="toggleSidebar()" class="btn btn-dark">
                ☰ Menú
            </button>

            <h4 class="m-0">
                Bienvenido {{ Auth::user()->name }}
            </h4>

        </div>

        <!-- CARD -->
        <div class="card card-custom">

            <div class="card-body">

                <h3 class="mb-4">
                    Importar Archivo
                </h3>

                <p class="text-muted">
                    El sistema soporta archivos CSV, TXT, XLSX y XLS.
                </p>

                <!-- FORMULARIO -->
                <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="upload-box">

                        <div class="upload-icon mb-3">
                            
                        </div>

                        <h5 class="mb-3">
                            Selecciona un archivo
                        </h5>

                        <input 
                            type="file" 
                            name="archivo" 
                            required 
                            class="form-control mb-3"
                            accept=".csv,.txt,.xlsx,.xls"
                        >

                        <div class="file-info mb-3">
                            Formatos permitidos:
                            CSV, TXT, XLSX, XLS
                        </div>

                        <button type="submit" class="btn btn-primary btn-upload">
                             Cargar archivo completo
                        </button>

                    </div>
                </form>

                <!-- MENSAJES -->
                @if(session('success'))
                    <div class="alert alert-success mt-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mt-4">
                        @foreach ($errors->all() as $error)
                            <p class="m-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

            </div>

        </div>

    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SIDEBAR JS -->
    <script>
        function toggleSidebar() {

            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');

            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        }
    </script>

</body>
</html>