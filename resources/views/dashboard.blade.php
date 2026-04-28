<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar CSV</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            overflow-x: hidden;
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
            padding: 10px;
            color: white;
            text-decoration: none;
        }

        .sidebar-link:hover {
            background: #343a40;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <h4 class="text-center py-3">Menú</h4>

        <a href="#" class="sidebar-link">Dashboard</a>
        <a href="#" class="sidebar-link">Importar CSV</a>
        <a href="#" class="sidebar-link">Reportes</a>
        <a href="#" class="sidebar-link">Configuración</a>
    </div>

    <!-- Contenido -->
    <div id="content">

        <button onclick="toggleSidebar()" class="btn btn-dark mb-3">
            ☰ Menú
        </button>

        <!-- TU FORMULARIO (SIN CAMBIOS) -->
        <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="file" name="archivo" required class="form-control mb-3">

            <button type="submit" class="btn btn-primary">
                Cargar archivo completo
            </button>
        </form>

        @if(session('success'))
            <div class="text-success mt-3">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="text-danger mt-3">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- TU JS SEPARADO -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

</body>
</html>