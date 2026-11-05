<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos Mesa {{ $mesa->numero }} - Las Delicias de Mary</title>
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

        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <h1 class="h3 m-0">
                Pedidos · Mesa {{ $mesa->numero }}
                <small class="text-muted fs-6">{{ ucfirst(strtolower($mesa->estado)) }}</small>
            </h1>
            <a href="{{ route('mesas.index') }}">← Volver a mesas</a>
        </div>

        @forelse ($pedidos as $pedido)
            @php
                // Color del estado del pedido
                $colores = [
                    'PENDIENTE'      => 'warning',
                    'EN_PREPARACION' => 'info',
                    'LISTO'          => 'primary',
                    'ENTREGADO'      => 'success',
                    'CANCELADO'      => 'secondary',
                ];
            @endphp
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h2 class="h5 mb-0">
                                Pedido N.º {{ $pedido->numero }}
                                <span class="badge text-bg-{{ $colores[$pedido->estado] ?? 'secondary' }} fs-6 align-middle">
                                    {{ ['PENDIENTE' => 'Pendiente', 'EN_PREPARACION' => 'En preparación', 'LISTO' => 'Listo', 'ENTREGADO' => 'Entregado', 'CANCELADO' => 'Cancelado'][$pedido->estado] ?? $pedido->estado }}
                                </span>
                            </h2>
                            <small class="text-muted">
                                {{ $pedido->fecha_hora->format('d/m/Y h:i a') }} · Mesero: {{ $pedido->usuario->nombre ?? '—' }}
                            </small>
                        </div>

                        @if ($pedido->estado === 'PENDIENTE')
                            <form method="POST" action="{{ route('pedidos.cancelar', $pedido) }}"
                                  onsubmit="return confirm('¿Cancelar este pedido? La cocina ya no lo preparará.')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">Cancelar pedido</button>
                            </form>
                        @endif
                    </div>

                    <ul class="list-group list-group-flush mt-2">
                        @foreach ($pedido->detalles as $detalle)
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span>
                                    {{ $detalle->descripcion() }}
                                    @if ($detalle->observaciones)
                                        <br><small class="text-muted">Obs.: {{ $detalle->observaciones }}</small>
                                    @endif
                                </span>
                                <span class="text-muted">x{{ $detalle->cantidad }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <p class="text-muted">Esta mesa no tiene pedidos registrados.</p>
        @endforelse
    </div>
</body>
</html>