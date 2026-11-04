<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_rol', 'nombre', 'usuario', 'contrasena_hash',
        'activo', 'bloqueado', 'intentos_fallidos'
    ];

    // Nunca enviar el hash de la contraseña a las vistas o a JSON
    protected $hidden = [
        'contrasena_hash'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'bloqueado' => 'boolean',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_usuario', 'id_usuario');
    }
}
