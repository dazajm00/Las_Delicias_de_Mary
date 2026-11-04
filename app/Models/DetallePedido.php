<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle_pedido';
    public $timestamps = false;

    // tipo = ARMADO -> plato que arma el mesero con insumos (id_plato queda NULL)
    // tipo = MENU   -> producto del menú fijo (id_plato apunta a platos)
    protected $fillable = [
        'id_pedido', 'tipo', 'id_plato', 'cantidad',
        'precio_unitario', 'observaciones', 'cancelado'
    ];

    protected $casts = [
        'cancelado' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function plato()
    {
        return $this->belongsTo(Plato::class, 'id_plato', 'id_plato');
    }

    // Insumos elegidos para un plato ARMADO (tabla detalle_pedido_insumo)
    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'detalle_pedido_insumo', 'id_detalle_pedido', 'id_insumo');
    }

    // Texto que ve la cocina: "Arroz · Frijoles · Carne de cerdo asada" o "Jugo de mora"
    public function descripcion(): string
    {
        if ($this->tipo === 'MENU') {
            return $this->plato->nombre ?? 'Producto';
        }

        $orden = ['BASE' => 1, 'PRINCIPIO' => 2, 'PROTEINA' => 3, 'ACOMPANAMIENTO' => 4];

        return $this->insumos
            ->sortBy(fn ($insumo) => $orden[$insumo->tipo] ?? 9)
            ->pluck('nombre')
            ->implode(' · ');
    }
}