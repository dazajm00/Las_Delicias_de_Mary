<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesas - Las Delicias de Mary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #faf6f1; }
        .navbar-delicias { background: #5f3524; }
        .btn-delicias { background: #b5432a; border-color: #b5432a; color: #fff; }
        .btn-delicias:hover { background: #973520; border-color: #973520; color: #fff; }

        /* Color de cada mesa según su estado */
        .mesa-DISPONIBLE { border: 2px solid #2e7d32; background: #e8f5e9; }
        .mesa-OCUPADA    { border: 2px solid #c0392b; background: #fdecea; }
        .mesa-RESERVADA  { border: 2px solid #a17a00; background: #fff4cf; }
    </style>
</head>
<body>
    <!-- Barra superior -->
    <nav class="navbar navbar-expand navbar-dark navbar-delicias">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-semibold" href="{{ route('inicio') }}">Las Delicias de Mary</a>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item"><a class="nav-link active" href="{{ route('mesas.index') }}">Mesas</a></li>
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

    <div class="container-fluid px-4 py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="h3 mb-1">Mesas</h1>
        <p class="mb-3">
            <span class="badge" style="background:#2e7d32">Disponible</span>
            <span class="badge" style="background:#c0392b">Ocupada</span>
            <span class="badge" style="background:#a17a00">Reservada</span>
        </p>

        <div class="row g-3">
            @forelse ($mesas as $mesa)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 mesa-{{ $mesa->estado }}">
                        <div class="card-body">
                            <h2 class="h5 mb-0">Mesa {{ $mesa->numero }}</h2>
                            <small class="text-muted">{{ $mesa->capacidad }} puestos</small>
                            <p class="fw-semibold mb-3">{{ ucfirst(strtolower($mesa->estado)) }}</p>

                            <a href="{{ route('pedidos.create', $mesa) }}" class="btn btn-sm btn-delicias mb-1">Nueva comanda</a>

                            @if ($mesa->estado === 'DISPONIBLE')
                                <form method="POST" action="{{ route('mesas.reservar', $mesa) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark mb-1">Reservar</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('mesas.liberar', $mesa) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark mb-1">Liberar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No hay mesas registradas.</p>
            @endforelse
        </div>
    </div>
</body>
</html>