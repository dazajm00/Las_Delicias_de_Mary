<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';
    protected $primaryKey = 'id_mesa';
    public $timestamps = false;

    protected $fillable = [
        'numero', 'capacidad', 'estado', 'activa'
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // Estados de un pedido que todavía no se ha entregado (regla R3)
    public const ESTADOS_EN_CURSO = ['PENDIENTE', 'EN_PREPARACION', 'LISTO'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_mesa', 'id_mesa');
    }

    public function pedidosEnCurso()
    {
        return $this->pedidos()->whereIn('estado', self::ESTADOS_EN_CURSO);
    }

    public function tienePedidosEnCurso(): bool
    {
        return $this->pedidosEnCurso()->exists();
    }
}