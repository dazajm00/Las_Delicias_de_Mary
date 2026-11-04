<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Insumo;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Plato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Formulario de la comanda de una mesa (CU-07).
     */
    public function create(Mesa $mesa)
    {
        // Solo el mesero registra comandas (CU-07)
        if (session('rol') !== 'MESERO') {
            return redirect()->route('mesas.index')->with('error', 'Solo el mesero puede registrar comandas.');
        }

        // Insumos disponibles para armar los platos, separados por tipo
        $principios      = Insumo::where('tipo', 'PRINCIPIO')->where('activo', true)->where('disponible', true)->orderBy('nombre')->get();
        $proteinas       = Insumo::where('tipo', 'PROTEINA')->where('activo', true)->where('disponible', true)->orderBy('nombre')->get();
        $acompanamientos = Insumo::where('tipo', 'ACOMPANAMIENTO')->where('activo', true)->where('disponible', true)->orderBy('nombre')->get();

        // Menú fijo (especiales, jugos, gaseosas, adicionales) con sus platos disponibles
        $categorias = Categoria::with(['platos' => function ($consulta) {
            $consulta->where('activo', true)->where('disponible', true)->orderBy('nombre');
        }])->orderBy('id_categoria')->get();

        return view('pedidos.create', compact('mesa', 'principios', 'proteinas', 'acompanamientos', 'categorias'));
    }

    /**
     * Guardar la comanda y enviarla a cocina (RF-03, RF-07).
     */
    public function store(Request $request, Mesa $mesa)
    {
        // Solo el mesero registra comandas (CU-07)
        if (session('rol') !== 'MESERO') {
            return redirect()->route('mesas.index')->with('error', 'Solo el mesero puede registrar comandas.');
        }

        $request->validate([
            'platos'                      => 'nullable|array',
            'platos.*.principio'          => 'nullable|exists:insumos,id_insumo',
            'platos.*.proteina'           => 'nullable|exists:insumos,id_insumo',
            'platos.*.acompanamientos'    => 'nullable|array',
            'platos.*.acompanamientos.*'  => 'exists:insumos,id_insumo',
            'platos.*.cantidad'           => 'nullable|integer|min:1|max:20',
            'platos.*.observaciones'      => 'nullable|max:255',
            'productos'                   => 'nullable|array',
            'productos.*'                 => 'nullable|integer|min:0|max:20',
        ]);

        // 1. Revisar los platos armados: cada uno necesita principio Y proteína
        $platosArmados = [];
        foreach ($request->input('platos', []) as $numero => $plato) {
            $principio = $plato['principio'] ?? null;
            $proteina  = $plato['proteina'] ?? null;

            if (! $principio && ! $proteina) {
                continue; // fila vacía: el mesero no usó ese plato
            }

            if (! $principio || ! $proteina) {
                return back()->withErrors(['platos' => "El plato {$numero} debe tener un principio y una proteína."])->withInput();
            }

            $platosArmados[] = $plato;
        }

        // 2. Productos del menú fijo con cantidad mayor que 0
        $productos = array_filter($request->input('productos', []), fn ($cantidad) => $cantidad > 0);

        // 3. La comanda no puede ir vacía
        if (count($platosArmados) === 0 && count($productos) === 0) {
            return back()->withErrors(['platos' => 'La comanda está vacía. Agrega al menos un plato antes de enviarla a la cocina.'])->withInput();
        }

        // 4. Mesa reservada: confirmar que llegó el cliente
        if ($mesa->estado === 'RESERVADA' && ! $request->has('confirmar_reserva')) {
            return back()->withErrors(['platos' => 'La mesa está reservada. Marca la casilla para confirmar que el cliente ya llegó.'])->withInput();
        }

        // 5. Guardar todo junto: si algo falla, no queda nada a medias (RNF-04)
        DB::transaction(function () use ($mesa, $platosArmados, $productos) {

            $pedido = Pedido::create([
                'numero'     => (Pedido::max('numero') ?? 0) + 1,
                'id_mesa'    => $mesa->id_mesa,
                'id_usuario' => session('id_usuario'),
                'estado'     => 'PENDIENTE',
                'fecha_hora' => now(),
            ]);

            // El arroz (insumo tipo BASE) va incluido en todos los platos
            $arroz = Insumo::where('tipo', 'BASE')->where('disponible', true)->pluck('id_insumo')->toArray();

            foreach ($platosArmados as $plato) {
                $detalle = DetallePedido::create([
                    'id_pedido'     => $pedido->id_pedido,
                    'tipo'          => 'ARMADO',
                    'cantidad'      => $plato['cantidad'] ?? 1,
                    'observaciones' => $plato['observaciones'] ?? null,
                ]);

                $insumos = array_merge($arroz, [$plato['principio'], $plato['proteina']], $plato['acompanamientos'] ?? []);
                $detalle->insumos()->attach(array_unique($insumos));
            }

            foreach ($productos as $idPlato => $cantidad) {
                $producto = Plato::find($idPlato);
                if (! $producto) {
                    continue;
                }
                DetallePedido::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'tipo'            => 'MENU',
                    'id_plato'        => $producto->id_plato,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $producto->precio,
                ]);
            }

            // La mesa pasa a ocupada
            $mesa->estado = 'OCUPADA';
            $mesa->save();
        });

        return redirect()->route('mesas.index')->with('success', "Comanda de la mesa {$mesa->numero} enviada a cocina.");
    }

    /**
     * Pedidos de una mesa (CU-09, CU-10). Mesero y administrador.
     */
    public function index(Mesa $mesa)
    {
        if (session('rol') !== 'MESERO' && session('rol') !== 'ADMINISTRADOR') {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para entrar a esta sección.');
        }

        $pedidos = Pedido::with(['usuario', 'detalles.plato', 'detalles.insumos'])
            ->where('id_mesa', $mesa->id_mesa)
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return view('pedidos.index', compact('mesa', 'pedidos'));
    }

    /**
     * Cancelar un pedido (RF-12). Solo si sigue pendiente.
     */
    public function cancelar(Pedido $pedido)
    {
        if (session('rol') !== 'MESERO' && session('rol') !== 'ADMINISTRADOR') {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para entrar a esta sección.');
        }

        if ($pedido->estado !== 'PENDIENTE') {
            $estados = ['EN_PREPARACION' => 'en preparación', 'LISTO' => 'listo', 'ENTREGADO' => 'entregado', 'CANCELADO' => 'cancelado'];
            return redirect()->route('pedidos.index', $pedido->id_mesa)
                ->with('error', "El pedido N.º {$pedido->numero} ya está {$estados[$pedido->estado]} y no se puede cancelar.");
        }

        // El pedido no se borra: queda como CANCELADO en el historial
        $pedido->estado = 'CANCELADO';
        $pedido->save();
        DetallePedido::where('id_pedido', $pedido->id_pedido)->update(['cancelado' => true]);

        // Si la mesa ya no tiene pedidos sin entregar, vuelve a disponible (regla R4)
        $mesa = $pedido->mesa;
        if (! $mesa->tienePedidosEnCurso()) {
            $mesa->estado = 'DISPONIBLE';
            $mesa->save();
        }

        return redirect()->route('pedidos.index', $mesa->id_mesa)
            ->with('success', "Pedido N.º {$pedido->numero} cancelado.");
    }
}