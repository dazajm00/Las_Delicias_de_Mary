<?php

namespace App\Http\Controllers;

use App\Models\Mesa;

class MesaController extends Controller
{
    /**
     * Tablero de mesas del mesero (CU-04).
     */
    public function index()
    {
        // Solo mesero o administrador
        if (session('rol') !== 'MESERO' && session('rol') !== 'ADMINISTRADOR') {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para entrar a esta sección.');
        }

        $mesas = Mesa::where('activa', true)->orderBy('numero')->get();
        return view('mesas.index', compact('mesas'));
    }

    /**
     * Reservar una mesa (solo si está disponible).
     */
    public function reservar(Mesa $mesa)
    {
        if (session('rol') !== 'MESERO' && session('rol') !== 'ADMINISTRADOR') {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para entrar a esta sección.');
        }

        if ($mesa->estado !== 'DISPONIBLE') {
            return redirect()->route('mesas.index')->with('error', 'Solo se puede reservar una mesa disponible.');
        }

        $mesa->estado = 'RESERVADA';
        $mesa->save();

        return redirect()->route('mesas.index')->with('success', "La mesa {$mesa->numero} quedó reservada.");
    }

    /**
     * Liberar una mesa: vuelve a disponible si no tiene pedidos sin entregar.
     */
    public function liberar(Mesa $mesa)
    {
        if (session('rol') !== 'MESERO' && session('rol') !== 'ADMINISTRADOR') {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para entrar a esta sección.');
        }

        if ($mesa->tienePedidosEnCurso()) {
            return redirect()->route('mesas.index')->with('error', "La mesa {$mesa->numero} tiene pedidos sin entregar. No se puede liberar.");
        }

        $mesa->estado = 'DISPONIBLE';
        $mesa->save();

        return redirect()->route('mesas.index')->with('success', "La mesa {$mesa->numero} quedó disponible.");
    }
}
