<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';
    protected $primaryKey = 'id_insumo';
    public $timestamps = false;

    // tipo: BASE (arroz), PRINCIPIO, PROTEINA, ACOMPANAMIENTO
    protected $fillable = [
        'nombre', 'tipo', 'disponible', 'activo'
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'activo' => 'boolean',
    ];

    public function detalles()
    {
        return $this->belongsToMany(DetallePedido::class, 'detalle_pedido_insumo', 'id_insumo', 'id_detalle_pedido');
    }

    public function estaDisponible(): bool
    {
        return $this->activo && $this->disponible;
    }
}
