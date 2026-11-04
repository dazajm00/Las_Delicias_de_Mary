<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plato extends Model
{
    use HasFactory;

    // Menú FIJO: especiales del día, jugos, gaseosas y adicionales
    protected $table = 'platos';
    protected $primaryKey = 'id_plato';
    public $timestamps = false;

    protected $fillable = [
        'id_categoria', 'nombre', 'descripcion', 'precio', 'disponible', 'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function estaDisponible(): bool
    {
        return $this->activo && $this->disponible;
    }
}