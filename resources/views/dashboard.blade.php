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

        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="text-center py-3 border-bottom">
            <span class="logo">Sistema</span>
        </div>

        <a href="{{ route('dashboard.datos') }}" class="sidebar-link">📊 Dashboard</a>
        <a href="#" class="sidebar-link">📂 Importar Archivo</a>
        <a href="#" class="sidebar-link">📈 Reportes</a>
        <a href="#" class="sidebar-link">⚙️ Configuración</a>

        <!-- BOTÓN CERRAR SESIÓN -->
        <div class="position-absolute bottom-0 w-100 p-3">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout-btn w-100">
                    🚪 Cerrar sesión
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
                    Importar archivo CSV
                </h3>

                <!-- FORMULARIO -->
                <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Seleccionar archivo
                        </label>

                        <input 
                            type="file" 
                            name="archivo" 
                            required 
                            class="form-control"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        ⬆️ Cargar archivo completo
                    </button>
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

    <!-- Sidebar JS -->
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