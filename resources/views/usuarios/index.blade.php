<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios - Las Delicias de Mary</title>
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
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('inicio') }}">Las Delicias de Mary</a>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item"><a class="nav-link active" href="{{ route('usuarios.index') }}">Usuarios</a></li>
                <li class="nav-item">
                    <span class="nav-link text-white-50">{{ session('nombre') }} · Administrador</span>
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
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 m-0">Usuarios</h1>
            <a href="{{ route('usuarios.create') }}" class="btn btn-delicias">+ Nuevo usuario</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->nombre }}</td>
                                <td>{{ $usuario->usuario }}</td>
                                <td>{{ ucfirst(strtolower($usuario->rol->nombre)) }}</td>
                                <td>
                                    @if (! $usuario->activo)
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @elseif ($usuario->bloqueado)
                                        <span class="badge text-bg-danger">Bloqueado</span>
                                    @else
                                        <span class="badge text-bg-success">Activo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    @if ($usuario->activo)
                                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" class="d-inline"
                                              onsubmit="return confirm('¿Desactivar este usuario? No podrá iniciar sesión.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Desactivar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>