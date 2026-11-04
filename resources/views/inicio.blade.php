<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio - Las Delicias de Mary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #faf6f1; }
        .navbar-delicias { background: #2b1f1a; }
        .btn-delicias { background: #b5432a; border-color: #b5432a; color: #fff; }
        .btn-delicias:hover { background: #973520; border-color: #973520; color: #fff; }
    </style>
</head>
<body>
    <!-- Barra superior -->
    <nav class="navbar navbar-expand navbar-dark navbar-delicias">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-semibold" href="{{ route('inicio') }}">Las Delicias de Mary</a>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                @if (session('rol') === 'ADMINISTRADOR')
                    <li class="nav-item"><a class="nav-link" href="{{ route('usuarios.index') }}">Usuarios</a></li>
                @endif
                @if (session('rol') === 'ADMINISTRADOR' || session('rol') === 'MESERO')
                    <li class="nav-item"><a class="nav-link" href="{{ route('mesas.index') }}">Mesas</a></li>
                @endif
                <li class="nav-item">
                    <span class="nav-link text-white-50">{{ session('nombre') }} · {{ ucfirst(strtolower(session('rol'))) }}</span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="h3 mb-1">Bienvenido, {{ session('nombre') }}</h1>
        <p class="text-muted mb-4">Rol: {{ ucfirst(strtolower(session('rol'))) }}</p>

        <div class="row g-3">
            @if (session('rol') === 'ADMINISTRADOR')
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Usuarios</h2>
                            <p class="text-muted">Crear, editar, desactivar y desbloquear cuentas.</p>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-delicias">Gestionar usuarios</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Mesas y pedidos</h2>
                            <p class="text-muted">Ver el estado de las mesas, supervisar y cancelar pedidos.</p>
                            <a href="{{ route('mesas.index') }}" class="btn btn-delicias">Ver mesas</a>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('rol') === 'MESERO')
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Mesas y comandas</h2>
                            <p class="text-muted">Ver las mesas y enviar comandas a la cocina.</p>
                            <a href="{{ route('mesas.index') }}" class="btn btn-delicias">Ir a mesas</a>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('rol') === 'COCINERO')
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Cocina</h2>
                            <p class="text-muted">Disponible en el siguiente avance.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>