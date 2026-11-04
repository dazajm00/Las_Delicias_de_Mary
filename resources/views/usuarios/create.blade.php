<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo usuario - Las Delicias de Mary</title>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h3 mb-3">Nuevo usuario</h1>

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('usuarios.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                                       class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario (para iniciar sesión)</label>
                                <input type="text" name="usuario" id="usuario" value="{{ old('usuario') }}"
                                       class="form-control @error('usuario') is-invalid @enderror">
                                @error('usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" name="contrasena" id="contrasena"
                                       class="form-control @error('contrasena') is-invalid @enderror">
                                @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Mínimo 6 caracteres.</div>
                            </div>

                            <div class="mb-4">
                                <label for="id_rol" class="form-label">Rol</label>
                                <select name="id_rol" id="id_rol" class="form-select @error('id_rol') is-invalid @enderror">
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                            {{ ucfirst(strtolower($rol->nombre)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-delicias">Guardar</button>
                                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
