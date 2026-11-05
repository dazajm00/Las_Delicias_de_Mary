<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda Mesa {{ $mesa->numero }} - Las Delicias de Mary</title>
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
                <li class="nav-item"><a class="nav-link" href="{{ route('mesas.index') }}">Mesas</a></li>
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
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <h1 class="h3 m-0">
                Comanda · Mesa {{ $mesa->numero }}
                <small class="text-muted fs-6">{{ ucfirst(strtolower($mesa->estado)) }} · {{ $mesa->capacidad }} puestos</small>
            </h1>
            <a href="{{ route('mesas.index') }}">← Volver a mesas</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>No se pudo enviar la comanda.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pedidos.store', $mesa) }}">
            @csrf

            <div class="row g-4">
                <!-- ============ PLATOS ARMADOS ============ -->
                <div class="col-lg-8">
                    <h2 class="h5">Platos armados <small class="text-muted">(el arroz va incluido en todos)</small></h2>
                    <p class="text-muted small">Llena un plato por cada cliente. Los platos que dejes vacíos no se envían.</p>

                    @for ($i = 1; $i <= $mesa->capacidad; $i++)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h3 class="h6 fw-semibold">Plato {{ $i }}</h3>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label small">Principio</label>
                                        <select name="platos[{{ $i }}][principio]" class="form-select">
                                            <option value="">— Sin plato —</option>
                                            @foreach ($principios as $insumo)
                                                <option value="{{ $insumo->id_insumo }}" {{ old("platos.$i.principio") == $insumo->id_insumo ? 'selected' : '' }}>
                                                    {{ $insumo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Proteína</label>
                                        <select name="platos[{{ $i }}][proteina]" class="form-select">
                                            <option value="">— Sin plato —</option>
                                            @foreach ($proteinas as $insumo)
                                                <option value="{{ $insumo->id_insumo }}" {{ old("platos.$i.proteina") == $insumo->id_insumo ? 'selected' : '' }}>
                                                    {{ $insumo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Cantidad</label>
                                        <input type="number" name="platos[{{ $i }}][cantidad]" min="1" max="20"
                                               value="{{ old("platos.$i.cantidad", 1) }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <span class="form-label small d-block">Acompañamientos (opcional)</span>
                                        @foreach ($acompanamientos as $insumo)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="p{{ $i }}a{{ $insumo->id_insumo }}"
                                                       name="platos[{{ $i }}][acompanamientos][]" value="{{ $insumo->id_insumo }}"
                                                       {{ in_array($insumo->id_insumo, old("platos.$i.acompanamientos", [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="p{{ $i }}a{{ $insumo->id_insumo }}">{{ $insumo->nombre }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="platos[{{ $i }}][observaciones]" maxlength="255"
                                               value="{{ old("platos.$i.observaciones") }}" class="form-control form-control-sm"
                                               placeholder="Observaciones para cocina (ej. sin cebolla)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- ============ MENÚ FIJO ============ -->
                <div class="col-lg-4">
                    <h2 class="h5">Menú del día, bebidas y adicionales</h2>
                    <p class="text-muted small">Escribe la cantidad de lo que pidan. Deja 0 en lo demás.</p>

                    <div class="card">
                        <div class="card-body">
                            @foreach ($categorias as $categoria)
                                @if ($categoria->platos->count() > 0)
                                    <h3 class="h6 text-uppercase text-muted mt-2">{{ $categoria->nombre }}</h3>
                                    @foreach ($categoria->platos as $plato)
                                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                            <div>
                                                {{ $plato->nombre }}<br>
                                                <small class="text-muted">${{ number_format($plato->precio, 0, ',', '.') }}</small>
                                            </div>
                                            <input type="number" name="productos[{{ $plato->id_plato }}]" min="0" max="20"
                                                   value="{{ old("productos.$plato->id_plato", 0) }}" class="form-control" style="width: 80px">
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if ($mesa->estado === 'RESERVADA')
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="confirmar_reserva" value="1" id="confirmar_reserva">
                            <label class="form-check-label" for="confirmar_reserva">El cliente de la reserva ya llegó</label>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-delicias btn-lg w-100 mt-3">Enviar a cocina</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
